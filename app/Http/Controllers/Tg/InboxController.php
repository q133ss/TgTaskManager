<?php

namespace App\Http\Controllers\Tg;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function index(Request $request)
    {
        if($request->has('chat_id')){
            $user = User::where('telegram_id', $request->chat_id)->firstOrFail();
            $tasks = $user->tasks()->whereNull('date')->get();
            $chat_id = $user->telegram_id;
            return view('telegram.inbox', compact('tasks', 'chat_id'));
        }
        abort(403);
    }
}
