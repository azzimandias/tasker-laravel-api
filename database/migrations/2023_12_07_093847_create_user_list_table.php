<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_list', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->index('user_id', 'list_user_user_idx');
            $table->foreign('user_id', 'list_user_user_fk')->on('users')->references('id');

            $table->unsignedBigInteger('list_id');
            $table->index('list_id', 'list_user_list_idx');
            $table->foreign('list_id', 'list_user_list_fk')->on('personal_lists')->references('id');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_list');
    }
};
