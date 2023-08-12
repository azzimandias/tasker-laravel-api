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
        Schema::create('tag_task', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tag_id')->nullable();
            $table->index('tag_id', 'task_tag_tag_idx');
            $table->foreign('tag_id', 'task_tag_tag_fk')->on('tags')->references('id');

            $table->unsignedBigInteger('task_id')->nullable();
            $table->index('task_id', 'task_tag_task_idx');
            $table->foreign('task_id', 'task_tag_task_fk')->on('tasks')->references('id');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_task');
    }
};
