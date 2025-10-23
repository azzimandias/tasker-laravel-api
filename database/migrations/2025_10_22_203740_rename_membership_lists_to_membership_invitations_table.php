<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('membership_lists', 'membership_invitations');
    }

    public function down(): void
    {
        Schema::rename('membership_invitations', 'membership_lists');
    }
};
