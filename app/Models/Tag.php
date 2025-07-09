<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'tags';
    protected $guarded = false;

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'tag_task', 'tag_id', 'task_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_tag', 'tag_id', 'user_id');
    }

}
