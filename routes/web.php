<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;

//Route::post('/webhook', [TelegramBotController::class, 'handle']);

Route::group(['prefix' => 'telegram', 'name' => 'telegram.'],function (){
    Route::get('/inbox', [\App\Http\Controllers\Tg\InboxController::class, 'index'])->name('telegram.inbox');
    Route::get('/tasks', [\App\Http\Controllers\Tg\TaskController::class, 'index'])->name('telegram.tasks');
});

/*

1) Груповые чаты
3) Теги!


Фильтрация по тегам:
• /all #bob — выведет все невыполненные задачи с тегом #bob,
• /today #bob #work — покажет сегодняшние задачи, содержащие хотя бы один из этих тегов.
 */
