<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    protected $telegramApiUrl;

    public function __construct()
    {
        $this->telegramApiUrl = 'https://api.telegram.org/bot' . env('BOT_TOKEN') . '/';
    }

    public function getUpdates($lastUpdateId)
    {
        $url = $this->telegramApiUrl . 'getUpdates?offset=' . ($lastUpdateId + 1);
        $response = Http::get($url);
        return $response->json();
    }

    public function sendMessage($chatId, $text, $replyMarkup = null)
    {
        $url = $this->telegramApiUrl . 'sendMessage';

        $data = [
            'chat_id' => $chatId,
            'text' => $text
        ];

        if($replyMarkup != null) {
            $data['reply_markup'] = $replyMarkup;
        }

        $response = Http::post($url, $data);

        return $response->json();
    }

    public function editMessageText($chatId, $messageId, $text, $replyMarkup = null)
    {
        $url = $this->telegramApiUrl . 'editMessageText';

        $response = Http::post($url, [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $replyMarkup,
        ]);

        return $response->json();
    }
}
