<?php
use App\Http\Controllers\DevController;
use App\Http\Controllers\PersonalListController;
use App\Http\Controllers\MembershipInvitationsController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*Route::get('/dev/create', [DevController::class, 'devCreate']);*/

Route::prefix('auth')->group(function() {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/logout', [UserController::class, 'logout'])
        ->middleware('auth:sanctum');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/user', function (Request $request) {
        return $request->user();
    });

    Route::controller(UserController::class)->group(function() {
        Route::post('/updateUserInfo', 'updateUserInfo');
        Route::post('/findUsers', 'findUsers');
    });

    Route::controller(PersonalListController::class)->group(function() {
        Route::get('/lists', 'lists');
        Route::get('/sortLists', 'sortLists');
        Route::post('/list/{personalList:id}', 'personalListTasksById');
        Route::post('/listName', 'personalListTasksByName');
        Route::post('/createList', 'createList');
        Route::delete('/personalList/{id}', 'deleteList');
        Route::patch('/updateList/{list:id}', 'updateList');
        Route::post('/globalSearch', 'globalSearch');
    });

    Route::controller(TaskController::class)->group(function() {
        Route::post('/createTask', 'createTask');
        Route::patch('/updateTask/{task:id}', 'updateTask');
        Route::delete('/deleteTask/{task:id}', 'deleteTask');
    });

    Route::controller(TagController::class)->group(function() {
        Route::get('/tags', 'tags');
        Route::post('/tag/{tag:id}', 'taggedTasks');
        Route::post('/tag', 'taggedTasks');
        Route::post('/addTagToTask', 'addTagToTask');
        Route::post('/createTag', 'createTag');
        Route::patch('/updateTag/{tag:id}', 'updateTag');
        Route::post('/deleteTagTask', 'deleteTagTask');
        Route::delete('/deleteTag/{tag:id}', 'deleteTag');
    });

    Route::controller(MembershipInvitationsController::class)->group(function() {
        Route::post('/createMembershipInvitation', 'createMembershipInvitation');
    });
});
