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
        Schema::create('membership_lists', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_approve')->default(false);

            $table->unsignedBigInteger('user_from_id')->nullable();
            $table->foreign('user_from_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->unsignedBigInteger('user_to_id')->nullable();
            $table->foreign('user_to_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->unsignedBigInteger('list_id')->nullable();
            $table->foreign('list_id')
                ->references('id')->on('personal_lists')
                ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_lists');
    }
};
