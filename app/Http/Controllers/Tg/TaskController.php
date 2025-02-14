<?php

namespace App\Http\Controllers\Tg;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tg\TaskController\TaskRequest;
use App\Models\Task;
use App\Models\User;
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
        if($request->has('chat_id') && $request->has('type')){
            switch ($request->type) {
                case 'tasks':
                    // Страница "Задачи"
                    $user = User::where('telegram_id', $request->chat_id)->firstOrFail();
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

                    Carbon::setLocale('ru');

                    // Передаем данные в представление
                    return view('telegram.tasks', [
                        'groupedTasks' => $groupedTasks,
                        'currentDate' => $currentDate
                    ]);
                case 'graphic':
                    // Тут будут задачи разделенные по датам и часам
                    return 'graphic';
                    break;
                default:
                    $tasks = Task::where('chat_id', $request->chat_id)->get();
                    break;
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

        $data['creator_id'] = User::where('telegram_id', $request->chat_id)->pluck('id')->first();

        $task = Task::create($data);
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
