<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Database\Seeders;

class DatabaseSeeder
{
	public function run(): void
	{
		$this->call([
			ResumeSeeder::class,
		]);
	}
}