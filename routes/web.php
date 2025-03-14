<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'telegram', 'name' => 'telegram.'],function (){
    Route::get('/inbox', [\App\Http\Controllers\Tg\InboxController::class, 'index'])->name('telegram.inbox');
    Route::get('/tasks', [\App\Http\Controllers\Tg\TaskController::class, 'index'])->name('telegram.tasks');
});

Route::view('/', 'front.index');
Route::get('/auth', [\App\Http\Controllers\LoginController::class, 'auth'])->name('auth');
Route::redirect('/login', '/')->name('login');

Route::group(['middleware' => 'auth', 'prefix' => 'crm', 'as' => 'crm.'], function (){
    Route::get('/', [\App\Http\Controllers\Crm\IndexController::class, 'index'])->name('index');
    Route::get('/graphic', [\App\Http\Controllers\Crm\GraphicController::class, 'index'])->name('graphic');
    Route::get('/all', [\App\Http\Controllers\Crm\AllController::class, 'index'])->name('all');
});

Route::view('/admin/login', 'admin.login')->name('admin.login');
Route::post('/admin/login', [\App\Http\Controllers\Admin\LoginController::class, 'login'])->name('admin.login');
Route::group(['middleware' => 'auth', 'prefix' => 'admin'], function (){
    Route::get('/', [\App\Http\Controllers\Admin\IndexController::class, 'index'])->name('admin.users');
});
