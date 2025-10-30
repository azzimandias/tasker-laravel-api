<?php

namespace App\Http\Controllers;

use App\Http\Resources\PersonalListResource;
use App\Http\Resources\TagResource;
use App\Models\PersonalList;
use App\Models\Tag;
use App\Models\TagTask;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTag;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    public function tags() : JsonResponse
    {
        $user = Auth::user();
        $tags = $user->tags;
        //$this->sendPersonalTagsToSocket($tags, $_GET['uuid']);
        return response()->json(TagResource::collection($tags));
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
        if (!$tag) {
            $tagData = ['id' => 0, 'name' => 'Все теги'];
            $lists = PersonalList::with(['tasks.tags'])
                ->whereHas('users', fn($q) => $q->where('users.id', Auth::id()))
                ->whereNull('deleted_at')
                ->get();
        } else {
            $tagData = $tag->only(['id', 'name']);
            $tagId = $tag->id;
            $lists = PersonalList::with(['tasks' => function($query) use ($tagId) {
                $query->whereHas('tags', function($q) use ($tagId) {
                        $q->where('tags.id', $tagId);
                    })->with(['tags']);
            }])->whereHas('users', fn($q) => $q->where('users.id', Auth::id()))
                ->whereNull('deleted_at')
                ->get();
        }
        return json_encode([
            'tag' => $tagData,
            'tasksByList' => PersonalListResource::collection($lists),
        ]);
    }

    public function addTagToTask(Request $request): JsonResponse
    {
        $tagId = $request->input('id');
        $taskId = $request->input('task_id');
        $task = Task::findOrFail($taskId);
        $tag = Tag::findOrFail($tagId);
        $task->tags()->syncWithoutDetaching([$tagId]);
        $this->sendAddTagTaskToSocket($tag, $taskId, $request->input('uuid'));
        return response()->json(new TagResource($tag));
    }

    public function createTag(Request $request): JsonResponse
    {
        $newTagName = $request->input('name');
        $taskId = $request->input('task_id');
        $tag = Tag::create([
            'name' => $newTagName,
        ]);
        $tag->users()->syncWithoutDetaching([Auth::id()]);
        if ($taskId) {
            $task = Task::findOrFail($taskId);
            $task->tags()->syncWithoutDetaching([$tag->id]);
        }
        $this->sendTagCreateToSocket($tag, $taskId, $request->input('uuid'));
        return response()->json(new TagResource($tag));
    }

    public function updateTag(Request $request, Tag $tag) : JsonResponse
    {
        $newTagName = $request->input('name');
        $tag->update([
            'name'  => $newTagName ?? $tag->name,
        ]);
        $this->sendUpdateTagToSocket($tag, $request->input('uuid'));
        return response()->json([
            'message' => 'Tag updated successfully',
            'tag' => new TagResource($tag->fresh())
        ]);
    }

    public function deleteTagTask(Request $request) : JsonResponse
    {
        $tagId = $request->input('id');
        $taskId = $request->input('task_id');
        $tag = Tag::find($tagId);
        $tag_task = TagTask::where('tag_id', $tagId)
            ->where('task_id', $taskId);
        $this->sendDeleteTagTaskToSocket($tag, $taskId, $request->input('uuid'));
        $tag_task->delete();
        return response()->json(['message' => 'Tag removed from task successfully']);
    }

    public function deleteTag(int $id) : JsonResponse
    {
        $tag = Tag::find($id);
        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }
        $tag_task = TagTask::where('tag_id', $id);
        //$this->sendDeleteTagToSocket($tag, $body->uuid);
        $tag_task->delete();
        $tag->delete();
        return response()->json(['message' => 'Tag deleted successfully']);
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
