<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User_List extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'user_list';
    protected $guarded = false;
}
