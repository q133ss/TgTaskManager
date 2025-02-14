<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TaskService;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;

class TelegramBotController extends Controller
{
    protected TelegramService $telegramService;
    protected TaskService $taskService;

    public function __construct(TelegramService $telegramService, TaskService $taskService)
    {
        $this->telegramService = $telegramService;
        $this->taskService = $taskService;
    }

    private function getTaskKeyboard(string $chatId): array
    {
        return [
            'inline_keyboard' => [
                [
                    [
                        'text' => "inbox",
                        'web_app' => [
                            'url' => route('telegram.inbox', ['chat_id' => $chatId])
                        ]
                    ]
                ]
            ]
        ];
    }

    private function sendTasksMessage($taks, $chatId){
        if($taks->isEmpty()){
            $this->telegramService->sendMessage($chatId, 'Задачи отсутствуют');
            return;
        }
        $taskText = '';
        foreach ($taks as $task) {
            $taskText .= '• '.$task->text . PHP_EOL;
        }

        $keyboard = $this->getTaskKeyboard($chatId);
        $this->telegramService->sendMessage($chatId, $taskText, json_encode($keyboard));
    }

    public function handleUpdate($update): void
    {
        if (isset($update['message'])) {
            $message = $update['message'];
            $chatId = $message['chat']['id']; // Получаем chat_id
            $text = $message['text'];

            // Проверяем и создаем пользователя
            $user = $this->findOrCreateUser($chatId, $message);

            switch ($text) {
                case '/start':
                    $this->sendWelcomeMessage($chatId);
                    break;
                case '/all':
                    $taks = $user->tasks;
                    $this->sendTasksMessage($taks, $chatId);
                    break;
                case '/today':
                    $taks = $user->tasks?->where('date', now()->format('Y-m-d'));
                    $this->sendTasksMessage($taks, $chatId);
                    break;
                case '/tomorrow':
                    $taks = $user->tasks?->where('date', now()->addDay()->format('Y-m-d'));
                    $this->sendTasksMessage($taks, $chatId);
                    break;
                case '/help':
                    $this->telegramService?->sendMessage($chatId, 'Инструкции по использованию «OK, Bob!» вы всегда можете найти на сайте okbob.app.

Начало работы в OK, Bob!

Задачи
• Создание задач
• Списки задач
• Дата и время задачи
• Напоминания
• Теги
• Редактирование

Работа в группах
• Начало работы
• Создание задач в группах
• Управление задачами
• Отчеты

Дополнительно
• Настройки
• Команды бота в Telegram
• Синхронизация: iCalendar
• Синхронизация: вебхуки
• Горячие клавиши
• Иконка мини-приложения

📢 Подписывайтесь на наш канал @okbob, чтобы быть в курсе обновлений.

👥 Присоединяйтесь к нашему сообществу в чате @okbob_chat – там вы всегда сможете получить ответ на свой вопрос.');
                    break;
                default:
                    // Создаем задачу
                    $this->taskService?->create($chatId, $text, null);
                    $keyboard = $this->getTaskKeyboard($chatId);
                    $this->telegramService?->sendMessage($chatId, '☑️'.$text, json_encode($keyboard));
                    break;
            }
        } elseif (isset($update['callback_query'])) {
            $callbackQuery = $update['callback_query'];
            $data = $callbackQuery['data'];
            $chatId = $callbackQuery['message']['chat']['id'];
            $messageId = $callbackQuery['message']['message_id'];

            // Проверяем и создаем пользователя
            $user = $this->findOrCreateUser($chatId, $callbackQuery['message']['chat']);

            $this->handleCallbackQuery($chatId, $messageId, $data);
        }
    }

    /**
     * Находит пользователя по telegram_id или создает нового.
     *
     * @param int $chatId
     * @param array $chatData
     * @return \App\Models\User
     */
    private function findOrCreateUser(int $chatId, array $chatData): \App\Models\User
    {
        // Проверяем, существует ли пользователь с таким telegram_id
        $user = \App\Models\User::firstOrNew(['telegram_id' => $chatId]);

        // Если пользователь новый, заполняем данные
        if (!$user->exists) {
            $user->telegram_id = $chatId;
            $user->first_name = $chatData['from']['first_name'] ?? null;
            $user->last_name = $chatData['from']['last_name'] ?? null;
            $user->username = $chatData['from']['username'] ?? null;
            $user->save();

            $this->getAndSaveUserProfilePhotoLink($chatId, $user);
        }

        return $user;
    }

    private function getAndSaveUserProfilePhotoLink(int $chatId, \App\Models\User $user): void
    {
        try {
            $token = env('BOT_TOKEN');
            // Получаем file_id аватара
            $response = Http::get("https://api.telegram.org/bot{$token}/getUserProfilePhotos", [
                'user_id' => $chatId,
                'limit' => 1,
            ]);
            $userProfilePhotos = json_decode($response->body(), true);

            if (!isset($userProfilePhotos['result']['photos'][0][0])) {
                return; // Аватар отсутствует
            }

            $photoFileId = $userProfilePhotos['result']['photos'][0][0]['file_id'];

            // Получаем информацию о файле
            $fileResponse = Http::get("https://api.telegram.org/bot{$token}/getFile", [
                'file_id' => $photoFileId,
            ]);
            $fileInfo = json_decode($fileResponse->body(), true);
            $filePath = $fileInfo['result']['file_path'];

            // Составляем прямую ссылку на аватар
            $avatarUrl = "https://api.telegram.org/file/bot{$token}/{$filePath}";

            // Сохраняем ссылку в модели пользователя
            $user->avatar_url = $avatarUrl;
        } catch (\Exception $e) {
            // Обработка ошибок (например, если аватара нет)
        }
    }

    protected function sendWelcomeMessage($chatId)
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Далее', 'callback_data' => 'next_2']
                ]
            ]
        ];

        $text = "🤖 Привет, это «OK, Bob!» — ваш бот-ассистент и трекер задач для Telegram!

