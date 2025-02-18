@extends('layouts.app')
@section('title', 'График')
@section('content')
    <style>
        .date-header{
            display: flex;
            justify-content: space-between;
            padding-bottom: 10px;
        }
        .date-header-wrap{
            display: flex;
            gap: 10px;
        }
        .date-btns{
            display: flex;
            gap: 5px;
        }
        .date-picker-header{
            width: 115px;
        }
    </style>

    <div class="main-content row">
    <div class="date-header">
        <!-- Текущая дата -->
        <h5 class="current-date">{{ $currentDate->translatedFormat('l, j F Y') }}</h5>
        <div class="date-header-wrap">
            <div class="date-btns">
                <!-- Кнопка "Назад" -->
                <button id="prev-day" class="btn btn-secondary">←</button>
                <!-- Кнопка "Вперед" -->
                <button id="next-day" class="btn btn-secondary">→</button>
            </div>
            <!-- Кнопка выбора даты -->
            <input type="text" id="date-picker" class="form-control date-picker-header" placeholder="Выберите дату">
        </div>
    </div>

    <hr>

    <!-- Список задач -->
    <div id="task-list">
        @foreach ($allHours as $time => $tasks)
            <strong>{{ $time  }}</strong>
            @php $formattedTime = \Carbon\Carbon::parse($time)->format('H_i'); @endphp
            <ul class="task-list time_{{$formattedTime}}" id="taskList">
                @if (count($tasks) > 0)
                    @foreach ($tasks as $task)
                        <li class="task-item" id="item_{{$task->id}}">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" {{$task->is_done == 1 ? 'checked' : ''}} id="task_check_{{$task->id}}" onclick="taskIsDone('{{$task->id}}', '{{$task->text}}', '{{$task->date}}')">
                                <label class="form-check-label">{{$task->text}}</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" onclick="setTask('{{$task->id}}', '{{$task->text}}', '{{$task->date}}', '{{$formattedTime}}')" data-bs-target="#taskModal">☰</button>
                        </li>
                    @endforeach
                @else
                    <li class="task-item" id="no-items-text">На это время задач нет.</li>
                @endif
            </ul>
        @endforeach
    </div>
    </div>
