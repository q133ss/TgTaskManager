<?php

namespace App\Http\Requests\Tg\TaskController;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'text' => 'required|string',
            'chat_id' => 'required|string',
            'date' => 'nullable|date',
            'assignee_id' => 'nullable|exists:users,id',
            'is_done' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'text.required' => 'Поле "Задача" обязательно для заполнения',
            'text.string' => 'Поле "Задача" должно быть строкой',
            'chat_id.required' => 'Поле "ID чата" обязательно для заполнения',
            'chat_id.string' => 'Поле "ID чата" должно быть строкой',
            'date.date' => 'Поле "Дата" должно быть датой',
            'assignee_id.exists' => 'Пользователь не найден',
            'is_done.boolean' => 'Поле "Выполнено" должно быть булевым значением'
        ];
    }
}
