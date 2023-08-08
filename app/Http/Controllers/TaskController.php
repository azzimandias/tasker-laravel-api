<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use JetBrains\PhpStorm\NoReturn;

class TaskController extends Controller
{
    public function tasks() : string {
        header('Access-Control-Allow-Origin: *');
        $response = Task::all();
        return json_encode($response);
    }
}
