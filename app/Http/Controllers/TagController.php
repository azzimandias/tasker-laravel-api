<?php

namespace App\Http\Controllers;

use App\Models\Personal_list;
use App\Models\Tag;
use App\Models\Tag_Task;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $this->sendPersonalTagsToSocket($response, $_GET['uuid']);
        return json_encode($response);
    }

    public function sendPersonalTagsToSocket($array, $uuid): void
    {
        try {
            Http::post(env('WEBSOCKET').'api/send-new-personal-tags', [
                'room' => 'bigMenuStore',
                'message' => $array,
                'uuid' => $uuid
            ]);
        } catch (\Throwable $e) {
            Log::error('WebSocket failed: ' . $e->getMessage());
        }
    }

    public function taggedTasks() : string {
        $idxs = null;
        $tasks = [];
        $tag = array();
        $tasksByList = [];
        $personal_lists = Personal_list::where('deleted_at', null)->get();
        if(isset($_GET['id'])) { $idxs = $_GET['id']; }
        if((int)$idxs === 0) {
            $tag = array(
                'id' => 0,
                'name' => 'Все теги'
            );
            foreach ($personal_lists as $pl) {
                $tasks = Tag_Task::select('tasks.*')
                    ->join('tasks', 'tag_task.task_id', '=', 'tasks.id')
                    ->where('tasks.id_list', $pl->id)
                    ->where('tag_task.deleted_at', '=', null)
                    ->where('tasks.deleted_at', '=', null)
                    ->groupBy('tasks.id')
                    ->get();
                if ($tasks) {
                    $tasks = $this->addTagsToTasks($tasks);
                    $tasksByList[] = ['personal_list' => $pl,'tasks' => $tasks];
                }
            }
        } else {
            $tag = Tag::find($idxs);
            foreach ($personal_lists as $pl) {
                $tasks = Tag::join('tag_task', 'tags.id', '=', 'tag_task.tag_id')
                    ->join('tasks', 'tag_task.task_id', '=', 'tasks.id')
                    ->where('tasks.id_list', $pl->id)
                    ->where('tag_task.deleted_at', '=', null)
                    ->where('tasks.deleted_at', '=', null)
                    ->where('tags.id', $idxs)
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
        return $tasks->map(function($task) {
            $tags = Tag::select('tags.id', 'tags.name')
                ->join('tag_task','tags.id','=','tag_task.tag_id')
                ->where('tag_task.task_id', '=', $task->id)
                ->where('tag_task.deleted_at', '=', null)
                ->get();
            $possibleTags = Tag::select('tags.id', 'tags.name')
                ->whereNotIn('tags.id', $tags->pluck('id'))
                ->get();
            return [
                'id' => $task->id,
                'name' => $task->name,
                'id_list' => $task->id_list,
                'is_done' => $task->is_done,
                'is_flagged' => $task->is_flagged,
                'description' => $task->description,
                'deadline' => $task->deadline,
                'tags' => $tags,
                'possibleTags' => $possibleTags,
            ];
        });
    }

    public function addTagToTask() : string {
        $body = file_get_contents('php://input');
        $body = json_decode($body);

        $tag_task = new Tag_Task;
        $tag_task->tag_id = $body->tag_id;
        $tag_task->task_id = $body->task_id;
        $tag_task->save();

        $tag = Tag::find($body->tag_id);

        $this->sendAddTagTaskToSocket($tag, $body->task_id, $body->uuid);
        return json_encode($body);
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

        $this->sendTagCreateToSocket($tag, $body->task_id, $body->uuid);
        return json_encode($tag);
    }

    public function updateTag() : string {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $tag = Tag::find($body->tag_id);
        $tag->name = $body->name;
        $tag->save();
        $this->sendUpdateTagToSocket($tag, $body->uuid);
        return json_encode($tag);
    }

    public function deleteTagTask() : void {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $tag = Tag::find($body->tag_id);
        $tag_task = Tag_Task::where('tag_id', $body->tag_id)
            ->where('task_id', $body->task_id);
        $this->sendDeleteTagTaskToSocket($tag, $body->task_id, $body->uuid);
        $tag_task->delete();
    }

    /**
     * Отправка уведомления об добавлении тега к задаче
     */
    protected function sendAddTagTaskToSocket(Tag $tag, $task_id, $uuid): void
    {
        try {
            Http::post(env('WEBSOCKET').'api/updates-on-list', [
                'room' => 'ListViewStore',
                'action' => 'add_tag_task',
                'taskId' => $task_id,
                'tag' => $tag,
                'uuid' => $uuid,
            ]);
        } catch (\Throwable $e) {
            Log::error('WebSocket failed: ' . $e->getMessage());
        }
    }
    /**
     * Отправка уведомления об создании тега и добавления его к задаче
     */
    protected function sendTagCreateToSocket(Tag $tag, $task_id, $uuid): void
    {
        try {
            Http::post(env('WEBSOCKET').'api/updates-on-list', [
                'room' => 'ListViewStore',
                'action' => 'create_tag_task',
                'taskId' => $task_id,
                'tag' => $tag,
                'uuid' => $uuid,
            ]);
        } catch (\Throwable $e) {
            Log::error('WebSocket failed: ' . $e->getMessage());
        }
    }
    /**
     * Отправка уведомления об обновлении тега
     */
    protected function sendUpdateTagToSocket(Tag $tag, $uuid): void
    {
        try {
            Http::post(env('WEBSOCKET').'api/updates-on-list', [
                'room' => 'ListViewStore',
                'action' => 'update_tag',
                'tag' => $tag,
                'uuid' => $uuid,
            ]);
        } catch (\Throwable $e) {
            Log::error('WebSocket failed: ' . $e->getMessage());
        }
    }
    /**
     * Отправка уведомления об удаления тега у задачи
     */
    protected function sendDeleteTagTaskToSocket(Tag $tag, $task_id, $uuid): void
    {
        try {
            Http::post(env('WEBSOCKET').'api/updates-on-list', [
                'room' => 'ListViewStore',
                'action' => 'delete_tag_task',
                'taskId' => $task_id,
                'tag' => $tag,
                'uuid' => $uuid,
            ]);
        } catch (\Throwable $e) {
            Log::error('WebSocket failed: ' . $e->getMessage());
        }
    }
}
