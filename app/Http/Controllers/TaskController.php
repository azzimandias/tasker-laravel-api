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

        switch ($body->name) {
            case 'name':
                $task->name = $body->value;
                break;
            case 'description':
                $task->description = $body->value;
                break;
            case 'deadline':
                $task->deadline = $body->value;
                break;
            case 'is_flagged':
                $task->is_flagged = $body->value ? 1 : 0;
                break;
            case 'is_done':
                $task->is_done = $body->value ? 1 : 0;
                break;
        }

        $task->save();
        $this->sendTaskUpdateToSocket($task);
    }

    public function createTask(): string
    {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $task = new Task;
        $task->name = $body->name;
        $task->id_list = $body->id_list;
        $task->save();
        $this->sendTaskCreateToSocket($task);
        return json_encode($task);
    }

    public function deleteTask(): void
    {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $task = Task::find($body->id);
        $this->sendTaskDeleteToSocket($task);
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
    protected function sendTaskUpdateToSocket(Task $task): void
    {
        $taskData = $this->fullTask($task);

        try {
            Http::post(env('WEBSOCKET').'api/updates-on-list', [
                'action' => 'update_task',
                'listId' => $task->id_list,
                'task' => $taskData
            ]);
        } catch (\Throwable $e) {
            Log::error('WebSocket failed: ' . $e->getMessage());
        }
    }

    /**
     * Отправка уведомления об создании задачи
     */
    protected function sendTaskCreateToSocket(Task $task): void
    {
        $taskData = $this->fullTask($task);

        try {
            Http::post(env('WEBSOCKET').'api/updates-on-list', [
                'action' => 'create_task',
                'listId' => $task->id_list,
                'taskId' => $taskData
            ]);
        } catch (\Throwable $e) {
            Log::error('WebSocket failed: ' . $e->getMessage());
        }
    }

    /**
     * Отправка уведомления об удалении задачи
     */
    protected function sendTaskDeleteToSocket(Task $task): void
    {
        try {
            Http::post(env('WEBSOCKET').'api/updates-on-list', [
                'action' => 'delete_task',
                'listId' => $task->id_list,
                'taskId' => $task->id
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
