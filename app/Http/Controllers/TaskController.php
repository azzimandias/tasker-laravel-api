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

    public function updateTask(): void {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
        if (isset($_POST['task'])) {
            //return json_encode($_POST['task']);
        }
        //return 'false';
    }
}
