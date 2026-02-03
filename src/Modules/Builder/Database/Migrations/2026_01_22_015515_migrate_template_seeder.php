<?php

use BilliftyResumeSDK\SharedResources\Modules\Builder\Database\Seeders\ColorSchemeSeeder;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Database\Seeders\TemplatesSeeder;
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
		(new TemplatesSeeder())->run();
		(new ColorSchemeSeeder())->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
