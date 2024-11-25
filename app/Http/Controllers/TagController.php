<?php

namespace App\Http\Controllers;

use App\Models\Personal_list;
use App\Models\Tag;
use App\Models\Tag_Task;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function tags() : string {
        $response = User::select('tags.id as id', 'tags.name as name')
            ->join('user_list', 'users.id', '=', 'user_list.user_id')
            ->join('personal_lists', 'user_list.list_id', '=', 'personal_lists.id')
            ->join('tasks', 'personal_lists.id', '=', 'tasks.id_list')
            ->join('tag_task', 'tasks.id', '=', 'tag_task.task_id')
            ->join('tags', 'tag_task.tag_id', '=', 'tags.id')
            ->where('users.id', $_GET['user_id'])
            ->groupBy('tags.id')
            ->get();
        return json_encode($response);
    }

    public function taggedTasks() : string {
        $idxs = null;
        $tasks = [];
        $tag = array();
        $tasksByList = [];
        $personal_lists = Personal_list::where('deleted_at', null)->get();
        if(isset($_GET['id'])) { $idxs = $_GET['id']; }
        if((int)$idxs !== 0) {
            $tag = Tag::find($idxs);
            foreach ($personal_lists as $pl) {
                $tasks = Tag::join('tag_task', 'tags.id', '=', 'tag_task.tag_id')
                    ->join('tasks', 'tag_task.task_id', '=', 'tasks.id')
                    ->where('tasks.id_list', $pl->id)
                    ->where('tag_task.deleted_at', '=', null)
                    ->where('tags.id', $idxs)
                    ->get();
                if ($tasks) {
                    $tasks = $this->addTagsToTasks($tasks);
                    $tasksByList[] = ['personal_list' => $pl,'tasks' => $tasks];
                }
            }
        } else {
            $tag = array('name' => 'Все теги');
            foreach ($personal_lists as $pl) {
                $tasks = Tag_Task::select('tasks.*')
                    ->join('tasks', 'tag_task.task_id', '=', 'tasks.id')
                    ->where('tasks.id_list', $pl->id)
                    ->where('tag_task.deleted_at', '=', null)
                    ->groupBy('tasks.id')
                    ->get();
                if ($tasks) {
                    $tasks = $this->addTagsToTasks($tasks);
                    $tasksByList[] = ['personal_list' => $pl,'tasks' => $tasks];
                }
            }
        }
        return json_encode(array(
            'tag' => $tag,
            'tasksByList' => $tasksByList
        ));
    }

    private function addTagsToTasks($tasks) : object {
        for ($i = 0; $i < count($tasks); $i++) {
            $tags = Tag::select('tags.id', 'tags.name')
                ->join('tag_task','tags.id','=','tag_task.tag_id')
                ->where('tag_task.task_id', '=', $tasks[$i]->id)
                ->where('tag_task.deleted_at', '=', null)
                ->get();
            $tasks[$i]->tags = $tags;
        }
        return $tasks;
    }

    public function createTag() : string {
        $body = file_get_contents('php://input');
        $body = json_decode($body);

        $tag = new Tag;
        $tag->name = $body->name;
        $tag->save();
        $newTagId = $tag->id;

        $tag_task = new Tag_Task;
        $tag_task->tag_id = $newTagId;
        $tag_task->task_id = $body->task_id;
        $tag_task->save();

        return json_encode($tag);
    }

    public function updateTag() : string {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $tag = Tag::find($body->tag_id);
        $tag->name = $body->name;
        $tag->save();
        return json_encode($tag);
    }

    public function deleteTagTask() : void {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $tag_task = Tag_Task::where('tag_id', $body->tag_id)
            ->where('task_id', $body->task_id);
        $tag_task->delete();
    }
}
