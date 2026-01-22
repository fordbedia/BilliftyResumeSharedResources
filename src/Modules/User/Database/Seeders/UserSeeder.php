<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Database\Seeders;

use BilliftyResumeSDK\SharedResources\Modules\User\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use BilliftyResumeSDK\SharedResources\SDK\Database\MakeSeeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends MakeSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
		User::create([
			'name' => 'Ed Bedia',
			'email' => 'me@fordbedia.com',
			'password' => bcrypt('123456')
		]);
    }

    /**
     * Revert the database seeds.
     */
    public function revert(): void
    {
        //
    }
}
