<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MembershipInvitationsController extends Controller
{
    public function createMembershipInvitation() : object
    {
        return response()->json([
            'message' => 'Invitation created successfully!',
        ]);
    }
}
