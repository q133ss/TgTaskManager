<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Бот-ассистент и трекер задач</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Flatpickr для календаря -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Custom Styles -->
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Arial', sans-serif;
            margin: 0;
            display: flex;
            overflow-x: hidden;
        }

        /* Сайдбар */
        .sidebar {
            background-color: #181818;
            color: #fff;
            height: 100vh;
            width: 250px;
            overflow-y: auto;
            padding: 1rem;
        }

        .sidebar-header {
            display: grid;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #333;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .sidebar-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
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

        .sidebar-days {
            margin-top: 1rem;
        }

        .sidebar-days li {
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .sidebar-days li.active {
            background-color: #2c2c2c;
        }

        /* Основной контент */
        .main-content {
            flex-grow: 1;
            padding: 1rem;
            overflow-y: auto;
        }

        .current-date {
            font-size: 1.2rem;
            margin-bottom: 1rem;
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

        .hour-column {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .hour-task {
            display: grid;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem;
            background-color: #222;
            border-radius: 5px;
        }

        /* Календарь */
        .calendar-container {
            margin-top: 1rem;
        }

        /* Три точки */
        .dropdown-toggle::after {
            content: "•••";
            font-size: 1.2rem;
            margin-left: 0.5rem;
        }

        #calendar{
            background: #181818;
            color: #ffffff;
            border: 1px solid #ffffff;
            border-radius: 20px;
            text-align: center;
        }

        .sidebar-menu li.active a {
            background-color: #2c2c2c; /* Темный фон */
            color: #ffffff; /* Белый текст */
            font-weight: bold; /* Жирный шрифт */
        }
    </style>
</head>
<body>

<!-- Сайдбар -->
<div class="sidebar">
    <!-- Заголовок сайдбара -->
    <div class="sidebar-header">
        <div class="d-flex align-items-center">
            @if(auth()->user()->avatar_url != null)
                <img src="{{auth()->user()->avatar_url}}" alt="Avatar" class="sidebar-avatar">
            @endif
            <div class="ms-2">
                <strong>{{auth()->user()->first_name}} {{auth()->user()->last_name}}</strong><br>
                <span>@</span><span>{{ auth()->user()->username }}</span>
            </div>
        </div>
    </div>

    <!-- Сообщение о тарифе -->
    <p class="text-center mb-3">Ваш тариф не оплачен</p>
    <button class="btn btn-primary w-100 mb-3">Выбрать тариф</button>

    <!-- Меню -->
    <ul class="sidebar-menu">
        <li class="{{ request()->routeIs('crm.index') ? 'active' : '' }}"><a href="{{route('crm.index')}}">Инбокс</a></li>
        <li class="{{ request()->routeIs('crm.graphic') ? 'active' : '' }}"><a href="{{route('crm.graphic')}}">График</a></li>
        <li class="{{ request()->routeIs('crm.all') ? 'active' : '' }}"><a href="{{route('crm.all')}}">Все невыполненные</a></li>
        <li><a href="#" data-bs-toggle="modal" data-bs-target="#chatModal">Чаты</a></li>
    </ul>

    <!-- Дни текущей недели -->
    <h6>Текущая неделя</h6>
    <ul class="sidebar-days">
        @php
            Carbon\Carbon::setLocale('ru');
            $today = Carbon\Carbon::now();
            // Начало недели (с понедельника)
            $startOfWeek = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY);

            // Конец недели (воскресенье)
            $endOfWeek = $today->copy()->endOfWeek(\Carbon\Carbon::MONDAY);
        @endphp
        @for ($i = 0; $i <= 6; $i++)
            @php
                $day = $startOfWeek->copy()->addDays($i);
                if(request()->has('date')){
                    $isActive = \Carbon\Carbon::parse(request()->date)->eq($day);
                }else{
                    $isActive = $day->isToday();
                }
                $currentUrl = request()->fullUrlWithQuery(['date' => $day->format('Y-m-d')]);
            @endphp
            <li class="{{ $isActive ? 'active' : '' }}" onclick="location.href='{{$currentUrl}}'">
                <strong>{{ $day->format('d.m') }}</strong> {{ $day->translatedFormat('l') }}
            </li>
        @endfor
    </ul>
    <!-- Календарь -->
    <div class="calendar-container">
        <input type="text" id="calendar" placeholder="Календарь">
    </div>
</div>

<!-- Основной контент -->
@yield('content')

<!-- Chat Modal -->
<div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background: #181818">
            <div class="modal-header">
                <h5 class="modal-title" id="chatModalLabel">Используйте всю силу {{env('BOT_USERNAME')}} групповыми
                    чатами
                </h5>
                <button type="button" class="btn-close" id="taskModalClose" data-bs-dismiss="modal"
                        aria-label="Close"></button>
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
                    У каждого будет создана своя задача: её можно увидеть в общем списке задач чата и в списках личных
                    задач.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/ru.js"></script>
@yield('footer')
<!-- Инициализация календаря -->
<script>
    @php
        if(request()->has('date')){
            $defaultDate = \Carbon\Carbon::parse(request()->date)->format('d-m-Y');
        }else{
            $defaultDate = \Carbon\Carbon::today();
        }
    @endphp
    flatpickr("#calendar", {
        locale: "ru",
        dateFormat: "d-m-Y",
        defaultDate: "{{ $defaultDate }}",
        onChange: function (selectedDates, dateStr, instance) {
            console.log("Выбрана дата:", dateStr);
        }
    });
</script>
</body>
</html>
