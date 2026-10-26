<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function users() : JsonResponse
    {
        $user = auth()->user();
        return response()->json(new UserResource($user));
    }
    public function updateUserInfo(Request $request) : JsonResponse
    {
        $user_email = $request->input('user_email', null);
        $user_name = $request->input('user_name', null);
        $user_surname = $request->input('user_surname', null);

        Auth::user()->update([
            'email'     => $user_email,
            'name'      => $user_name,
            'surname'   => $user_surname,
        ]);
        return response()->json(['message' => 'User info updated']);
    }
}
