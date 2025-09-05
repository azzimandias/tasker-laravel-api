<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Task;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use App\Models\PersonalList;
use App\Models\UserList;
use App\Models\TagTask;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;

class PersonalListController extends Controller
{
    public function lists() : string {
        $this->updatePersonalCountOfActiveTasks();
        $user = Auth::user();
        $lists = PersonalList::whereHas('users', fn($q) => $q->where('users.id', $user->id))
            ->whereNull('deleted_at')
            ->get();
        $this->sendPersonalCountOfActiveTasksToSocket($lists, $_GET['uuid']);
        return json_encode($lists);
    }

    private function updatePersonalCountOfActiveTasks() : void {
        $user = Auth::user();
        $personalLists = PersonalList::whereHas('users', fn($q) => $q->where('users.id', $user->id))
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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function sortLists() : string {
        $result = $this->updateSortCountOfActiveTasks();
        $this->sendSortCountOfActiveTasksToSocket($result, $_GET['uuid']);
        return json_encode($result);
    }

    private function updateSortCountOfActiveTasks() : array {
        $user = Auth::user();
        $userId = $user->id;
        return [
            [
                'id' => 1,
                'count' => count(Task::join('personal_lists','tasks.id_list','=','personal_lists.id')
                    ->join('user_list', 'personal_lists.id', '=', 'user_list.list_id')
                    ->where('user_list.user_id', $_GET['user_id'])
                    ->where('personal_lists.deleted_at', null)
                    ->where('tasks.deadline', date('Y-m-d'))
                    ->get()
                )
            ],
            [
                'id' => 2,
                'count' => count(Task::join('personal_lists','tasks.id_list','=','personal_lists.id')
                    ->join('user_list', 'personal_lists.id', '=', 'user_list.list_id')
                    ->where('user_list.user_id', $_GET['user_id'])
                    ->where('personal_lists.deleted_at', null)
                    ->where('is_flagged', 1)
                    ->get()
                )
            ],
            [
                'id' => 3,
                'count' => count(Task::join('personal_lists','tasks.id_list','=','personal_lists.id')
                    ->join('user_list', 'personal_lists.id', '=', 'user_list.list_id')
                    ->where('user_list.user_id', $_GET['user_id'])
                    ->where('personal_lists.deleted_at', null)
                    ->where('is_done', 1)
                    ->get()
                )
            ],
            [
                'id' => 4,
                'count' => count(Task::whereHas('personal_list.users', fn($q) => $q->where('users.id', $userId))
                    ->with('personal_list')
                    ->get()
                )
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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    private function personalList($id_list, $isDone) : object {
        return Task::where('id_list', $id_list)
            ->where('is_done', '=', (int)$isDone)
            ->get()
            ->map(function($task) {
                return (new TaskController)->fullTask($task);
            });
    }

    private function sortList($id_list, $case = null) : object {
        $tasks = Task::select('personal_lists.id as personal_lists_id',
            'personal_lists.name as personal_lists_name',
            'personal_lists.color as color',
            'tasks.id as id',
            'tasks.name as name',
            'tasks.is_done as is_done',
            'tasks.is_flagged as is_flagged',
            'tasks.description as description',
            'tasks.deadline as deadline')
            ->join('personal_lists','tasks.id_list','=','personal_lists.id')
            ->join('user_list', 'personal_lists.id', '=', 'user_list.list_id')
            ->where('user_list.user_id', $_GET['user_id'])
            ->where('personal_lists.id', $id_list)
            ->where('personal_lists.deleted_at', null);
        switch ($case) {
            case 'today':
                $tasks->where('deadline', date('Y-m-d'));
                break;
            case 'with_flag':
                $tasks->where('is_flagged', 1);
                break;
            case 'done':
                $tasks->where('is_done', 1);
                break;
        }
        return $tasks->get()->map(function($task) {
            return (new TaskController)->fullTask($task);;
        });
    }

    public function personalListTasks() : string {
        $this->updatePersonalCountOfActiveTasks();
        $result = [];
        if(isset($_GET['id'])) {
            $list = PersonalList::find($_GET['id']);
            $tasks = $this->personalList($_GET['id'], false);
            $tasksDone = $this->personalList($_GET['id'], true);
            $result = ['list'=>$list, 'tasks'=>$tasks, 'tasksDone'=>$tasksDone];
        } elseif (isset($_GET['name'])) {
            $PersonalLists = PersonalList::where('deleted_at', null)->get();

            switch ($_GET['name']) {
                case 'today':
                    $result = [
                        'sortList' => [
                            'id' => 1,
                            'name' => 'Сегодня'
                        ]
                    ];
                    break;
                case 'with_flag':
                    $result = [
                        'sortList' => [
                            'id' => 2,
                            'name' => 'С флажком'
                        ]
                    ];
                    break;
                case 'done':
                    $result = [
                        'sortList' =>[
                            'id' => 3,
                            'name' => 'Завершено'
                        ]
                    ];
                    break;
                case 'all':
                    $result = [
                        'sortList' =>[
                            'id' => 4,
                            'name' => 'Все'
                        ]
                    ];
                    break;
            }
            $result['tasksByList'] = [];

            foreach ($PersonalLists as $pl) {
                switch ($_GET['name']) {
                    case 'today':
                        $tasks = $this->sortList($pl['id'], 'today');
                        if (count($tasks) > 0) {
                            $result['tasksByList'][] = ['personal_list' => $pl,'tasks' => $tasks];
                        }
                        break;
                    case 'with_flag':
                        $tasks = $this->sortList($pl['id'], 'with_flag');
                        if (count($tasks) > 0) {
                            $result['tasksByList'][] = ['personal_list' => $pl, 'tasks' => $tasks];
                        }
                        break;
                    case 'done':
                        $tasks = $this->sortList($pl['id'], 'done');
                        if (count($tasks) > 0) {
                            $result['tasksByList'][] = ['personal_list' => $pl, 'tasks' => $tasks];
                        }
                        break;
                    case 'all':
                        $tasks = $this->sortList($pl['id']);
                        if (count($tasks) > 0) {
                            $result['tasksByList'][] = ['personal_list' => $pl, 'tasks' => $tasks];
                        }
                        break;
                }
            }
        }
        return json_encode($result);
    }

    public function saveList() : string {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $user = Auth::user();
        $new_list = $body->list;

        $list = new PersonalList;
        $list->name = $new_list->name;
        $list->color = $new_list->color;
        $list->count_of_active_tasks = 0;
        $list->owner_id = $user->id;
        $list->save();

        $user_list = new UserList;
        $user_list->user_id = $new_list->user_id;
        $user_list->list_id = $list->id;
        $user_list->save();

        return json_encode($list);
    }

    public function deleteList() : void {
        $body = file_get_contents('php://input');
        $list = PersonalList::find($body);
        $list->delete();
    }

    public function updateList(PersonalList $list) : void {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $upd_list = $body->list;

        if (isset($upd_list->color)) {
            $list->color = $upd_list->color;
        }
        if (isset($upd_list->name)) {
            $list->name = $upd_list->name;
        }
        $list->save();
        $this->sendListUpdateToSocket($list, $body->uuid);
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
