<?php

namespace App\Http\Controllers;

use App\Models\PersonalList;
use App\Models\Tag;
use App\Models\TagTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    public function tags(User $user) : string {
        $tags = $user->tags;
        $this->sendPersonalTagsToSocket($tags, $_GET['uuid']);
        return json_encode($tags);
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

    public function taggedTasks(Tag $tag = null): string
    {
        $allTags = Tag::whereNull('deleted_at')->get()->keyBy('id');

        if (!$tag) {
            $tagData = ['id' => 0, 'name' => 'Все теги'];
            $tasks = Task::has('tags')
                ->with([
                    'tags' => function($query) {
                        $query->whereNull('tags.deleted_at');
                        $query->whereNull('tag_task.deleted_at');
                    },
                    'personal_list'
                ])
                ->get();
        } else {
            $tagData = $tag->only(['id', 'name']);
            $tasks = $tag->tasks()
                ->with([
                    'tags' => function($query) {
                        $query->whereNull('tags.deleted_at');
                        $query->whereNull('tag_task.deleted_at');
                    },
                    'personal_list'
                ])
                ->get();
        }

        $tasksByList = [];
        $processedTaskIds = [];

        foreach ($tasks as $task) {
            if (in_array($task->id, $processedTaskIds) || !$task->personal_list) {
                continue;
            }

            $listId = $task->personal_list->id;

            if (!isset($tasksByList[$listId])) {
                $tasksByList[$listId] = [
                    'list' => $task->personal_list,
                    'tasks' => []
                ];
            }
            $uniqueTags = $task->tags->unique('id');
            $currentTagIds = $uniqueTags->pluck('id')->toArray();
            $possibleTags = $allTags->except($currentTagIds)->values();

            $tasksByList[$listId]['tasks'][] = [
                'id' => $task->id,
                'name' => $task->name,
                'description' => $task->description,
                'is_done' => $task->is_done,
                'is_flagged' => $task->is_flagged,
                'deadline' => $task->deadline,
                'tags' => $uniqueTags->map->only(['id', 'name'])->values(),
                'possibleTags' => $possibleTags->map->only(['id', 'name']),
            ];
            $processedTaskIds[] = $task->id;
        }
        return json_encode([
            'tag' => $tagData,
            'tasksByList' => array_values($tasksByList)
        ]);
    }

    public function addTagToTask() : string {
        $body = file_get_contents('php://input');
        $body = json_decode($body);

        $tag_task = new TagTask;
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

        $tagM = Tag::where('name', $body->name)->first();
        if ($tagM) {
            $newTagId = $tagM->id;
        } else {
            $tag = new Tag;
            $tag->name = $body->name;
            $tag->save();
            $newTagId = $tag->id;
        }

        if ($body->task_id) {
            $tag_task = new TagTask;
            $tag_task->tag_id = $newTagId;
            $tag_task->task_id = $body->task_id;
            $tag_task->save();
        }

        $this->sendTagCreateToSocket($tag, $body->task_id, $body->uuid);
        return json_encode($tag);
    }

    public function updateTag(Tag $tag) : string {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $tag->name = $body->name;
        $tag->save();
        $this->sendUpdateTagToSocket($tag, $body->uuid);
        return json_encode($tag);
    }

    public function deleteTagTask() : void {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $tag = Tag::find($body->tag_id);
        $tag_task = TagTask::where('tag_id', $body->tag_id)
            ->where('task_id', $body->task_id);
        $this->sendDeleteTagTaskToSocket($tag, $body->task_id, $body->uuid);
        $tag_task->delete();
    }

    public function deleteTag(Tag $tag) : void {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $tag_task = TagTask::where('tag_id', $body->id);
        $this->sendDeleteTagToSocket($tag, $body->uuid);
        $tag_task->delete();
        $tag->delete();
    }

    /**
     * Отправка уведомления об добавлении тега к задаче
     */
    protected function sendAddTagTaskToSocket(Tag $tag, $task_id, $uuid) : void
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
    protected function sendTagCreateToSocket(Tag $tag, $task_id, $uuid) : void
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
    protected function sendUpdateTagToSocket(Tag $tag, $uuid) : void
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
    protected function sendDeleteTagTaskToSocket(Tag $tag, $task_id, $uuid) : void
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
    /**
     * Отправка уведомления об удаления тега
     */
    protected function sendDeleteTagToSocket(Tag $tag, $uuid) : void
    {
        try {
            Http::post(env('WEBSOCKET').'api/updates-on-list', [
                'room' => 'ListViewStore',
                'action' => 'delete_tag',
                'tag' => $tag,
                'uuid' => $uuid,
            ]);
        } catch (\Throwable $e) {
            Log::error('WebSocket failed: ' . $e->getMessage());
        }
    }
}
