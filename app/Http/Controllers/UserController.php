<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function users() : string {
        $user = auth()->user();
        return json_encode($user);
    }
}
