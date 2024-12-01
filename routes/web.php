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
//Route::get('/', function () { return view('welcome'); });
Route::get('/dev/create', [DevController::class, 'devCreate']);
//Route::get('/tasks', [TaskController::class, 'tasks']);
Route::get('/lists', [PersonalListController::class, 'lists']);
Route::get('/sortLists', [PersonalListController::class, 'sortLists']);
Route::get('/tags', [TagController::class, 'tags']);
Route::get('/list', [PersonalListController::class, 'personalListTasks']);
Route::get('/tag', [TagController::class, 'taggedTasks']);
Route::get('/user', [UserController::class, 'users']);
Route::post('/createTask', [TaskController::class, 'createTask']);
Route::post('/updateTask', [TaskController::class, 'updateTask']);
Route::post('/deleteTask', [TaskController::class, 'deleteTask']);
Route::post('/saveList', [PersonalListController::class, 'saveList']);
Route::post('/deleteList', [PersonalListController::class, 'deleteList']);
Route::post('/globalSearch', [TaskController::class, 'globalSearch']);
Route::post('/updateUserInfo', [UserController::class, 'updateUserInfo']);
Route::post('/createTag', [TagController::class, 'createTag']);
Route::post('/updateTag', [TagController::class, 'updateTag']);
Route::post('/deleteTagTask', [TagController::class, 'deleteTagTask']);
Route::post('/updateListName', [PersonalListController::class, 'updateListName']);
require __DIR__.'/auth.php';
