<?php

namespace App\Jobs;

use App\Models\Task;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendReminder implements ShouldQueue
{
    use Queueable;

    private string $chatId;
    private Task $task;

    /**
     * Create a new job instance.
     */
    public function __construct(string $chatId, Task $task)
    {
        $this->chatId = $chatId;
        $this->task = $task;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $service = new TelegramService();
        $service->sendMessage($this->chatId, 'Напоминаю вам о задаче: ' . $this->task->text);
    }
}
