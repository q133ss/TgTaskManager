<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('ru');
        // Устанавливаем текущую дату (или переданную через параметр)
        $currentDate = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $user = auth()->user();
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
        return view('crm.index', [
            'tasks' => $tasks,
            'allHours' => $allHours,
            'currentDate' => $currentDate,
        ]);
    }
}
