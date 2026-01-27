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
        Schema::create('resume', function (Blueprint $table) {
            $table->id();
			$table->string('name');
			$table->unsignedBigInteger('user_id');

			$table->string('export_status')->default('idle'); // idle|queued|processing|ready|failed
			$table->string('export_format')->nullable();      // pdf|docx
			$table->string('export_disk')->nullable();        // public|s3
			$table->string('export_path')->nullable();        // resume_pdfs/...
			$table->text('export_error')->nullable();
			$table->timestamp('export_requested_at')->nullable();
			$table->timestamp('export_ready_at')->nullable();

            $table->timestamps();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
		Schema::create('basic', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('resume_id');
			$table->string('name');
			$table->string('label');
			$table->string('url')->nullable();
			$table->text('image')->nullable();
			$table->string('image_disk')->nullable();
			$table->string('email')->nullable();
			$table->string('phone')->nullable();
			$table->string('website')->nullable();
			$table->string('address')->nullable();
			$table->string('postalCode')->nullable();
			$table->string('city')->nullable();
			$table->string('countryCode')->nullable();
			$table->string('region')->nullable();
			$table->text('summary')->nullable();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
            $table->timestamps();
        });

		Schema::create('profiles', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('basic_id');
			$table->string('network')->nullable();
			$table->string('username')->nullable();
			$table->string('url')->nullable();
			$table->foreign('basic_id')->references('id')->on('basic')->onDelete('cascade');
            $table->timestamps();
        });

		Schema::create('work', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('resume_id');
			$table->string('name');
			$table->string('position');
			$table->string('startDate')->nullable();
			$table->string('endDate')->nullable();
			$table->text('summary')->nullable();
			$table->text('highlights')->nullable();
			$table->unsignedInteger('sort_order')->default(0)->index();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
            $table->timestamps();
        });

		Schema::create('skills', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('resume_id');
			$table->string('name');
			$table->string('level')->nullable();
			$table->unsignedInteger('sort_order')->default(0)->index();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
            $table->timestamps();
        });

		Schema::create('education', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('resume_id');
			$table->string('institution');
			$table->string('area');
			$table->string('studyType')->nullable();;
			$table->string('startDate')->nullable();
			$table->string('endDate')->nullable();
			$table->float('score')->nullable();
			$table->unsignedInteger('sort_order')->default(0)->index();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
            $table->timestamps();
        });

		Schema::create('reference', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('resume_id');
			$table->string('name');
			$table->text('reference');
			$table->unsignedInteger('sort_order')->default(0)->index();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume');
    }
};
