<?php

namespace App\Http\Controllers;

use App\Models\Personal_tag;
use Illuminate\Http\Request;

class PersonalTagController extends Controller
{
    public function tags() : string {
        header('Access-Control-Allow-Origin: *');
        $response = Personal_tag::all();
        return json_encode($response);
    }
}
