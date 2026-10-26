<?php

namespace App\Http\Controllers;

use App\Models\MembershipInvitation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MembershipInvitationsController extends Controller
{
    public function findUsers(Request $request): JsonResponse
    {
        $listId = $request->input('listId');
        $searchStr = $request->input('searchStr', '');
        $currentUserId = Auth::id();

        $users = User::query()
            ->where('login', 'like', "%{$searchStr}%")
            ->where('id', '<>', $currentUserId)
            // подгружаем приглашения, которые текущий пользователь им уже отправил
            ->with(['receivedInvitations' => function ($q) use ($currentUserId, $listId) {
                $q->where('user_from_id', $currentUserId)
                    ->where('list_id', $listId);
            }])
            ->get(['id', 'login'])
            ->map(function ($user) {
                return [
                    'value'   => $user->id,
                    'label'   => $user->login,
                    'invited' => $user->receivedInvitations->isNotEmpty(),
                ];
            });

        return response()->json($users);
    }

    public function createMembershipInvitation(Request $request) : JsonResponse
    {
        $listId = $request->input('listId', null);
        $selectedUsers = $request->input('selectedUsers', null);
        if ($listId === null) {
            return response()->json([
                'message' => 'List not found',
            ], 400);
        }
        if ($selectedUsers === null) {
            return response()->json([
                'message' => 'Users not found',
            ], 400);
        }
        foreach ($selectedUsers as $selectedUser) {
            MembershipInvitation::create([
                'list_id' => $listId,
                'user_to_id' => $selectedUser['value'],
                'user_from_id' => Auth::id(),
                'is_approve' => false
            ]);
        }
        return response()->json([
            'message' => 'Invitation created successfully!',
        ]);
    }
}
