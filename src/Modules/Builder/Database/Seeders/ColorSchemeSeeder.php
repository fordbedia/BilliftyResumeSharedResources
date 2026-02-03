<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Database\Seeders;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\ColorScheme;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use BilliftyResumeSDK\SharedResources\SDK\Database\MakeSeeder;

class ColorSchemeSeeder extends MakeSeeder
{
	protected array $colorSchemes = [
		[
			'id' => 1,
			'slug' => 'teal',
			'name' => 'Teal',
			'primary' => 'hsl(173 58% 39%)',
			'accent' => 'hsl(173 58% 49%)',
		],
		[
			'id' => 2,
			'slug' => 'blue',
			'name' => 'Blue',
			'primary' => 'hsl(217 91% 60%)',
			'accent' => 'hsl(217 91% 70%)',
		],
		[
			'id' => 3,
			'slug' => 'indigo',
			'name' => 'Indigo',
			'primary' => 'hsl(239 84% 67%)',
			'accent' => 'hsl(239 84% 77%)',
		],
		[
			'id' => 4,
			'slug' => 'purple',
			'name' => 'Purple',
			'primary' => 'hsl(280 65% 60%)',
			'accent' => 'hsl(280 65% 70%)',
		],
		[
			'id' => 5,
			'slug' => 'pink',
			'name' => 'Pink',
			'primary' => 'hsl(330 81% 60%)',
			'accent' => 'hsl(330 81% 70%)',
		],
		[
			'id' => 6,
			'slug' => 'rose',
			'name' => 'Rose',
			'primary' => 'hsl(350 89% 60%)',
			'accent' => 'hsl(350 89% 70%)',
		],
		[
			'id' => 7,
			'slug' => 'red',
			'name' => 'Red',
			'primary' => 'hsl(0 84% 60%)',
			'accent' => 'hsl(0 84% 70%)',
		],
		[
			'id' => 8,
			'slug' => 'orange',
			'name' => 'Orange',
			'primary' => 'hsl(25 95% 53%)',
			'accent' => 'hsl(25 95% 63%)',
		],
		[
			'id' => 9,
			'slug' => 'amber',
			'name' => 'Amber',
			'primary' => 'hsl(38 92% 50%)',
			'accent' => 'hsl(38 92% 60%)',
		],
		[
			'id' => 10,
			'slug' => 'yellow',
			'name' => 'Yellow',
			'primary' => 'hsl(48 96% 53%)',
			'accent' => 'hsl(48 96% 63%)',
		],
		[
			'id' => 11,
			'slug' => 'lime',
			'name' => 'Lime',
			'primary' => 'hsl(84 81% 44%)',
			'accent' => 'hsl(84 81% 54%)',
		],
		[
			'id' => 12,
			'slug' => 'green',
			'name' => 'Green',
			'primary' => 'hsl(142 71% 45%)',
			'accent' => 'hsl(142 71% 55%)',
		],
		[
			'id' => 13,
			'slug' => 'emerald',
			'name' => 'Emerald',
			'primary' => 'hsl(160 84% 39%)',
			'accent' => 'hsl(160 84% 49%)',
		],
		[
			'id' => 14,
			'slug' => 'cyan',
			'name' => 'Cyan',
			'primary' => 'hsl(189 94% 43%)',
			'accent' => 'hsl(189 94% 53%)',
		],
		[
			'id' => 15,
			'slug' => 'slate',
			'name' => 'Slate',
			'primary' => 'hsl(215 25% 27%)',
			'accent' => 'hsl(215 20% 47%)',
		],
		[
			'id' => 16,
			'slug' => 'gray',
			'name' => 'Gray',
			'primary' => 'hsl(220 9% 46%)',
			'accent' => 'hsl(220 9% 56%)',
		],
		[
			'id' => 17,
			'slug' => 'zinc',
			'name' => 'Zinc',
			'primary' => 'hsl(240 5% 34%)',
			'accent' => 'hsl(240 5% 44%)',
		],
		[
			'id' => 18,
			'slug' => 'stone',
			'name' => 'Stone',
			'primary' => 'hsl(25 6% 45%)',
			'accent' => 'hsl(25 6% 55%)',
		],
	];


    /**
     * Run the database seeds.
     */
    public function run(): void
    {
		foreach ($this->colorSchemes as $colorScheme) {
			ColorScheme::updateOrCreate($colorScheme);
		}
    }

    /**
     * Revert the database seeds.
     */
    public function revert(): void
    {
        foreach ($this->colorSchemes as $colorScheme) {
			ColorScheme::where('id', $colorScheme['id'])->delete();
		}
    }
}
