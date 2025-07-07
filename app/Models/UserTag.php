<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserTag extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'user_tag';
    protected $guarded = false;
    protected $fillable = [
        'user_id',
        'tag_id',
    ];
}
