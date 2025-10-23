<?php

namespace App\Http\Controllers;

use App\Http\Resources\PersonalListResource;
use App\Http\Resources\TaskResource;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\PersonalList;
use App\Models\UserList;
use App\Models\TagTask;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;

class PersonalListController extends Controller
{
    public function lists() : JsonResponse
    {
        $this->updatePersonalCountOfActiveTasks();
        $userId = Auth::id();
        $lists = PersonalList::whereHas('users', fn($q) => $q->where('users.id', $userId))
            ->whereNull('deleted_at')
            ->get();
        $this->sendPersonalCountOfActiveTasksToSocket($lists, $_GET['uuid']);
        return response()->json($lists);
    }
    private function updatePersonalCountOfActiveTasks() : void
    {
        $userId = Auth::id();
        $personalLists = PersonalList::whereHas('users', fn($q) => $q->where('users.id', $userId))
            ->whereNull('deleted_at')
            ->get();

        $activeTasksCounts = Task::whereIn('id_list', $personalLists->pluck('id'))
            ->where('is_done', 0)
            ->selectRaw('id_list, COUNT(*) as count')
            ->groupBy('id_list')
            ->pluck('count', 'id_list');

        PersonalList::whereIn('id', $personalLists->pluck('id'))
            ->update([
                'count_of_active_tasks' => DB::raw('CASE id ' .
                    $personalLists->map(function($list) use ($activeTasksCounts) {
                        return "WHEN {$list->id} THEN " . ($activeTasksCounts[$list->id] ?? 0);
                    })->implode(' ') . ' END')
            ]);
    }
    public function sendPersonalCountOfActiveTasksToSocket($object, $uuid): void
    {
        try {
            $response = Http::post(env('WEBSOCKET').'api/send-new-personal-lists-count', [
                'room' => 'bigMenuStore',
                'message' => $object->toArray(),
                'uuid' => $uuid
            ]);
        } catch (\Throwable $e) {
            Log::error('WebSocket failed: ' . $e->getMessage());
        }
    }

    public function sortLists() : JsonResponse
    {
        $result = $this->updateSortCountOfActiveTasks();
        $this->sendSortCountOfActiveTasksToSocket($result, $_GET['uuid']);
        return response()->json($result);
    }
    private function updateSortCountOfActiveTasks() : array
    {
        $userId = Auth::id();
        $baseQuery = Task::whereHas('personalList.users', fn($q) => $q->where('users.id', $userId));
        return [
            [
                'id' => 1,
                'count' => (clone $baseQuery)->whereDate('deadline', today())->count()
            ],
            [
                'id' => 2,
                'count' => (clone $baseQuery)->where('is_flagged', true)->count()
            ],
            [
                'id' => 3,
                'count' => (clone $baseQuery)->where('is_done', true)->count()
            ],
            [
                'id' => 4,
                'count' => $baseQuery->count()
            ],
        ];
    }
    public function sendSortCountOfActiveTasksToSocket($array, $uuid): void
    {
        try {
            $response = Http::post(env('WEBSOCKET').'api/send-new-sort-lists-count', [
                'room' => 'bigMenuStore',
                'message' => $array,
                'uuid' => $uuid
            ]);
        } catch (\Throwable $e) {
            Log::error('WebSocket failed: ' . $e->getMessage());
        }
    }

    private function tasks($id_list, $isDone): object
    {
        return Task::where('id_list', $id_list)
            ->where('is_done', '=', (int)$isDone)
            ->with(['tags' => function($query) {
                $query->whereNull('tag_task.deleted_at');
            }, 'personalList', 'assignedTo', 'tags'])
            ->get();
    }
    public function personalListTasksById(PersonalList $personalList) : JsonResponse
    {
        $this->updatePersonalCountOfActiveTasks();

        $tasks = $this->tasks($personalList->id, false)->load('personalList', 'assignedTo');
        $tasksDone = $this->tasks($personalList->id, true)->load('personalList', 'assignedTo');
        $personalList->load('owner');
        $result = [
            'list' => new PersonalListResource($personalList),
            'tasks' => TaskResource::collection($tasks),
            'tasksDone' => TaskResource::collection($tasksDone),
        ];

        return response()->json($result);
    }

