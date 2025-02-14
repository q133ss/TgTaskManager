<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook', [TelegramBotController::class, 'handle']);

Route::group(['prefix' => 'telegram'],function (){
    Route::view('/inbox', 'telegram.inbox')->name('telegram.inbox');
});

/*
 • /all — показать все невыполненные задачи,
• /today — показать задачи на сегодня,
• /tomorrow — задачи на завтра,
• /inbox — задачи без указанной даты (из «Инбокса»),
•/help — помощь.

Фильтрация по тегам:
• /all #bob — выведет все невыполненные задачи с тегом #bob,
• /today #bob #work — покажет сегодняшние задачи, содержащие хотя бы один из этих тегов.
 */
