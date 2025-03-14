<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список пользователей</title>
    <!-- Подключение Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Дополнительные стили для бокового меню */
        .sidebar {
            height: 100vh;
            background-color: #f8f9fa;
            border-right: 1px solid #e9ecef;
        }
        .sidebar .nav-link {
            color: #495057;
        }
        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: #ffffff;
        }
        .content {
            padding: 20px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Боковое меню -->
        <nav class="col-md-2 sidebar d-flex flex-column justify-content-between">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#">Пользователи</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Настройки</a>
                </li>
            </ul>
            <div class="text-center p-3">
                <small>&copy; 2023 Ваш проект</small>
            </div>
        </nav>

        <!-- Основное содержимое -->
        <main class="col-md-10 content">
            <h2>Список пользователей</h2>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Аватар</th>
                    <th>Имя</th>
                    <th>Фамилия</th>
                    <th>Ник</th>
                    <th>Telegram ID</th>
                    <th>Статус подписки</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td><img src="https://via.placeholder.com/40" alt="Аватар" class="user-avatar"></td>
                    <td>Иван</td>
                    <td>Иванов</td>
                    <td>@ivan_ivanov</td>
                    <td>123456789</td>
                    <td><span class="badge bg-success">Активна</span></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td><img src="https://via.placeholder.com/40" alt="Аватар" class="user-avatar"></td>
                    <td>Мария</td>
                    <td>Петрова</td>
                    <td>@maria_petrova</td>
                    <td>987654321</td>
                    <td><span class="badge bg-danger">Неактивна</span></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td><img src="https://via.placeholder.com/40" alt="Аватар" class="user-avatar"></td>
                    <td>Алексей</td>
                    <td>Сидоров</td>
                    <td>@alex_sidorov</td>
                    <td>555555555</td>
                    <td><span class="badge bg-warning text-dark">Ожидание</span></td>
                </tr>
                </tbody>
            </table>
        </main>
    </div>
</div>

<!-- Подключение Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
