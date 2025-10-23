<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Tag;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;

class TaskController extends Controller
{
    public function tasks(): string
    {
        $response = Task::all();
        return response()->json($response);
    }

    public function updateTask(Request $request, Task $task): JsonResponse
    {
        $updField = $request->input('field');
        if (!$updField || !isset($updField['key'])) {
            return response()->json(['error' => 'Invalid task data'], 400);
        }
        switch ($updField['key']) {
            case 'name':
                $task->name = $updField['value'];
                break;
            case 'description':
                $task->description = $updField['value'];
                break;
            case 'deadline':
                $timestampSeconds = (int) ($updField['value']);
                $task->deadline = $updField['value'] ?
                    Carbon::createFromTimestamp($timestampSeconds)->format('Y-m-d H:i:s') :
                    null;
                break;
            case 'is_flagged':
                $task->is_flagged = (bool) $updField['value'];
                break;
            case 'is_done':
                $task->is_done = (bool) $updField['value'];
                break;
            default:
                return response()->json(['error' => 'Unknown field'], 400);
        }
        $task->save();
        return response()->json([
            'message' => 'Tag updated successfully',
            'task' => new TaskResource($task->fresh())
        ]);
    }

    public function createTask(Request $request): JsonResponse
    {
        $newTask = $request->input('task', null);
        $task = Task::create([
            'name' => $newTask['name'],
            'id_list' => $newTask['id_list'],
            'id_user' => Auth::id(),
        ]);
        $this->sendTaskCreateToSocket($task, $request->query('uuid', null));
        return response()->json(new TaskResource($task));
    }

    public function deleteTask(Request $request, int $id): JsonResponse
    {
        $task = Task::find($id);
        if (!$id) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        //$this->sendTaskDeleteToSocket($task, $body->uuid);
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully']);
    }

    /**
     * Отправка обновления задачи через WebSocket
     */
    protected function sendTaskUpdateToSocket(Task $task, $uuid): void
    {
        $taskData = $this->fullTask($task);

        try {
            Http::post(env('WEBSOCKET').'api/updates-on-list', [
                'room' => 'ListViewStore',
                'action' => 'update_task',
                'listId' => $task->id_list,
                'task' => $taskData,
                'uuid' => $uuid,
            ]);
        } catch (\Throwable $e) {
            Log::error('WebSocket failed: ' . $e->getMessage());
        }
    }

    /**
     * Отправка уведомления об создании задачи
     */
    protected function sendTaskCreateToSocket(Task $task, $uuid): void
    {
        $taskData = $this->fullTask($task);

        try {
            Http::post(env('WEBSOCKET').'api/updates-on-list', [
                'room' => 'ListViewStore',
                'action' => 'create_task',
                'listId' => $task->id_list,
                'task' => $taskData,
                'uuid' => $uuid,
            ]);
        } catch (\Throwable $e) {
            Log::error('WebSocket failed: ' . $e->getMessage());
        }
    }

    /**
     * Отправка уведомления об удалении задачи
     */
    protected function sendTaskDeleteToSocket(Task $task, $uuid): void
    {
        try {
            Http::post(env('WEBSOCKET').'api/updates-on-list', [
                'room' => 'ListViewStore',
                'action' => 'delete_task',
                'listId' => $task->id_list,
                'taskId' => $task->id,
                'uuid' => $uuid,
            ]);
        } catch (\Throwable $e) {
            Log::error('WebSocket failed: ' . $e->getMessage());
        }
    }

    public function fullTask(Task $task): array
    {
        // Загружаем связанные данные
        $task->load(['tags' => function($query) {
            $query->whereNull('tag_task.deleted_at');
        }]);

        // Получаем possibleTags (теги, которые можно добавить)
        /*$usedTagIds = $task->tags->pluck('id')->toArray();
        $user = Auth::user();
        $possibleTags = Tag::whereNull('deleted_at')
            ->whereHas('users', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereNotIn('id', $usedTagIds)
            ->get();*/

        // Формируем полные данные задачи
        return [
            'id' => $task->id,
            'name' => $task->name,
            'id_list' => $task->id_list,
            'is_done' => $task->is_done,
            'is_flagged' => $task->is_flagged,
            'description' => $task->description,
            'deadline' => $task->deadline,
            'tags' => $task->tags,
            'possibleTags' => [],
            'user' => Auth::id()
        ];
    }
}
