<?php

use App\Http\Controllers\DevController;
use App\Http\Controllers\PersonalListController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});
Route::get('/', function () { return view('welcome'); });
Route::get('/dev/create', [DevController::class, 'devCreate']);
Route::get('/tasks', [TaskController::class, 'tasks']);
Route::get('/lists', [PersonalListController::class, 'lists']);
Route::get('/sortLists', [PersonalListController::class, 'sortLists']);
Route::get('/tags', [TagController::class, 'tags']);
Route::get('/list', [PersonalListController::class, 'personalListTasks']);
Route::get('/tag', [TagController::class, 'taggedTasks']);
Route::get('/user', [UserController::class, 'users']);
Route::post('/updateTask', [TaskController::class, 'updateTask']);
Route::post('/createTask', [TaskController::class, 'createTask']);
Route::post('/deleteTask', [TaskController::class, 'deleteTask']);
Route::post('/saveList', [PersonalListController::class, 'saveList']);
Route::post('/deleteList', [PersonalListController::class, 'deleteList']);
Route::post('/globalSearch', [TaskController::class, 'globalSearch']);
require __DIR__.'/auth.php';
