<?php

namespace App\Http\Controllers\Tg;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tg\TaskController\TaskRequest;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tasks = [];
        $user = User::where('telegram_id', $request->chat_id)->firstOrFail();
        Carbon::setLocale('ru');

        if($request->has('chat_id') && $request->has('type')){
            switch ($request->type) {
                case 'tasks':
                    // Страница "Задачи"
                    $tasks = $user
                        ->tasks()
                        ->where('tasks.date', '!=', null)
                        ->orderBy('tasks.date')
                        ->get();

                    // Группируем задачи по датам
                    $groupedTasks = $tasks->groupBy(function ($task) {
                        return Carbon::parse($task->date)->format('Y-m-d');
                    });

                    // Устанавливаем текущую дату (или переданную через параметр)
                    $currentDate = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

                    // Передаем данные в представление
                    return view('telegram.tasks', [
                        'groupedTasks' => $groupedTasks,
                        'currentDate' => $currentDate
                    ]);
                case 'graphic':
                    // Устанавливаем текущую дату (или переданную через параметр)
                    $currentDate = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

                    // Получаем все задачи для данного чата и текущей даты
                    $tasks = $user
                        ->tasks()
                        ->whereDate('date', $currentDate->format('Y-m-d'))
                        ->get();

                    // Группируем задачи по времени
                    $groupedTasks = [];
                    foreach ($tasks as $task) {
                        $time = $task->time ?? '00:00'; // Если время не указано, используем 00:00
                        $formattedTime = \Carbon\Carbon::parse($time)->minute(0)->second(0);
                        $groupedTasks[$formattedTime->format('H:i')][] = $task;
                    }

                    // Создаем массив для всех часов (00:00 - 23:00)
                    $allHours = [];
                    for ($hour = 0; $hour < 24; $hour++) {
                        for ($minute = 0; $minute < 60; $minute += 60) {
                            $time = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minute, 2, '0', STR_PAD_LEFT);
                            $allHours[$time] = $groupedTasks[$time] ?? []; // Добавляем пустой массив, если задач нет
                        }
                    }

                    // Передаем данные в представление
                    return view('telegram.graphic', [
                        'allHours' => $allHours,
                        'currentDate' => $currentDate,
                    ]);
                default:
                    // Получаем все задачи для данного chat_id
                    $tasks = $user
                        ->tasks()
                        ->where('is_done', false)
                        ->whereNotNull('date') // Убедимся, что дата не null
                        ->orderBy('date') // Отсортируем задачи по дате
                        ->get();

                    // Группируем задачи по дате
                    $groupedTasks = $tasks->groupBy(function ($task) {
                        return \Carbon\Carbon::parse($task->date)->format('Y-m-d');
                    });

                    // Передаем данные в представление
                    return view('telegram.all', [
                        'groupedTasks' => $groupedTasks,
                    ]);
            }
            abort(403);
        }
        abort(403);
    }

    public function duplicate(string $id, string $chat_id)
    {
        $task = Task::findOrFail($id);
        $this->taskCheck($task, $chat_id);
        $newTask = $task->replicate(['text' => $task->text . ' (копия)']);
        $newTask->save();
        return $newTask;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request)
    {
        $data = $request->validated();
        unset($data['chat_id']);

        // Извлекаем текст задачи
        $taskText = $data['text'];

        // Регулярное выражение для поиска времени в формате HH:MM или HH-MM
        $pattern = '/\b(\d{1,2}[:\-]\d{2})\b/';

        // Ищем время в тексте
        preg_match($pattern, $taskText, $matches);

        // Если время найдено, извлекаем его и удаляем из текста
        if (!empty($matches)) {
            $time = $matches[0];
            $data['time'] = $time; // Сохраняем время в отдельное поле, если нужно
            $data['text'] = preg_replace($pattern, '', $taskText); // Удаляем время из текста
        }

        $data['creator_id'] = User::where('telegram_id', $request->chat_id)->pluck('id')->first();
        $task = Task::create($data);

        (new TaskService())->createReminder($request->chat_id, $task); // Создаем напоминание

        return $task;
    }

    private function taskCheck(Task $task, string $chat_id)
    {
        $user_id = User::where('telegram_id', $chat_id)->pluck('id')->firstOrFail();
        if($task->creator_id !== $user_id && $task->assignee_id !== $user_id){
            abort(403);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, string $id)
    {
        $task = Task::findOrFail($id);
        $this->taskCheck($task, $request->chat_id);
        $data = $request->validated();
        unset($data['chat_id']);
        $task->update($data);
        return $task;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if($request->has('chat_id')){
            $task = Task::findOrFail($id);
            $this->taskCheck($task, $request->chat_id);
            $task->delete();
            return response()->json(['message' => 'Task deleted']);
        }
        abort(403);
    }
}
