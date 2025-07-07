<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\PersonalList;
use App\Models\Tag;
use App\Models\TagTask;
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
            PersonalList::create([
                'name' => $list['name'],
                'count_of_active_tasks' => $list['count_of_active_tasks'],
                'color' => $list['color'],
            ]);
        }

        $devTasks = [
            [
                'name' => 'Задача 1',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => false,
                'is_done' => false,
                'id_list' => 1,
            ],
            [
                'name' => 'Задача 2',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => true,
                'is_done' => false,
                'id_list' => 2,
            ],
            [
                'name' => 'Задача 3',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => false,
                'is_done' => true,
                'id_list' => 2,
            ],
            [
                'name' => 'Задача 4',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => true,
                'is_done' => false,
                'id_list' => 4,
            ],
            [
                'name' => 'Задача 5',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => true,
                'is_done' => false,
                'id_list' => 1,
            ],
            [
                'name' => 'Задача 6',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => true,
                'is_done' => true,
                'id_list' => 3,
            ],
            [
                'name' => 'Задача 7',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => false,
                'is_done' => false,
                'id_list' => 1,
            ],
            [
                'name' => 'Задача 8',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => true,
                'is_done' => false,
                'id_list' => 2,
            ],
            [
                'name' => 'Задача 9',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => false,
                'is_done' => true,
                'id_list' => 2,
            ],
            [
                'name' => 'Задача 10',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => true,
                'is_done' => false,
                'id_list' => 4,
            ],
            [
                'name' => 'Задача 11',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => false,
                'is_done' => true,
                'id_list' => 1,
            ],
            [
                'name' => 'Задача 12',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => true,
                'is_done' => true,
                'id_list' => 3,
            ],
            [
                'name' => 'Задача 13',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => false,
                'is_done' => false,
                'id_list' => 1,
            ],
            [
                'name' => 'Задача 14',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => true,
                'is_done' => false,
                'id_list' => 2,
            ],
            [
                'name' => 'Задача 15',
                'description' => 'This is front-end app',
                'deadline' => date('Y-m-d'),
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker',
                'is_flagged' => false,
                'is_done' => true,
                'id_list' => 2,
            ],
            [
                'name' => 'Задача 16',
                'description' => 'This is back-end api for "Tasker" app',
                'deadline' => null,
                'priority' => null,
                'url' => 'https://github.com/azzimandias/tasker-laravel-api',
                'is_flagged' => true,
                'is_done' => false,
                'id_list' => 4,
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
            Tag::create([
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
            TagTask::create([
                'task_id' => $tasktag['id_task'],
                'tag_id' => $tasktag['id_tag'],
            ]);
        }
        die();
    }
}
