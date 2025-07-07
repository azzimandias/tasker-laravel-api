<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonalList extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'personal_lists';
    protected $guarded = false;
    protected $fillable = ['name','color',"owner_id"];

    public function userlist(): HasMany
    {
        return $this->hasMany(UserList::class, "list_id", "id");
    }
}
