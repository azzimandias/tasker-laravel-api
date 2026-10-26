<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipInvitation extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'membership_invitations';
    protected $guarded = false;

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_from_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_to_id');
    }
}
