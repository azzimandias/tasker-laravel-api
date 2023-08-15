<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\Personal_list;
use JetBrains\PhpStorm\NoReturn;

class PersonalListController extends Controller
{
    public function lists() : string {
        $this->updatePersonalCountOfActiveTasks();
        $response = Personal_list::all();
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
            'Сегодня',
            Task::where('deadline', date('Y-m-d'))->get()
        ];
    }
    public function sortListWithFlag() : array {
        return [
            'С флажком',
            Task::where('is_flagged', 1)->get()
        ];
    }
    public function sortListDone() : array {
        return [
            'Завершено',
            Task::where('is_done', 1)->get()
        ];
    }
    public function sortListAll() : array {
        return [
            'Все',
            Task::all()
        ];
    }
    public function personalListTasks() : string {
        $result = '';
        if(isset($_GET['id'])) {
            $response = Personal_list::find($_GET['id']);
            $listName = $response['name'];
            $response = Task::where('id_list', $_GET['id'])->get();
            $result = [$listName, $response];
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
