<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница входа</title>
    <!-- Подключение Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Дополнительные стили для центрирования формы */
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="login-container">
        <h2>Вход</h2>
        <form method="post" action="{{ route('admin.login') }}">
            @csrf
            <!-- Поле для ввода email -->
            <div class="mb-3">
                <label for="email" class="form-label">Логин</label>
                <input type="text" name="name" class="form-control" id="email" placeholder="Введите ваш логин" required>
            </div>
            <!-- Поле для ввода пароля -->
            <div class="mb-3">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" name="password" class="form-control" id="password" placeholder="Введите ваш пароль" required>
            </div>
            <!-- Кнопка входа -->
            <button type="submit" class="btn btn-primary w-100">Войти</button>
        </form>
    </div>
</div>

<!-- Подключение Bootstrap JS (необходим для некоторых компонентов) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
