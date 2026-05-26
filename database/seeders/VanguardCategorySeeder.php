<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VanguardCategorySeeder extends Seeder
{
    private array $categories = [
        ['name' => 'Ayran',              'icon' => 'local_drink',    'color' => '#4CAF50', 'sort_order' => 1],
        ['name' => 'Noodle',             'icon' => 'ramen_dining',   'color' => '#FF9800', 'sort_order' => 2],
        ['name' => 'Kola & Gazlı İçecek','icon' => 'sports_bar',    'color' => '#F44336', 'sort_order' => 3],
        ['name' => 'Soda',               'icon' => 'water_full',     'color' => '#2196F3', 'sort_order' => 4],
        ['name' => 'Meyve Suyu',         'icon' => 'emoji_food_beverage', 'color' => '#FF5722', 'sort_order' => 5],
        ['name' => 'Su',                 'icon' => 'water_drop',     'color' => '#00BCD4', 'sort_order' => 6],
        ['name' => 'Enerji İçeceği',     'icon' => 'bolt',           'color' => '#FFC107', 'sort_order' => 7],
        ['name' => 'Şalgam',             'icon' => 'local_bar',      'color' => '#9C27B0', 'sort_order' => 8],
        ['name' => 'Kahve',              'icon' => 'coffee',         'color' => '#795548', 'sort_order' => 9],
        ['name' => 'Ketçap & Mayonez',   'icon' => 'restaurant',     'color' => '#E91E63', 'sort_order' => 10],
        ['name' => 'Soğuk Çay',          'icon' => 'local_cafe',     'color' => '#8BC34A', 'sort_order' => 11],
        ['name' => 'Toz İçecek',         'icon' => 'blender',        'color' => '#FF9800', 'sort_order' => 12],
    ];

    public function run(): void
    {
        foreach ($this->categories as $data) {
            Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name'       => $data['name'],
                    'slug'       => Str::slug($data['name']),
                    'icon'       => $data['icon'],
                    'color'      => $data['color'],
                    'is_active'  => true,
                    'sort_order' => $data['sort_order'],
                ],
            );
        }

        $this->command->info('✓ ' . count($this->categories) . ' Vanguard kategorisi eklendi.');
    }
}
