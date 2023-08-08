<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function devCreate() {
        $date = date('Y-m-d h:i:s');
        $devTasks = [
            [
                'name' => 'Dev "Tasker" app',
                'description' => 'This is front-end app',
                'date_create' => $date,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => 0,
                'is_done' => 0
            ],
            [
                'name' => 'Dev "Tasker" api',
                'description' => 'This is back-end api for "Tasker" app',
                'date_create' => $date,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => 0,
                'is_done' => 0
            ],
            [
                'name' => 'Dev "Tasker" app',
                'description' => 'This is front-end app',
                'date_create' => $date,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => 0,
                'is_done' => 0
            ],
            [
                'name' => 'Dev "Tasker" api',
                'description' => 'This is back-end api for "Tasker" app',
                'date_create' => $date,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => 0,
                'is_done' => 0
            ],
            [
                'name' => 'Dev "Tasker" app',
                'description' => 'This is front-end app',
                'date_create' => $date,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => 0,
                'is_done' => 0
            ],
            [
                'name' => 'Dev "Tasker" api',
                'description' => 'This is back-end api for "Tasker" app',
                'date_create' => $date,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => 0,
                'is_done' => 0
            ],
        ];
        foreach ($devTasks as $task) {
            Task::create([
                'name' => $task['name'],
                'description' => $task['description'],
                'date_create' => $task['date_create'],
                'priority' => $task['priority'],
                'url' => $task['url'],
                'is_flagged' => $task['is_flagged'],
                'is_done' => $task['is_done'],
            ]);
        }
        die();
    }

    public function tasks() {
        header('Access-Control-Allow-Origin: *');
        $response = Task::all();
        return json_encode($response);
    }
}
