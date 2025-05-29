<?php
use App\Http\Controllers\DevController;
use App\Http\Controllers\PersonalListController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/dev/create', [DevController::class, 'devCreate']);

Route::prefix('auth')->group(function() {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/logout', [UserController::class, 'logout'])
        ->middleware('auth:sanctum');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::controller(UserController::class)->group(function() {
        Route::post('/updateUserInfo', 'updateUserInfo');
    });

    Route::controller(PersonalListController::class)->group(function() {
        Route::get('/lists', 'lists');
        Route::get('/sortLists', 'sortLists');
        Route::get('/list', 'personalListTasks');
        Route::post('/saveList', 'saveList');
        Route::post('/deleteList', 'deleteList');
        Route::post('/updateList', 'updateList');
    });

    Route::controller(TaskController::class)->group(function() {
        Route::post('/createTask', 'createTask');
        Route::post('/updateTask/{task}', 'updateTask');
        Route::post('/deleteTask', 'deleteTask');
        Route::post('/globalSearch', 'globalSearch');
    });

    Route::controller(TagController::class)->group(function() {
        Route::get('/tags', 'tags');
        Route::get('/tag', 'taggedTasks');
        Route::post('/addTagToTask', 'addTagToTask');
        Route::post('/createTag', 'createTag');
        Route::post('/updateTag', 'updateTag');
        Route::post('/deleteTagTask', 'deleteTagTask');
    });
});
