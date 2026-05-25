<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FuskaCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'       => 'PET Şişe',
                'icon'       => 'water_drop',
                'color'      => '#00A6AB',
                'is_active'  => true,
                'sort_order' => 1,
            ],
            [
                'name'       => 'Premium',
                'icon'       => 'star',
                'color'      => '#13275A',
                'is_active'  => true,
                'sort_order' => 2,
            ],
            [
                'name'       => 'Prestige Cam',
                'icon'       => 'wine_bar',
                'color'      => '#E73A99',
                'is_active'  => true,
                'sort_order' => 3,
            ],
            [
                'name'       => 'Damacana',
                'icon'       => 'water',
                'color'      => '#00A6AB',
                'is_active'  => true,
                'sort_order' => 4,
            ],
            [
                'name'       => 'Bardak Su',
                'icon'       => 'local_cafe',
                'color'      => '#13275A',
                'is_active'  => true,
                'sort_order' => 5,
            ],
            [
                'name'       => 'Aromalı Su',
                'icon'       => 'spa',
                'color'      => '#E73A99',
                'is_active'  => true,
                'sort_order' => 6,
            ],
            [
                'name'       => 'Diğer',
                'icon'       => 'more_horiz',
                'color'      => '#888888',
                'is_active'  => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                array_merge($data, ['slug' => Str::slug($data['name'])]),
            );
        }

        $this->command->info('✓ ' . count($categories) . ' Fuska kategorisi eklendi.');
    }
}
