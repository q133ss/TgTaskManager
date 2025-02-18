<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Обрабатывает данные авторизации от Telegram.
     */
    public function auth(Request $request)
    {
        // Получаем параметры из запроса
        $data = $request->all();

        // Проверяем подпись Telegram
        if (!$this->verifyTelegramData($data)) {
            abort(403, 'Invalid Telegram signature.');
        }

        // Извлекаем данные пользователя
        $userId = $data['id'];
        $firstName = $data['first_name'];
        $lastname = $data['last_name'] ?? null;
        $username = $data['username'] ?? null;
        $photoUrl = $data['photo_url'] ?? null;

        // Создаем или находим пользователя в базе данных
        $user = \App\Models\User::firstOrCreate(
            ['username' => $username],
            [
                'first_name' => $firstName,
                'last_name' => $lastname,
                'username' => $username,
                'avatar_url' => $photoUrl,
                'telegram_id' => $userId
            ]
        );

        // Авторизуем пользователя
        \Auth::login($user);

        // Перенаправляем на главную страницу
        return to_route('crm.index')->with('success', 'Вы успешно вошли через Telegram!');
    }

    /**
     * Проверяет подпись Telegram.
     */
    private function verifyTelegramData(array $data): bool
    {
        // Сортируем параметры по ключам
        ksort($data);

        // Формируем строку для проверки подписи
        $checkString = '';
        foreach ($data as $key => $value) {
            if ($key !== 'hash') {
                $checkString .= "{$key}={$value}\n";
            }
        }

        // Удаляем последний символ переноса строки
        $checkString = rtrim($checkString, "\n");

        // Генерируем хэш для проверки
        $secretKey = hash('sha256', config('services.telegram.bot_token'), true);
        $expectedHash = hash_hmac('sha256', $checkString, $secretKey);

        // Сравниваем хэши
        return hash_equals($expectedHash, $data['hash']);
    }
}
