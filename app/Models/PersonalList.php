<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonalList extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'personal_lists';
    protected $guarded = false;
    protected $fillable = ['name','color',"owner_id"];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    public function userlist(): HasMany
    {
        return $this->hasMany(UserList::class, "list_id", "id");
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): belongsToMany
    {
        return $this->belongsToMany(User::class, 'user_list', 'list_id', 'user_id')
            ->wherePivotNull('deleted_at');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'id_list')->whereNull('tasks.deleted_at');
    }
}
