<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бот-ассистент и трекер задач</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Arial', sans-serif;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        h1, h2, h3 {
            color: #aaa;
        }

        p {
            color: #ccc;
            line-height: 1.6;
        }

        .feature {
            margin-bottom: 20px;
        }

        .feature-icon {
            display: inline-block;
            width: 40px;
            height: 40px;
            background-color: #222;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            margin-right: 10px;
        }

        .btn-telegram {
            background-color: #0088cc;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn-telegram:hover {
            background-color: #0077b3;
        }

        footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>

<!-- Главный контейнер -->
<div class="container mt-5">
    <!-- Заголовок -->
    <h1 class="text-center mb-4">Бот-ассистент и трекер задач</h1>
    <p class="text-center lead mb-5">Управляйте своими задачами прямо из Telegram без лишних усилий.</p>

    <!-- Кнопка "Войти через Telegram" -->
    <div class="d-flex justify-content-center mb-5">
{{--        <button type="button" class="btn btn-telegram">--}}
{{--            <img src="https://telegram.org/favicon.ico" alt="Telegram" style="width: 20px; height: 20px; margin-right: 5px;">--}}
{{--            Войти через Telegram--}}
{{--        </button>--}}
        <script async src="https://telegram.org/js/telegram-widget.js?22" data-telegram-login="mycrmtestbot123_bot" data-size="large" data-auth-url="{{route('auth')}}" data-request-access="write"></script>
    </div>

    <!-- Основные преимущества -->
    <div class="mb-5">
        <h2 class="text-center mb-4">Преимущества</h2>
        <div class="row">
            <!-- Для любого устройства -->
            <div class="col-12 feature">
                <span class="feature-icon"><i class="bi bi-phone"></i></span>
                <strong>Для любого устройства</strong>
                <p>Можно работать из Telegram с телефона или из браузера за компьютером.</p>
            </div>

            <!-- Без установки и регистрации -->
            <div class="col-12 feature">
                <span class="feature-icon"><i class="bi bi-check-circle"></i></span>
                <strong>Без установки и регистрации</strong>
                <p>Достаточно активировать чат-бот или войти с помощью Telegram-аккаунта.</p>
            </div>

            <!-- Простой интерфейс -->
            <div class="col-12 feature">
                <span class="feature-icon"><i class="bi bi-chat-left-text"></i></span>
                <strong>Простой интерфейс</strong>
                <p>Поставить задачу так же просто и быстро, как отправить сообщение другу.</p>
            </div>

            <!-- Не уйдет из России -->
            <div class="col-12 feature">
                <span class="feature-icon"><i class="bi bi-geo-alt"></i></span>
                <strong>Не уйдет из России</strong>
                <p>Больше не придётся искать другое приложение для планирования дел. OK, Bob! разработан и находится в России.</p>
            </div>

            <!-- Понимает голосовые -->
            <div class="col-12 feature">
                <span class="feature-icon"><i class="bi bi-mic"></i></span>
                <strong>Понимает голосовые</strong>
                <p>Можно поставить задачу голосовым сообщением, если заняты руки. Бот расшифрует и запишет.</p>
            </div>

            <!-- Надежный и безопасный -->
            <div class="col-12 feature">
                <span class="feature-icon"><i class="bi bi-shield-lock"></i></span>
                <strong>Надежный и безопасный</strong>
                <p>Данные с вашими задачами под защитой, а переписка не хранится на сервере.</p>
            </div>
        </div>
    </div>

    <!-- Подвал -->
    <footer>
        <hr>
        <p>&copy; 2023 OK, Bob!. Все права защищены.</p>
    </footer>
</div>

<!-- Bootstrap Icons (для иконок) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
