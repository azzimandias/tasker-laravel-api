<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personal_list extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'personal_lists';
    protected $guarded = false;
    protected $fillable = ['name','color'];
}
