<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'tasks';
    protected $guarded = false;
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'deadline' => 'datetime',
    ];


    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function personalList(): BelongsTo
    {
        return $this->belongsTo(PersonalList::class, 'id_list')
            ->whereNull('personal_lists.deleted_at');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tag_task')
            ->whereNull('tag_task.deleted_at')
            ->whereNull('tags.deleted_at');
    }
}
