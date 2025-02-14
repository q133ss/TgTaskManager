<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::resource('task', \App\Http\Controllers\Tg\TaskController::class);
Route::post('task/duplicate/{id}/{chat_id}', [\App\Http\Controllers\Tg\TaskController::class, 'duplicate']);
