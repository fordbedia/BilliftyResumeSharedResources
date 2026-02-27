<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_strength', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('resume_id')->unique();
            $table->unsignedTinyInteger('score')->default(0);
            $table->string('grade', 32)->default('Weak');
            $table->boolean('passed')->default(false)->index();
            $table->json('feedback')->nullable();
            $table->json('notes')->nullable();
            $table->string('scorer_version', 32)->default('v1.0');
            $table->timestamp('scored_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'resume_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_strength');
    }
};