@endsection
@section('footer')
    <!-- Task Management Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalLabel">Управление задачей</h5>
                    <button type="button" class="btn-close" id="taskModalClose" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <button type="button" class="btn btn-secondary w-100 mb-2" onclick="duplicateTask()">Дублировать</button>
                    <button type="button" class="btn btn-primary w-100 mb-2" id="set-date-btn">Указать дату</button>
                    <!-- Скрытый блок для выбора даты -->
                    <div id="date-picker-container" style="display: none;">
                        <label for="task-date" class="form-label">Выберите дату:</label>
                        <input type="text" class="form-control" id="task-date" placeholder="Выберите дату">
                        <br>
                    </div>
                    <button type="button" class="btn btn-success w-100 mb-2" onclick="taskIsDone()">Завершить</button>
                    <button type="button" class="btn btn-danger w-100" onclick="taskDelete()">Удалить</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    @php $chat_id = auth()->user()->telegram_id; @endphp
    <script>
        let task_id = '';
        let task_text = '';
        let chat_id = '{{$chat_id}}';
        let task_date = '';
        let formatted_time = '';

        function setTask(id, text, date, time_) {
            task_id = id;
            task_text = text;
            task_date = date;
            formatted_time = time_;
        }

        async function duplicateTask() {
            const url = `/api/task/duplicate/${task_id}/${chat_id}`;
            fetch(url, {
                method: 'POST', // Метод запроса
                headers: {
                    'Accept': 'application/json',         // Заголовок Accept
                    'Content-Type': 'application/json'    // Заголовок Content-Type
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Ошибка HTTP! Статус: ${response.status}`);
                    }
                    let data = response.json();
                    return data;
                }).then(data => {
                let new_id = data.id;
                let new_text = data.text;
                let new_date = data.date;
                document.querySelector('.time_'+formatted_time).insertAdjacentHTML('beforeend','<li class="task-item" id="item_'+new_id+'">'+
                    '<div class="form-check">'+
                    '<input class="form-check-input" type="checkbox" id="task_check_'+new_id+'" onclick="taskIsDone('+new_id+', '+new_text+', '+new_date+')">'+
                    '<label class="form-check-label">'+new_text+'</label>'+
                    '</div>'+
                    '<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" onclick="setTask('+new_id+', '+new_text+', '+new_date+')" data-bs-target="#taskModal">☰</button>'+
                    '</li>');
                document.querySelector('#taskModalClose').click();
            })
                .catch(error => {
                    console.error('Произошла ошибка:', error);
                });
        }

        async function updateTask(data) {
            const url = `/api/task/${task_id}`;
            fetch(url, {
                method: 'PATCH', // Метод запроса
                headers: {
                    'Accept': 'application/json',         // Заголовок Accept
                    'Content-Type': 'application/json'    // Заголовок Content-Type
                },
                body: JSON.stringify(data) // Преобразуем данные в JSON
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Ошибка HTTP! Статус: ${response.status}`);
                    }
                    return response.json(); // Парсим ответ как JSON
                })
                .catch(error => {
                    console.error('Произошла ошибка:', error);
                });
        }

        function taskIsDone(taskId = null, taskText = null, taskDate = null) {
            let taskStatus = true;

            if(taskId != null){
                task_id = taskId;
                let checkInput = document.querySelector('#task_check_'+task_id).checked;
                task_text = taskText;
                task_date = taskDate;
                taskStatus = checkInput;
            }else{
                let checkInput = document.querySelector('#task_check_'+task_id).checked;
                taskStatus = !checkInput;
            }

            const isDoneData = {
                chat_id: '{{$chat_id}}', // ID чата
                assignee_id: null,      // ID исполнителя задачи
                text: task_text, // Текст задачи
                date: task_date,  // Дата задачи (формат YYYY-MM-DD)
                is_done: taskStatus        // Статус завершения задачи
            };

            updateTask(isDoneData).then(data => {
                document.querySelector('#task_check_'+task_id).checked = taskStatus;
                document.querySelector('#taskModalClose').click();
            });
        }

        async function taskDelete() {
            let conf = confirm('Вы уверены, что хотите удалить задачу?');
            if(conf){
                const url = `/api/task/${task_id}?chat_id=${chat_id}`;
                fetch(url, {
                    method: 'DELETE', // Метод запроса
                    headers: {
                        'Accept': 'application/json',         // Заголовок Accept
                        'Content-Type': 'application/json'    // Заголовок Content-Type
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Ошибка HTTP! Статус: ${response.status}`);
                        }
                        let data = response.json();
                        return data;
                    }).then(data => {
                    document.querySelector('#item_'+task_id).remove();
                    document.querySelector('#taskModalClose').click();
                })
                    .catch(error => {
                        console.error('Произошла ошибка:', error);
                    });
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Инициализация Flatpickr
            const flatpickrInstance = flatpickr("#task-date", {
                dateFormat: "Y-m-d", // Формат даты
                defaultDate: new Date(), // Установка текущей даты по умолчанию
                locale: "ru", // Локализация
                onChange: function (selectedDates, dateStr, instance) {
                    // selectedDates - массив выбранных дат (в формате Date)
                    // dateStr - выбранная дата в строковом формате (например, "2023-10-15")
                    // instance - экземпляр Flatpickr
                    handleDateChange(dateStr);
                }
            });

            function handleDateChange(selectedDate) {
                const isDoneData = {
                    chat_id: '{{$chat_id}}', // ID чата
                    assignee_id: null,      // ID исполнителя задачи
                    text: task_text, // Текст задачи
                    date: selectedDate,  // Дата задачи (формат YYYY-MM-DD)
                };

                updateTask(isDoneData).then(data => {
                    document.querySelector('#item_'+task_id).remove();
                    document.querySelector('#taskModalClose').click();
                });
            }

            // Показать календарь при клике на "Указать дату"
            document.getElementById('set-date-btn').addEventListener('click', function () {
                const datePickerContainer = document.getElementById('date-picker-container');
                if (datePickerContainer.style.display === 'none') {
                    datePickerContainer.style.display = 'block';
                    // Открыть календарь автоматически
                    flatpickrInstance.open();
                } else {
                    datePickerContainer.style.display = 'none';
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Инициализация календаря
            const flatpickrInstance = flatpickr("#date-picker", {
                dateFormat: "Y-m-d",
                defaultDate: "{{ $currentDate->format('Y-m-d') }}",
                locale: "ru",
                onChange: function (selectedDates, dateStr, instance) {
                    updateDate(dateStr);
                }
            });

            // Функция обновления даты
            function updateDate(newDate) {
                // Обновляем URL страницы с новой датой (в формате ISO для параметров)
                const isoDate = newDate; // Дата остается в ISO-формате для передачи в URL
                window.location.href = '/crm/graphic?date=' + isoDate;
            }

            // Форматирование даты
            function formatDate(dateStr) {
                const date = new Date(dateStr);
                const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
                return date.toLocaleDateString('ru-RU', options);
            }

            // Кнопки "Назад" и "Вперед"
            document.getElementById('prev-day').addEventListener('click', function () {
                const currentDate = new Date("{{ $currentDate->format('Y-m-d') }}");
                currentDate.setDate(currentDate.getDate() - 1);
                updateDate(currentDate.toISOString().split('T')[0]);
            });

            document.getElementById('next-day').addEventListener('click', function () {
                const currentDate = new Date("{{ $currentDate->format('Y-m-d') }}");
                currentDate.setDate(currentDate.getDate() + 1);
                updateDate(currentDate.toISOString().split('T')[0]);
            });
        });
    </script>
@endsection
