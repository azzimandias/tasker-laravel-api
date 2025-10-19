<?php

namespace App\Http\Controllers;

use App\Models\PersonalList;
use App\Models\Tag;
use App\Models\TagTask;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTag;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    public function tags(User $user) : string {
        $user = Auth::user();
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
        $user = Auth::user();
        if (!$tag) {
            $tagData = ['id' => 0, 'name' => 'Все теги'];
            $lists = PersonalList::with(['tasks.tags'])
                ->whereHas('users', fn($q) => $q->where('users.id', $user->id))
                ->whereNull('deleted_at')
                ->get()
                ->map(function($list) {
                    $list->tasks->map(function($task) {
                        $task->possibleTags = Tag::whereNotIn('id', $task->tags->pluck('id'))
                            ->whereNull('deleted_at')
                            ->get();
                        return $task;
                    });
                    return $list;
                });
        } else {
            $tagData = $tag->only(['id', 'name']);
            $tagId = $tag->id;
            $lists = PersonalList::with(['tasks' => function($query) use ($tagId) {
                $query->whereHas('tags', function($q) use ($tagId) {
                        $q->where('tags.id', $tagId);
                    })->with(['tags']);
            }])->whereHas('users', fn($q) => $q->where('users.id', $user->id))
                ->whereNull('deleted_at')
                ->get()
                ->map(function($list) {
                    $list->tasks->map(function($task) {
                        $task->possibleTags = Tag::whereNotIn('id', $task->tags->pluck('id'))
                            ->whereNull('deleted_at')
                            ->get();
                        return $task;
                    });
                    return $list;
                });
        }
        return json_encode([
            'tag' => $tagData,
            'tasksByList' => $lists,
        ]);
    }

    public function addTagToTask() : string {
        $body = file_get_contents('php://input');
        $body = json_decode($body);

        $tag_task = new TagTask;
        $tag_task->tag_id = $body->id;
        $tag_task->task_id = $body->task_id;
        $tag_task->save();

        $tag = Tag::find($body->id);

        $this->sendAddTagTaskToSocket($tag, $body->task_id, $body->uuid);
        return json_encode($body);
    }

    public function createTag() : string {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $user = Auth::user();

        $tag = new Tag;
        $tag->name = $body->name;
        $tag->save();
        $newTagId = $tag->id;

        $user_tag = new UserTag;
        $user_tag->user_id = $user->id;
        $user_tag->tag_id = $newTagId;
        $user_tag->save();

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
        $tag = Tag::find($body->id);
        $tag_task = TagTask::where('tag_id', $body->id)
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
