<?php

namespace App\Console\Commands;

use App\Http\Controllers\TelegramBotController;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramPolling extends Command
{
    protected $signature = 'telegram:polling';
    protected $description = 'Poll Telegram for updates';

    protected $telegramService;
    protected $telegramBotController;

    public function __construct(TelegramService $telegramService, TelegramBotController $telegramBotController)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
        $this->telegramBotController = $telegramBotController;
    }

    public function handle()
    {
        $lastUpdateId = 0;

        while (true) {
            $updates = $this->telegramService->getUpdates($lastUpdateId);

            if(isset($updates['result'])) {
                foreach ($updates['result'] as $update) {
                    $lastUpdateId = $update['update_id'];
                    $this->telegramBotController->handleUpdate($update);
                }
            }else{
                dd($updates);
            }

            sleep(1); // Wait for 1 second before polling again to avoid hitting rate limits
        }
    }
}
