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
        header('Access-Control-Allow-Origin: *');
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

    public function sortLists() : string {
        header('Access-Control-Allow-Origin: *');
        $result = $this->updateSortCountOfActiveTasks();
        return json_encode($result);
    }

    private function updateSortCountOfActiveTasks() : array {
        return [
            [
                'id' => 1,
                'count' => 0 //count(Task::where('created_at', date('Y-m-d h:i:s'))->get()) // for test
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
}
