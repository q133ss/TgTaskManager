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

        $username = $data['username'] ?? null;

        $userCheck = \App\Models\User::where(['username' => $username]);

        if($userCheck->exists()){
            $user = $userCheck->first();
        }else{
            // Проверяем подпись Telegram
            if (!$this->verifyTelegramData($data)) {
                abort(403, 'Invalid Telegram signature.');
            }

            // Извлекаем данные пользователя
            $userId = $data['id'];
            $firstName = $data['first_name'];
            $lastname = $data['last_name'] ?? null;
            $photoUrl = $data['photo_url'] ?? null;

            // Создаем или находим пользователя в базе данных
            $user = \App\Models\User::create(
                [
                    'username' => $username,
                    'first_name' => $firstName,
                    'last_name' => $lastname,
                    'username' => $username,
                    'avatar_url' => $photoUrl,
                    'telegram_id' => $userId
                ]
            );

        }

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
        // Список допустимых параметров от Telegram
        $allowedKeys = ['id', 'first_name', 'last_name', 'username', 'photo_url', 'auth_date', 'hash'];

        // Фильтруем данные, оставляя только разрешенные ключи
        $filteredData = array_filter($data, function ($key) use ($allowedKeys) {
            return in_array($key, $allowedKeys);
        }, ARRAY_FILTER_USE_KEY);

        // Удаляем пробелы и символы новой строки из значений
        $filteredData = array_map('trim', $filteredData);

        // Сортируем параметры по ключам
        ksort($filteredData);

        // Формируем строку для проверки подписи
        $checkString = '';
        foreach ($filteredData as $key => $value) {
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
        return hash_equals($expectedHash, $filteredData['hash']);
    }
}
