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
        Schema::create('task__tags', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_task')->nullable();
            $table->index('id_task', 'task_tag_task_idx');
            $table->foreign('id_task', 'task_tag_task_fk')->on('tasks')->references('id');

            $table->unsignedBigInteger('id_tag')->nullable();
            $table->index('id_tag', 'task_tag_tag_idx');
            $table->foreign('id_tag', 'task_tag_tag_fk')->on('personal_tags')->references('id');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task__tags');
    }
};
