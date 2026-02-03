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
        Schema::create('user_info', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id');
            $table->string('phone')->nullable();
			$table->text('avatar')->nullable();
			$table->string('website')->nullable();
			$table->string('address_1')->nullable();
			$table->string('address_2')->nullable();
			$table->text('bio')->nullable();
			$table->timestamps();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_info');
    }
};
