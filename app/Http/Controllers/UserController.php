<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function users() : string {
        $user = auth()->user();
        return json_encode($user);
    }
    public function updateUserInfo() : void {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        $user = User::find($body->id);
        $user->email = $body->email;
        $user->name = $body->name;
        $user->surname = $body->surname;
        $user->save();
    }
}
