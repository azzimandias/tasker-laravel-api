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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('date_create')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('priority')->nullable();
            $table->boolean('is_flagged')->default(0);
            $table->boolean('is_done')->default(0);

            $table->unsignedBigInteger('id_user')->nullable();
            $table->index('id_user', 'task_user_idx');
            $table->foreign('id_user', 'task_user_fk')->on('users')->references('id');

            $table->unsignedBigInteger('id_list')->nullable();
            $table->index('id_list', 'task_list_idx');
            $table->foreign('id_list', 'task_list_fk')->on('personal_lists')->references('id');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
