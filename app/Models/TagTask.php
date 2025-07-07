<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TagTask extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'tag_task';
    protected $guarded = false;
}