«OK, Bob!» поможет планировать задачи в командах прямо из чатов, а также управлять личными делами и календарём. ⏰

В следующих 10 сообщениях мы кратко расскажем о ключевых функциях.

📚 Подробные инструкции всегда доступны в документации на сайте или из команды /help в боте.

Если возникнут вопросы — пишите в чат поддержки, и мы с радостью поможем! 💬

Нажмите Далее ➡️, чтобы узнать, как ставить задачи в боте.";
        $this->telegramService->sendMessage($chatId, $text, json_encode($keyboard));
    }

    protected function handleCallbackQuery($chatId, $messageId, $data)
    {
        $stepMessages = [
            'next_1' => [
                'text' => '🤖 Привет, это «OK, Bob!» — ваш бот-ассистент и трекер задач для Telegram!

«OK, Bob!» поможет планировать задачи в командах прямо из чатов, а также управлять личными делами и календарём. ⏰

В следующих 10 сообщениях мы кратко расскажем о ключевых функциях.

📚 Подробные инструкции всегда доступны в документации на сайте или из команды /help в боте.

Если возникнут вопросы — пишите в чат поддержки, и мы с радостью поможем! 💬

Нажмите Далее ➡️, чтобы узнать, как ставить задачи в боте.',
                'next' => 'next_2'
            ],
            'next_2' => [
                'text' => "Как добавить задачу в боте 📝

Чтобы создать задачу, просто напишите любое сообщение в личной переписке с ботом.

Задача без указания даты будет добавлена в список «Инбокс» – этот список можно увидеть, если:
- нажать на кнопку под ответным сообщением в боте;
- выполнить команду /inbox в боте.

Подробнее: Добавление задач

Нажмите Далее ➡️, чтобы узнать, как ставить задачи на определенное время и день.",
                'next' => 'next_3',
                'back' => 'next_1'
            ],
            'next_3' => [
                'text' => "Как ставить задачи на конкретную дату и время ⏱️

При создании задачи укажите день и время, чтобы бот добавил её в календарь. Вы можете:
• указать дату через точку (21.04) или косую черту (21/04);
• использовать слова сегодня, завтра, today, tomorrow или дни недели (понедельник, суббота, Wednesday) для выбора ближайшего соответствующего дня;
• задавать время через дефис (14-00) или двоеточие (14:00).

Например:
встреча с клиентом завтра 14:00

Создаст задачу на завтрашний день на 14 часов.

📆 Если вы указали только время, без даты, задача поставится на сегодняшний день.
В платных тарифах бот также распознаёт эти форматы из аудиосообщений в личной переписке.

Подробнее: Дата и время

Далее ➡️ – часовой пояс и другие настройки.",
                'next' => 'next_4',
                'back' => 'next_2'
            ],
            'next_4' => [
                'text' => "Установка часового пояса и другие настройки ⚙️

Чтобы время у задач и напоминания работали корректно, важно установить правильный часовой пояс. По умолчанию установлен GMT+3 (Москва).

✈️ Чтобы поменять часовой пояс, откройте меню настроек в мини-приложении или версии для браузера и укажите свой город или смещение по времени.

В этом же разделе вы можете поменять язык интерфейса, включить или отключить отображение выполненных задач, выбрать тёмную тему и настроить другие параметры, чтобы «OK, Bob!» работал так, как удобно именно вам.

Подробнее: Настройки

Далее ➡️ – напоминания и продолжительность события.",
                'next' => 'next_5',
                'back' => 'next_3'
            ],
            'next_5' => [
                'text' => "Как установить напоминание и продолжительность события ⏳

При создании задачи есть возможность добавить напоминание, чтобы бот заранее предупредил вас. Например, можно задать уведомление за 15 минут, 1 час или даже за сутки до начала.

🔔 Для этого в тексте задачи напишите, за сколько прислать уведомление, после восклицательного знака: ![N]м, где N – количество минут, за которое вас необходимо предупредить.

Также можно указать значение в часах, например:
Встреча завтра 12-00 !1ч
уведомление придёт завтра в 11.

Подробнее: Напоминания

⌛️ Если у задачи есть временные рамки (встреча, звонок), укажите длительность в часах (1ч) или минутах (15м)— так во вкладке «График» в мини-приложении будет учитываться фактическая продолжительность. Это удобно для планирования дня и избежания накладок.

Далее ➡️  – как использовать бота в рабочих группах.",
                'next' => 'next_6',
                'back' => 'next_4'
            ],
            'next_6' => [
                'text' => "Как использовать бот в рабочих группах 👥

Чтобы вести задачи всей командой, пригласите бота @okbob_bot в общий чат. Теперь вы можете упоминать участников через имя пользователя @username и сразу назначать исполнителя.

✔️ Каждый сможет видеть свой личный список задач и устанавливать статус выполнения, а также в разделе «Чаты» в мини-приложении и версии для браузера выводятся задачи всех исполнителей из группы.

Подробнее: Работа в группах

Далее ➡️ – интеграция с календарями Яндекс, Google и Apple.",
                'next' => 'next_7',
                'back' => 'next_5'
            ],
            'next_7' => [
                'text' => "Синхронизация с календарями: iCalendar и webhooks 🔗

Синхронизация со сторонними календарями доступна на тарифе «Бизнес» из раздела настроек в мини-приложении и версии для браузера.

📅 «OK, Bob!» поддерживает формат iCalendar, с помощью которого вы сможете выгружать все задачи и события в Яндекс.Календарь, Google Calendar, Apple Calendar и другие календари.

Подробнее: Синхронизация через iCalendar

🔄 Также есть возможность использовать для синхронизации технологию вебхуков (webhooks) — бот будет отправлять информацию о новых задачах в нужный вам сервес.

Подробнее: Синхронизация через вебхуки

Далее ➡️ – возможности версии для браузера.",
                'next' => 'next_8',
                'back' => 'next_6'
            ],
            'next_8' => [
                'text' => 'Возможности версии для браузера на компьютере 💻

Зайдите на hey.okbob.app, авторизуйтесь через Telegram, и вы получите удобный интерфейс для работы с задачами, календарём и аналитикой с большого экрана.

🖥️ Здесь можно просматривать списки личных задач и рабочих групп, редактировать настройки, смотреть детальные отчёты и управлять интеграциями. Интерфейс адаптирован под разные устройства, так что организовывать дела станет ещё проще.

Подробнее: Версия для браузера

Далее ➡️ – как использовать теги.',
                'next' => 'next_9',
                'back' => 'next_7',
            ],
            'next_9' => [
                'text' => 'Теги у задач 🏷️

Для удобной сортировки и поиска добавляйте теги прямо в тексте содержания задачи.

Например, #встреча, #срочно, #❗️ — любые слова и даже emoji, которые помогут группировать задачи.

🔎 Потом легко найти всё по конкретному тегу в списках задач в боте или отфильтровать в мини-приложении и версии для браузера.

Подробнее: Теги

Далее ➡️ – отчёты для рабочих групп.',
                'next' => 'next_10',
                'back' => 'next_8',
            ],
            'next_10' => [
                'text' => 'Отчёты для рабочих групп 📊

С помощью отчётов для рабочих групп вы сможете анализировать эффективность и нагрузку команды: какие задачи выполнены и кто из сотрудников сколько сделал.

Отчеты доступны в версии для браузера с компьютера на hey.okbob.app.

Подробнее: Отчёты в рабочих группах

Далее ➡️ – команды Telegram-бота.',
                'next' => 'next_11',
                'back' => 'next_9',
            ],
            'next_11' => [
                'text' => 'Команды бота в Telegram 💬
В чате с ботом @okbob_bot вы можете использовать команды, вызываемые через /:
• /all — показать все невыполненные задачи,
• /today — показать задачи на сегодня,
• /tomorrow — задачи на завтра,
• /inbox — задачи без указанной даты (из «Инбокса»),
•/help — помощь.

Фильтрация по тегам:
• /all #bob — выведет все невыполненные задачи с тегом #bob,
• /today #bob #work — покажет сегодняшние задачи, содержащие хотя бы один из этих тегов.

Подробнее: Команды бота

Далее ➡️ – канал с обновлениями и чат сообщества.',
                'next' => 'next_12',
                'back' => 'next_10',
            ],
            'next_12' => [
                'text' => 'Спасибо, что прошли онбординг с «OK, Bob!» 🤖

📚 Напоминаем, что полное руководство вы всегда сможете найти на сайте: Инструкции

📢 Подписывайтесь на канал @okbob, чтобы первыми узнавать об обновлениях и новых возможностях.

💬 Присоединяйтесь к нашему сообществу в чате @okbob_chat, где можно задать вопросы, поделиться опытом использования и предложить улучшения.

Успешной работы и продуктивных дней!',
                'back' => 'next_11',
                'next' => null
            ]
        ];

        if (isset($stepMessages[$data])) {
            $step = $stepMessages[$data];
            $keyboard = [
                'inline_keyboard' => [
                    array_filter([
                        $step['back'] ? ['text' => 'Назад', 'callback_data' => $step['back']] : null,
                        $step['next'] ? ['text' => 'Далее', 'callback_data' => $step['next']] : null
                    ])
                ]
            ];

            $this->telegramService->editMessageText($chatId, $messageId, $step['text'], json_encode($keyboard));
        }
    }
}
