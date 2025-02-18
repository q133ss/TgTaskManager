<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    @yield('meta')
    <!-- Custom Styles for Dark Theme -->
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Arial', sans-serif;
        }
        .navbar {
            background-color: #1e1e1e;
        }
        .navbar-nav {
            flex-direction: row;
            gap: 10px;
        }
        .sidebar {
            background-color: #181818;
            color: #fff;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar-header {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid #333;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        .sidebar-menu li a {
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            color: #aaa;
            transition: background-color 0.3s;
        }
        .sidebar-menu li a:hover {
            background-color: #2c2c2c;
            color: #fff;
        }
        .task-list {
            list-style: none;
            padding: 0;
        }
        .task-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            margin-bottom: 5px;
            background-color: #222;
            border-radius: 5px;
        }
        .task-item .form-check-input {
            margin-right: 10px;
        }
        .modal {
            --bs-modal-bg: #1e1e1e;
            --bs-modal-color: #fff;
        }
        .modal-content {
            background-color: #1e1e1e;
            color: #fff;
            border: none;
        }
        .modal-header, .modal-footer {
            border-color: #333;
        }
    </style>
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <ul class="navbar-nav">
            @php $currentChatId = \Request()->chat_id; @endphp
            <li class="nav-item"><a class="nav-link" href="{{route('telegram.tasks', ['chat_id' => $currentChatId, 'type' => 'tasks'])}}">Задачи</a></li>
            <li class="nav-item"><a class="nav-link" href="{{route('telegram.tasks', ['chat_id' => $currentChatId, 'type' => 'graphic'])}}">График</a></li>
            <li class="nav-item"><a class="nav-link" href="{{route('telegram.inbox', ['chat_id' => $currentChatId])}}">Инбокс</a></li>
            <li class="nav-item"><a class="nav-link" href="{{route('telegram.tasks', ['chat_id' => $currentChatId, 'type' => 'all'])}}">Все</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#chatModal">Чаты</a></li>
        </ul>
        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
            ☰
        </button>
    </div>
</nav>

<!-- Sidebar Menu (Offcanvas) -->
<div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header sidebar-header">
        <h5></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="sidebar-menu">
            <li><a href="{{route('telegram.tasks', ['chat_id' => $currentChatId, 'type' => 'tasks'])}}">Задачи</a></li>
            <li><a href="{{route('telegram.tasks', ['chat_id' => $currentChatId, 'type' => 'graphic'])}}">График</a></li>
            <li><a href="{{route('telegram.inbox', ['chat_id' => $currentChatId])}}">Инбокс</a></li>
            <li><a href="{{route('telegram.tasks', ['chat_id' => $currentChatId, 'type' => 'all'])}}">Все</a></li>
            <li><a href="#" data-bs-toggle="modal" data-bs-target="#chatModal">Чаты</a></li>
        </ul>
{{--        <h6 class="mt-4">Задачи на сегодня:</h6>--}}
{{--        <ul class="task-list">--}}
{{--            <li class="task-item">--}}
{{--                <div class="form-check">--}}
{{--                    <input class="form-check-input" type="checkbox">--}}
{{--                    <label class="form-check-label">Купить хлеб</label>--}}
{{--                </div>--}}
{{--                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#taskModal">☰</button>--}}
{{--            </li>--}}
{{--        </ul>--}}
    </div>
</div>

<!-- Chat Modal -->
<div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background: #181818">
            <div class="modal-header">
                <h5 class="modal-title" id="chatModalLabel">Используйте всю силу {{env('BOT_USERNAME')}} групповыми чатами
                </h5>
                <button type="button" class="btn-close" id="taskModalClose" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-primary">
                    Добавьте бота {{env('BOT_USERNAME')}} в рабочий чат с коллегами
                </p>
                <p>
                    Бот отправит сообщение о том, что он подключился.
                </p>
                <p class="text-primary">
                    Отметьте бота в сообщении {{env('BOT_USERNAME')}}, чтобы поставить задачу на себя
                </p>
                <small>
                    {{env('BOT_USERNAME')}} моя первая задача в рабочем чате.
                </small>
                <p class="text-primary">
                    Поставьте задачи на коллег
                </p>
                <small>
                    {{env('BOT_USERNAME')}} сверстать страницу подписки @evgeny
                </small>
                <p>
                    У каждого будет создана своя задача: её можно увидеть в общем списке задач чата и в списках личных задач.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Inbox Section -->
<div class="container mt-5">
    @yield('content')
</div>

@yield('footer')
<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
