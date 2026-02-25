<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Database\Seeders;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Templates;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use BilliftyResumeSDK\SharedResources\SDK\Database\MakeSeeder;

class TemplatesSeeder extends MakeSeeder
{
	protected array $templates = [
		[
			'name' => 'Basic One',
			'slug' => 'basic',
			'description' => 'Clean, minimalist design with a contemporary feel',
			'icon' => 'FileText',
			'colors' => [
			  'primary' => 'hsl(var(--primary))',
			  'accent'  => 'hsl(var(--accent))',
			  'bg'      => 'hsl(var(--background))',
			],
			'path' => 'templates.basic',
		],
		[
			'name' => 'Basic Two',
			'slug' => 'basic-two',
			'description' => 'Clean, minimalist design with a contemporary feel',
			'icon' => 'FileText',
			'colors' => [
			  'primary' => 'hsl(var(--primary))',
			  'accent'  => 'hsl(var(--accent))',
			  'bg'      => 'hsl(var(--background))',
			],
			'path' => 'templates.basic-two',
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
			'plan' => 'pro'
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
		],
		[
			'name' => 'Slate',
			'description' => 'Modern layout perfect for personal or freelance roles. Includes a sidebar for easy access to your contact information and social media links.',
			'icon' => 'Layers',
			'colors' => [
			  'primary' => 'hsl(215 25% 27%)',
			  'accent'  => 'hsl(215 20% 65%)',
			  'bg'      => 'hsl(0 0% 100%)',
			],
			'slug' => 'slate',
			'path' => 'templates.slate',
		],
		[
			'name' => 'Echelon',
			'description' => 'Modern layout perfect for personal or freelance roles. Includes a sidebar for easy access to your contact information and social media links.',
			'icon' => 'Layers',
			'colors' => [
			  'primary' => 'hsl(215 25% 27%)',
			  'accent'  => 'hsl(215 20% 65%)',
			  'bg'      => 'hsl(0 0% 100%)',
			],
			'slug' => 'echelon',
			'path' => 'templates.echelon',
			'plan' => 'pro'
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
