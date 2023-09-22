<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use JetBrains\PhpStorm\NoReturn;

class TaskController extends Controller
{
    public function tasks() : string {
        $response = Task::all();
        return json_encode($response);
    }

    public function updateTask(): void {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $task = Task::find($body->id);
        switch ($body->name) {
            case 'name':
                $task->name = $body->value;
                break;
            case 'description':
                $task->description = $body->value;
                break;
            case 'deadline':
                $task->deadline = $body->value;
                break;
            case 'is_flagged':
                $task->is_flagged = $body->value;
                break;
            case 'is_done':
                $task->is_done = $body->value;
                break;
        }
        $task->save();
    }

    public function createTask() : string {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $task = new Task;
        $task->name = $body->name;
        $task->id_list = $body->id_list;
        $task->save();
        return json_encode($task);
    }
}
