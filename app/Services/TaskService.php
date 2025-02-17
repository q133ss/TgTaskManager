<?php

namespace App\Services;

use App\Jobs\SendReminder;
use App\Models\Task;
use App\Models\User;

class TaskService
{
    public function create(string $chat_id, string $text, string $date = null)
    {
        // Регулярное выражение для поиска времени в формате HH:MM или HH-MM
        $pattern = '/\b(\d{1,2}[:\-]\d{2})\b/';

        // Ищем время в тексте
        preg_match($pattern, $text, $matches);

        // Если время найдено, извлекаем его и удаляем из текста
        $time = null;
        if (!empty($matches)) {
            $time = $matches[0]; // Сохраняем найденное время
            $text = preg_replace($pattern, '', $text); // Удаляем время из текста
            $text = trim($text); // Убираем лишние пробелы
        }

        // Создаем задачу
        $task = Task::create([
            'creator_id' => User::where('telegram_id', $chat_id)->pluck('id')->first(),
            'assignee_id' => null,
            'text' => $text, // Текст без времени
            'date' => $date,
            'time' => $time, // Сохраняем время, если оно было найдено
            'is_done' => false
        ]);

        $this->createReminder($chat_id, $task);

        return $task;
    }

    public function createReminder(string $chat_id, $task)
    {
        // Регулярное выражение для поиска шаблона !числом или !числоч
        $pattern = '/!(\d+)(м|ч)/u';

        // Ищем совпадение в тексте
        preg_match($pattern, $task->text, $matches);

        $reminderTime = null;
        if (!empty($matches)) {
            $value = (int)$matches[1]; // Число (например, 30)
            $unit = $matches[2];      // Единица измерения (м или ч)

            // Преобразуем в минуты
            if ($unit === 'ч') {
                $reminderTime = $value * 60; // Часы в минуты
            } else {
                $reminderTime = $value; // Минуты
            }

            // Удаляем шаблон из текста
            $text = preg_replace($pattern, '', $task->text);
            $text = trim($text); // Убираем лишние пробелы
        }

        // Если время напоминания найдено, создаем отложенную задачу
        if ($reminderTime) {
            // Вычисляем время отправки напоминания
            $reminderDate = now()->addMinutes($reminderTime);

            // Отправляем задачу в очередь
            SendReminder::dispatch($chat_id, $task)->delay($reminderDate);
        }

        return true;
    }
}
