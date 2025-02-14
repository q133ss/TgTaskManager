<?php

namespace App\Http\Controllers\Tg;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tg\TaskController\TaskRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tasks = [];
        if($request->has('chat_id') && $request->has('type')){
            switch ($request->type) {
                case 'tasks':
                    $tasks = Task::where('chat_id', $request->chat_id)->where('date', '!=', null)->get();
                    break;
                case 'graphic':
                    // Тут будут задачи разделеные по датам и часам
                    return 'graphic';
                    break;
                default:
                    $tasks = Task::where('chat_id', $request->chat_id)->get();
                    break;
            }
            return $tasks;
        }
        abort(403);
    }

    public function duplicate(string $id, string $chat_id)
    {
        $task = Task::findOrFail($id);
        $this->taskCheck($task, $chat_id);
        $newTask = $task->replicate(['text' => $task->text . ' (копия)']);
        $newTask->save();
        return $newTask;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request)
    {
        $task = Task::create($request->validated());
        return $task;
    }

    private function taskCheck(Task $task, string $chat_id)
    {
        $user_id = User::where('telegram_id', $chat_id)->pluck('id')->firstOrFail();
        if($task->creator_id !== $user_id && $task->assignee_id !== $user_id){
            abort(403);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, string $id)
    {
        $task = Task::findOrFail($id);
        $this->taskCheck($task, $request->chat_id);
        $data = $request->validated();
        unset($data['chat_id']);
        $task->update($data);
        return $task;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if($request->has('chat_id')){
            $task = Task::findOrFail($id);
            $this->taskCheck($task, $request->chat_id);
            $task->delete();
            return response()->json(['message' => 'Task deleted']);
        }
        abort(403);
    }
}
