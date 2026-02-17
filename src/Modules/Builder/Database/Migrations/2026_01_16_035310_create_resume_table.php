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
			$table->string('plan')->default('free');
			$table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });

		Schema::create('color_scheme', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('slug');
			$table->string('primary');
			$table->string('accent');
			$table->timestamps();
		});

        Schema::create('resume', function (Blueprint $table) {
            $table->id();
			$table->string('name');
			$table->unsignedBigInteger('user_id');
			$table->unsignedBigInteger('template_id');
			$table->unsignedBigInteger('color_scheme_id');

			$table->string('export_status')->default('idle'); // idle|queued|processing|ready|failed
			$table->string('export_format')->nullable();      // pdf|docx
			$table->string('export_disk')->nullable();        // public|s3
			$table->string('export_path')->nullable();        // resume_pdfs/...
			$table->text('export_error')->nullable();
			$table->string('email_export_status')->nullable(); // queued|processing|sent|failed
    		$table->text('email_export_error')->nullable();
			$table->timestamp('export_requested_at')->nullable();
			$table->timestamp('export_ready_at')->nullable();

            $table->timestamps();
			$table->softDeletes();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
			$table->foreign('color_scheme_id')->references('id')->on('color_scheme')->onDelete('cascade');
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
			$table->softDeletes();
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
			$table->softDeletes();
            $table->timestamps();
        });

		Schema::create('reference', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('resume_id');
			$table->string('name');
			$table->text('reference');
			$table->unsignedInteger('sort_order')->default(0)->index();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
			$table->softDeletes();
            $table->timestamps();
        });

		// ----------------------------------------------------------------------------
		// Additional Information
		// ----------------------------------------------------------------------------
		Schema::create('certification', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('resume_id');
			$table->text('body');
			$table->tinyInteger('is_active')->default(1);
			$table->timestamps();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
		});
		Schema::create('accomplishment', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('resume_id');
			$table->text('body');
			$table->tinyInteger('is_active')->default(1);
			$table->timestamps();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
		});
		Schema::create('languages', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('resume_id');
			$table->tinyInteger('is_active')->default(1);
			$table->timestamps();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
		});

		Schema::create('language', function (Blueprint $table) {
			$table->unsignedBigInteger('languages_id');
			$table->string('language');
			$table->timestamps();
			$table->softDeletes();
			$table->foreign('languages_id')->references('id')->on('languages')->onDelete('cascade');
		});
		// ----------------------------------------------------------------------------
		// For US Candidate
		// ----------------------------------------------------------------------------
		Schema::create('affiliations', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('resume_id');
			$table->text('body');
			$table->tinyInteger('is_active')->default(1);
			$table->timestamps();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
		});
		Schema::create('interest', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('resume_id');
			$table->text('body');
			$table->tinyInteger('is_active')->default(1);
			$table->timestamps();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
		});
		Schema::create('volunteering', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('resume_id');
			$table->text('body');
			$table->tinyInteger('is_active')->default(1);
			$table->timestamps();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
		});
		Schema::create('websites', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('resume_id');
			$table->tinyInteger('is_active')->default(1);
			$table->timestamps();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
		});
		Schema::create('website', function (Blueprint $table) {
			$table->unsignedBigInteger('websites_id');
			$table->string('url');
			$table->timestamps();
			$table->softDeletes();
			$table->foreign('websites_id')->references('id')->on('websites')->onDelete('cascade');
		});
		Schema::create('project', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('resume_id');
			$table->text('body');
			$table->tinyInteger('is_active')->default(1);
			$table->timestamps();
			$table->foreign('resume_id')->references('id')->on('resume')->onDelete('cascade');
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
