<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;

class TaskService
{
    public function create(string $chat_id, string $text, string $date = null)
    {
        // Создает задачу!
        Task::create([
            'creator_id' => User::where('telegram_id', $chat_id)->pluck('id')->first(),
            'assignee_id' => null,
            'text' => $text,
            'date' => $date,
            'is_done' => false
        ]);

        return true;
    }
}
