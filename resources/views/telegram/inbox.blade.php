@extends('layouts.tg')
@section('meta')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/ru.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <h3>Инбокс</h3>
    <ul class="task-list" id="taskList">
        @foreach($tasks as $task)
        <li class="task-item" id="item_{{$task->id}}">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" {{$task->is_done == 1 ? 'checked' : ''}} id="task_check_{{$task->id}}" onclick="taskIsDone('{{$task->id}}', '{{$task->text}}', '{{$task->date}}')">
                <label class="form-check-label">{{$task->text}}</label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" onclick="setTask('{{$task->id}}', '{{$task->text}}', '{{$task->date}}')" data-bs-target="#taskModal">☰</button>
        </li>
        @endforeach
    </ul>
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


    <script>
        let task_id = '';
        let task_text = '';
        let chat_id = '{{$chat_id}}';
        let task_date = '';

        function setTask(id, text, date) {
            task_id = id;
            task_text = text;
            task_date = date;
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
                    document.querySelector('#taskList').insertAdjacentHTML('beforeend','<li class="task-item" id="item_'+new_id+'">'+
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
@endsection
