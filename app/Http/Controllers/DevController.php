<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Personal_list;
use App\Models\Personal_tag;
use App\Models\Task_Tag;
use JetBrains\PhpStorm\NoReturn;

class DevController extends Controller
{
    #[NoReturn] public function devCreate() : void {
        $devLists = [
            [
                'name' => 'Рутина',
                'count_of_active_tasks' => 0,
                'color' => '#6161a3',
            ],
            [
                'name' => 'Финансы',
                'count_of_active_tasks' => 0,
                'color' => '#56b59c',
            ],
            [
                'name' => 'Тренировки',
                'count_of_active_tasks' => 0,
                'color' => '#87311e',
            ],
            [
                'name' => 'Учеба',
                'count_of_active_tasks' => 0,
                'color' => '#923d6e',
            ],
            [
                'name' => 'Хобби',
                'count_of_active_tasks' => 0,
                'color' => '#bbaa48',
            ],
        ];
        foreach ($devLists as $list) {
            Personal_list::create([
                'name' => $list['name'],
                'count_of_active_tasks' => $list['count_of_active_tasks'],
                'color' => $list['color'],
            ]);
        }

        $devTasks = [
            [
                'name' => 'Разработать "Tasker-app"',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => 0,
                'is_done' => 0,
                'id_list' => 1,
            ],
            [
                'name' => 'Разработать "Tasker-api"',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => 1,
                'is_done' => 0,
                'id_list' => 2,
            ],
            [
                'name' => 'Разработать "Tasker-app"',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => 0,
                'is_done' => 1,
                'id_list' => 2,
            ],
            [
                'name' => 'Разработать "Tasker-api"',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => 1,
                'is_done' => 0,
                'id_list' => 4,
            ],
            [
                'name' => 'Разработать "Tasker-app"',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => 1,
                'is_done' => 0,
                'id_list' => 1,
            ],
            [
                'name' => 'Разработать "Tasker-api"',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => 1,
                'is_done' => 1,
                'id_list' => 3,
            ],
            [
                'name' => 'Разработать "Tasker-app"',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => 0,
                'is_done' => 0,
                'id_list' => 1,
            ],
            [
                'name' => 'Разработать "Tasker-api"',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => 1,
                'is_done' => 0,
                'id_list' => 2,
            ],
            [
                'name' => 'Разработать "Tasker-app"',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => 0,
                'is_done' => 1,
                'id_list' => 2,
            ],
            [
                'name' => 'Разработать "Tasker-api"',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => 1,
                'is_done' => 0,
                'id_list' => 4,
            ],
            [
                'name' => 'Разработать "Tasker-app"',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => 1,
                'is_done' => 0,
                'id_list' => 1,
            ],
            [
                'name' => 'Разработать "Tasker-api"',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => 1,
                'is_done' => 1,
                'id_list' => 3,
            ],
        ];
        foreach ($devTasks as $task) {
            Task::create([
                'name' => $task['name'],
                'description' => $task['description'],
                'deadline' => $task['deadline'],
                'priority' => $task['priority'],
                'url' => $task['url'],
                'is_flagged' => $task['is_flagged'],
                'is_done' => $task['is_done'],
                'id_list' => $task['id_list'],
            ]);
        }

        $devTags = [
            [
                'name' => 'Срочно'
            ],
            [
                'name' => 'тольконеэто'
            ],
            [
                'name' => 'Скозочноебали'
            ],
            [
                'name' => 'деффки'
            ],
            [
                'name' => 'Нерочно'
            ],
            [
                'name' => 'memes'
            ],
        ];
        foreach ($devTags as $tag) {
            Personal_tag::create([
                'name' => $tag['name'],
            ]);
        }

        $devTaskTags = [
            [
                'id_task' => 1,
                'id_tag' => 3,
            ],
            [
                'id_task' => 1,
                'id_tag' => 1,
            ],
            [
                'id_task' => 2,
                'id_tag' => 2,
            ],
            [
                'id_task' => 5,
                'id_tag' => 3,
            ],
            [
                'id_task' => 5,
                'id_tag' => 4,
            ],
        ];
        foreach ($devTaskTags as $tasktag) {
            Task_Tag::create([
                'id_task' => $tasktag['id_task'],
                'id_tag' => $tasktag['id_tag'],
            ]);
        }
        die();
    }
}
