<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use App\Models\Task;
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

    public function updateTask(Task $task): void
    {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $updTask = $body->task;

        switch ($updTask->name) {
            case 'name':
                $task->name = $updTask->value;
                break;
            case 'description':
                $task->description = $updTask->value;
                break;
            case 'deadline':
                $task->deadline = $updTask->value;
                break;
            case 'is_flagged':
                $task->is_flagged = $updTask->value ? 1 : 0;
                break;
            case 'is_done':
                $task->is_done = $updTask->value ? 1 : 0;
                break;
        }

        $task->save();
        $this->sendTaskUpdateToSocket($task, $body->uuid);
    }

    public function createTask(): string
    {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $new_task = $body->task;
        $task = new Task;
        $task->name = $new_task->name;
        $task->id_list = $new_task->id_list;
        $task->save();
        $this->sendTaskCreateToSocket($task, $body->uuid);
        return json_encode($task);
    }

    public function deleteTask(): void
    {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $task = Task::find($body->id);
        $this->sendTaskDeleteToSocket($task, $body->uuid);
        $task->delete();
    }

    public function globalSearch(): string
    {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $searchString = $body->searchString;
        $tasks = Task::where('name', 'like', "%$searchString%")
            ->where('deleted_at', null)
            ->get();
        return json_encode($tasks);
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

    protected function fullTask(Task $task): array
    {
        // Загружаем связанные данные
        $task->load(['tags' => function($query) {
            $query->whereNull('tag_task.deleted_at');
        }]);

        // Получаем possibleTags (теги, которые можно добавить)
        $usedTagIds = $task->tags->pluck('id')->toArray();
        $possibleTags = Tag::whereNotIn('id', $usedTagIds)->get();

        // Формируем полные данные задачи
        return [
            'key' => mt_rand(),
            'id' => $task->id,
            'name' => $task->name,
            'id_list' => $task->id_list,
            'is_done' => $task->is_done,
            'is_flagged' => $task->is_flagged,
            'description' => $task->description,
            'deadline' => $task->deadline,
            'tags' => $task->tags,
            'possibleTags' => $possibleTags,
        ];
    }
}
