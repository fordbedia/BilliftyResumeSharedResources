<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Database\Seeders;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Templates;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use BilliftyResumeSDK\SharedResources\SDK\Database\MakeSeeder;

class TemplatesSeeder extends MakeSeeder
{
	protected array $templates = [
		[
			'name' => 'Basic',
			'slug' => 'basic',
			'description' => 'Clean, minimalist design with a contemporary feel',
			'icon' => 'FileText',
			'colors' => [
			  'primary' => 'hsl(var(--primary))',
			  'accent'  => 'hsl(var(--accent))',
			  'bg'      => 'hsl(var(--background))',
			],
			'path' => 'templates.resume',
		],
		[
			'name' => 'Moderno One',
			'description' => 'Traditional layout perfect for corporate roles',
			'icon' => 'Layers',
			'colors' => [
			  'primary' => 'hsl(215 25% 27%)',
			  'accent'  => 'hsl(215 20% 65%)',
			  'bg'      => 'hsl(0 0% 100%)',
			],
			'slug' => 'moderno-one',
			'path' => 'templates.moderno-one',
		],
		[
			'name' => 'Simple One',
			'description' => 'Traditional layout perfect for corporate roles',
			'icon' => 'Layers',
			'colors' => [
			  'primary' => 'hsl(215 25% 27%)',
			  'accent'  => 'hsl(215 20% 65%)',
			  'bg'      => 'hsl(0 0% 100%)',
			],
			'slug' => 'simple-one',
			'path' => 'templates.simple-one',
		]
	];
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
		foreach ($this->templates as $template) {
			Templates::query()->create($template);
		}
    }

    /**
     * Revert the database seeds.
     */
    public function revert(): void
    {
        //
    }
}
