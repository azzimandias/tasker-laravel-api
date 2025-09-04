<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DevController;
use App\Http\Controllers\PersonalListController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/hello', function () {
    return ['Hello World!'];
});

Route::get('/dev/create', [DevController::class, 'devCreate']);

require __DIR__.'/auth.php';
