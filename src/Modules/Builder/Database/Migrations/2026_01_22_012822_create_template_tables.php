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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
			$table->string('name');
			$table->text('description')->nullable();
			$table->string('icon')->nullable();
			$table->string('slug');
			$table->json('colors')->nullable();
			$table->text('path');
			$table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });

		Schema::create('resume_template', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('template_id');
			$table->unsignedBigInteger('resume_id');

			$table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