    private function sortList(int $listId, ?string $case = null): object
    {
        $userId = Auth::id();
        $tasksQuery = Task::with([
            'personalList:id,name,color',
            'tags'
        ])->whereNull('tasks.deleted_at')
          ->whereHas('personalList', function ($query) use ($userId, $listId) {
            $query->whereNull('deleted_at')
                ->where('id', $listId)
                ->whereHas('users', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
            });
        });
        match ($case) {
            'today' => $tasksQuery->whereDate('deadline', now()->toDateString()),
            'with_flag' => $tasksQuery->where('is_flagged', true),
            'done' => $tasksQuery->where('is_done', true),
            default => null,
        };
        return $tasksQuery->get()->map(fn($task) => (new TaskController)->fullTask($task));
    }
    public function personalListTasksByName(Request $request): JsonResponse
    {
        $name = $request->query('name', null);
        if (!$name) {
            return response()->json(['error' => 'Parameter "name" is required'], 400);
        }
        $sortMap = [
            'today'     => ['id' => 1, 'name' => 'Сегодня'],
            'with_flag' => ['id' => 2, 'name' => 'С флажком'],
            'done'      => ['id' => 3, 'name' => 'Завершено'],
            'all'       => ['id' => 4, 'name' => 'Все'],
        ];
        $result = [
            'sortList' => $sortMap[$name] ?? ['id' => 0, 'name' => 'Неизвестно'],
            'tasksByList' => [],
        ];
        $personalLists = PersonalList::whereNull('deleted_at')->get();
        foreach ($personalLists as $pl) {
            $tasks = match ($name) {
                'today'     => $this->sortList($pl->id, 'today'),
                'with_flag' => $this->sortList($pl->id, 'with_flag'),
                'done'      => $this->sortList($pl->id, 'done'),
                default     => $this->sortList($pl->id),
            };
            if ($tasks && $tasks->count() > 0) {
                $result['tasksByList'][] = [
                    'personal_list' => $pl,
                    'tasks' => $tasks,
                ];
            }
        }
        return response()->json($result);
    }

    public function createList(Request $request) : JsonResponse
    {
        $new_list = $request->input('list');
        $list = PersonalList::create([
            'name' => $new_list['name'],
            'color' => $new_list['color'],
            'count_of_active_tasks' => 0,
            'owner_id' => Auth::id(),
        ]);
        $list->userlist()->create([
            'user_id' => Auth::id(),
        ]);
        return response()->json(new PersonalListResource($list));
    }
    public function deleteList(Request $request, int $id) : JsonResponse
    {
        $list = PersonalList::find($id);
        if (!$list) {
            return response()->json(['message' => 'List not found'], 404);
        }
        if ($list->owner_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $list->delete();
        return response()->json(['message' => 'List deleted successfully']);
    }
    public function updateList(Request $request, PersonalList $list) : JsonResponse
    {
        $upd_list = $request->input('list', null);
        $list->update([
            'name'  => $upd_list['name'] ?? $list->name,
            'color' => $upd_list['color'] ?? $list->color,
        ]);
        if ($request->has('uuid')) {
            $this->sendListUpdateToSocket($list, $request->input('uuid'));
        }
        return response()->json([
            'message' => 'List updated successfully',
            'list' => new PersonalListResource($list->fresh())
        ]);
    }

    public function globalSearch(Request $request): JsonResponse
    {
        $searchString = trim($request->input('searchString', ''));
        if ($searchString === '') {
            return response()->json(['error' => 'Search string is required'], 400);
        }
        $userId = Auth::id();

        $personalLists = PersonalList::with(['tasks' => function($query) use ($searchString, $userId) {
            $query->where('name', 'LIKE', '%' . $searchString . '%')
                ->whereHas('personal_list.users', fn($q) => $q->where('users.id', $userId));
        }])->whereHas('users', fn($q) => $q->where('users.id', $userId))
           ->get();

        return response()->json(PersonalListResource::collection($personalLists));
    }

    /**
     * Отправка уведомления об обновлении списка
     */
    protected function sendListUpdateToSocket(PersonalList $list, $uuid): void
    {
        try {
            Http::post(env('WEBSOCKET').'api/updates-on-list', [
                'room' => 'ListViewStore',
                'action' => 'update_list',
                'listId' => $list->id,
                'list' => [
                    'id' => $list->id,
                    'name' => $list->name,
                    'color' => $list->color,
                ],
                'uuid' => $uuid
            ]);
        } catch (RequestException $e) {
            Log::error('Failed to send update to WebSocket');
        }
    }
}
