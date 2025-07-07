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
        return $this->belongsTo(PersonalList::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tag_task');
    }
}
