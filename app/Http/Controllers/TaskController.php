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
        $task->name = $body->name;
        $task->is_flagged = $body->is_flagged;
        $task->is_done = $body->is_done;
        $task->save();
    }
}
