<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\Personal_list;
use App\Models\User_List;
use App\Models\Tag_Task;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\NoReturn;

class PersonalListController extends Controller
{
    public function lists() : string {
        $this->updatePersonalCountOfActiveTasks();
        $response = Personal_list::select('personal_lists.*')
            ->join('user_list', 'personal_lists.id', '=', 'user_list.list_id')
            ->where('user_list.user_id',$_GET['user_id'])
            ->get();
        $this->sendPersonalCountOfActiveTasksToSocket($response);
        return json_encode($response);
    }

    private function updatePersonalCountOfActiveTasks() : void {
        $personal_lists = Personal_list::select('personal_lists.*')
            ->join('user_list', 'personal_lists.id', '=', 'user_list.list_id')
            ->where('user_list.user_id',$_GET['user_id'])
            ->get();
        foreach ($personal_lists as $list) {
            $arr = Task::where('id_list', $list['id'])->get();
            $pl = Personal_list::find($list['id']);
            $pl->count_of_active_tasks = count($arr);
            $pl->save();
        }
    }

    public function sendPersonalCountOfActiveTasksToSocket($object) {
        $response = Http::post('http://localhost:3001/api/send-new-personal-lists-count', [
            'room' => 'personal_lists_count',
            'message' => $object->toArray()
        ]);
        return $response->json();
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function sortLists() : string {
        $result = $this->updateSortCountOfActiveTasks();
        $this->sendSortCountOfActiveTasksToSocket($result);
        return json_encode($result);
    }

    private function updateSortCountOfActiveTasks() : array {
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
                'count' => count(Task::join('personal_lists','tasks.id_list','=','personal_lists.id')
                    ->join('user_list', 'personal_lists.id', '=', 'user_list.list_id')
                    ->where('user_list.user_id', $_GET['user_id'])
                    ->where('personal_lists.deleted_at', null)
                    ->get()
                )
            ],
        ];
    }

    public function sendSortCountOfActiveTasksToSocket($array) {
        $response = Http::post('http://localhost:3001/api/send-new-sort-lists-count', [
            'room' => 'sort_lists_count',
            'message' => $array
        ]);
        return $response->json();
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    private function personalList($id_list, $isDone) : object {
        return Task::where('id_list', $id_list)
            ->where('is_done', '=', (int)$isDone)
            ->get()
            ->map(function($task) {
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

    private function sortList($id_list, $case = null) : object {
        $tasks = Task::select('personal_lists.id as personal_list_id',
            'personal_lists.name as personal_list_name',
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
                    'is_done' => $task->is_done,
                    'is_flagged' => $task->is_flagged,
                    'description' => $task->description,
                    'deadline' => $task->deadline,
                    'personal_list_id' => $task->personal_list_id,
                    'personal_list_name' => $task->personal_list_name,
                    'color' => $task->color,
                    'tags' => $tags,
                    'possibleTags' => $possibleTags,
                ];
        });
    }

    public function personalListTasks() : string {
        $result = [];
        if(isset($_GET['id'])) {
            $list = Personal_list::find($_GET['id']);
            $tasks = $this->personalList($_GET['id'], false);
            $tasksDone = $this->personalList($_GET['id'], true);
            $result = ['list'=>$list, 'tasks'=>$tasks, 'tasksDone'=>$tasksDone];
        } elseif (isset($_GET['name'])) {
            $personal_lists = Personal_list::where('deleted_at', null)->get();

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

            foreach ($personal_lists as $pl) {
                switch ($_GET['name']) {
                    case 'today':
                        $tasks = $this->sortList($pl['id'], 'today');
                        if ($tasks) {
                            $result['tasksByList'][] = ['personal_list' => $pl,'tasks' => $tasks];
                        }
                        break;
                    case 'with_flag':
                        $tasks = $this->sortList($pl['id'], 'with_flag');
                        if ($tasks) {
                            $result['tasksByList'][] = ['personal_list' => $pl, 'tasks' => $tasks];
                        }
                        break;
                    case 'done':
                        $tasks = $this->sortList($pl['id'], 'done');
                        if ($tasks) {
                            $result['tasksByList'][] = ['personal_list' => $pl, 'tasks' => $tasks];
                        }
                        break;
                    case 'all':
                        $tasks = $this->sortList($pl['id']);
                        if ($tasks) {
                            $result['tasksByList'][] = ['personal_list' => $pl, 'tasks' => $tasks];
                        }
                        break;
                }
            }
        }
        return json_encode($result);
    }

    public function saveList() : void {
        $body = file_get_contents('php://input');
        $body = json_decode($body);

        $list = new Personal_list;
        $list->name = $body->name;
        $list->color = $body->color;
        $list->count_of_active_tasks = 0;
        $list->save();

        $user_list = new User_List;
        $user_list->user_id = $body->user_id;
        $user_list->list_id = $list->id;
        $user_list->save();
    }

    public function deleteList() : void {
        $body = file_get_contents('php://input');
        $list = Personal_list::find($body);
        $list->delete();
    }

    public function updateList() : void {
        $body = file_get_contents('php://input');
        $body = json_decode($body);

        $list = Personal_list::find($body->id);
        if (isset($body->color)) {
            $list->color = $body->color;
        }
        if (isset($body->name)) {
            $list->name = $body->name;
        }
        $list->save();
    }
}
