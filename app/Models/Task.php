<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'tasks';
    protected $guarded = false;

    public function personal_list(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PersonalList::class, 'id_list');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_task', 'task_id', 'tag_id');
    }
}
