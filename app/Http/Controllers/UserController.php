<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function users() : string {
        $user = auth()->user();
        return json_encode($user);
    }
    public function updateUserInfo() : void {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $user = User::find($body->user_id);
        $user->email = $body->user_email;
        $user->name = $body->user_name;
        $user->surname = $body->user_surname;
        $user->save();
    }
    public function findUsers(): string {
        $body = json_decode(file_get_contents('php://input'));
        $users = User::where('login', 'like', "%{$body->searchStr}%")
            ->where('id', '<>', Auth::id())
            ->get(['id', 'login'])
            ->map(function ($user) {
                return [
                    'value' => $user->id,
                    'label' => $user->login,
                ];
            });
        return json_encode($users);
    }
}
