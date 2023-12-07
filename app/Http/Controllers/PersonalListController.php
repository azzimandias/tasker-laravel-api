<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\Personal_list;
use App\Models\User_List;
use JetBrains\PhpStorm\NoReturn;

class PersonalListController extends Controller
{
    public function lists() : string {
        //$this->updatePersonalCountOfActiveTasks();
        $response = Personal_list::select('personal_lists.*')
            ->join('user_list', 'personal_lists.id', '=', 'user_list.list_id')
            ->where('user_list.user_id',$_GET['user_id'])
            ->get();
        return json_encode($response);
    }

    private function updatePersonalCountOfActiveTasks() : void {
        $personal_lists = Personal_list::all();
        foreach ($personal_lists as $list) {
            $arr = Task::where('id_list', $list['id'])->get();
            $pl = Personal_list::find($list['id']);
            $pl->count_of_active_tasks = count($arr);
            $pl->save();
        }
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function sortLists() : string {
        $result = $this->updateSortCountOfActiveTasks();
        return json_encode($result);
    }

    private function updateSortCountOfActiveTasks() : array {
        return [
            [
                'id' => 1,
                'count' => count(Task::where('deadline', date('Y-m-d'))->get())
            ],
            [
                'id' => 2,
                'count' => count(Task::where('is_flagged', 1)->get())
            ],
            [
                'id' => 3,
                'count' => count(Task::where('is_done', 1)->get())
            ],
            [
                'id' => 4,
                'count' => count(Task::all())
            ],
        ];
    }

    public function sortListToday() : array {
        return [
            [
                'id' => 1,
                'name' => 'Сегодня'
            ],
            Task::where('deadline', date('Y-m-d'))->get()
        ];
    }
    public function sortListWithFlag() : array {
        return [
            [
                'id' => 2,
                'name' => 'С флажком'
            ],
            Task::where('is_flagged', 1)->get()
        ];
    }
    public function sortListDone() : array {
        return [
            [
                'id' => 3,
                'name' => 'Завершено'
            ],
            Task::where('is_done', 1)->get()
        ];
    }
    public function sortListAll() : array {
        return [
            [
                'id' => 4,
                'name' => 'Все'
            ],
            Task::all()
        ];
    }
    public function personalListTasks() : string {
        $result = '';
        if(isset($_GET['id'])) {
            $list = Personal_list::find($_GET['id']);
            $response = Task::where('id_list', $_GET['id'])->get();
            $result = [$list, $response];
        } elseif (isset($_GET['name'])) {
            switch ($_GET['name']) {
                case 'today':
                    $result = $this->sortListToday();
                    break;
                case 'with_flag':
                    $result = $this->sortListWithFlag();
                    break;
                case 'done':
                    $result = $this->sortListDone();
                    break;
                case 'all':
                    $result = $this->sortListAll();
                    break;
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
    }
}
