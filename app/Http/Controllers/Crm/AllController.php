<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AllController extends Controller
{
    public function index()
    {
        Carbon::setLocale('ru');
        $user = auth()->user();
        $tasks = $user
            ->tasks()
            ->where('is_done', false)
            ->whereNotNull('date') // Убедимся, что дата не null
            ->orderBy('date') // Отсортируем задачи по дате
            ->get();

        // Группируем задачи по дате
        $groupedTasks = $tasks->groupBy(function ($task) {
            return Carbon::parse($task->date)->format('Y-m-d');
        });

        // Передаем данные в представление
        return view('crm.all', [
            'groupedTasks' => $groupedTasks,
        ]);
    }
}
