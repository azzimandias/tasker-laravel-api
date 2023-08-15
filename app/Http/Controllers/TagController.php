<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Tag_Task;
use App\Models\Task;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function tags() : string {
        $response = Tag::all();
        return json_encode($response);
    }

    public function taggedTasks() : string {
        $idxs = null;
        $tasks = [];
        $result = [];
        if(isset($_GET['id'])) { $idxs = $_GET['id']; }
        if((int)$idxs !== 0) {
            $response = Tag::find($idxs);
            $tagName = $response['name'];
            $tasks = $response->tasks;
        } else {
            $response = Tag_Task::select('task_id')
                                    ->where('deleted_at', null)
                                    ->groupBy('task_id')
                                    ->get();
            $tagName = 'Все теги';
            foreach ($response as $res) {
                $tasks[] = Task::find($res['task_id']);
            }
        }
        $result = [$tagName, $tasks];
        return json_encode($result);
    }

}
