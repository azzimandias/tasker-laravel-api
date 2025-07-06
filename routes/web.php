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

Route::controller(PersonalListController::class)->group(function() {
    Route::get('/updateOwners', 'updateOwners'); // для проставления id_owner после миграции
});

Route::controller(TagController::class)->group(function() {
    Route::get('/createUserTagConnection', 'createUserTagConnection'); // для добавления записей в user_tag
});

require __DIR__.'/auth.php';
