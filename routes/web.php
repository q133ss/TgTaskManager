<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'telegram', 'name' => 'telegram.'],function (){
    Route::get('/inbox', [\App\Http\Controllers\Tg\InboxController::class, 'index'])->name('telegram.inbox');
    Route::get('/tasks', [\App\Http\Controllers\Tg\TaskController::class, 'index'])->name('telegram.tasks');
});

Route::view('/', 'front.index');
Route::post('/auth', [\App\Http\Controllers\LoginController::class, 'auth'])->name('auth');
Route::redirect('/login', '/')->name('login');

Route::group(['middleware' => 'auth', 'prefix' => 'crm', 'as' => 'crm.'], function (){
    Route::get('/', [\App\Http\Controllers\Crm\IndexController::class, 'index'])->name('index');
    Route::get('/graphic', [\App\Http\Controllers\Crm\GraphicController::class, 'index'])->name('graphic');
    Route::get('/all', [\App\Http\Controllers\Crm\AllController::class, 'index'])->name('all');
});

Route::get('/log', function (){
    \Auth()->login(\App\Models\User::find(1));
    return 11;
});

/*
 * Делаем фронт!
 * Вход через ТГ
 * Модалки о чате и о помощи!
 *
3) Теги!


Фильтрация по тегам:
• /all #bob — выведет все невыполненные задачи с тегом #bob,
• /today #bob #work — покажет сегодняшние задачи, содержащие хотя бы один из этих тегов.
 */
