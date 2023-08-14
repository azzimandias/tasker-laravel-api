<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\PersonalListController;
use App\Http\Controllers\TagController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () { return view('welcome'); });
Route::get('/dev/create', [DevController::class, 'devCreate']);
Route::get('/tasks', [TaskController::class, 'tasks']);
Route::get('/lists', [PersonalListController::class, 'lists']);
Route::get('/sortLists', [PersonalListController::class, 'sortLists']);
Route::get('/tags', [TagController::class, 'tags']);
Route::get('/list', [PersonalListController::class, 'personalListTasks']);
Route::get('/tag', [TagController::class, 'taggedTasks']);
Route::get('/updateTask', [TaskController::class, 'updateTask']);
