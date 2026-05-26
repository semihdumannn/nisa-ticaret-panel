<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VanguardBrandSeeder extends Seeder
{
    private array $brands = [
        ['name' => 'Havran Çiftlik', 'sort_order' => 1],
        ['name' => 'Arslan',         'sort_order' => 2],
        ['name' => 'İndomie',        'sort_order' => 3],
        ['name' => 'Sarıyer',        'sort_order' => 4],
        ['name' => 'Black Bruin',    'sort_order' => 5],
        ['name' => 'Beypazarı',      'sort_order' => 6],
        ['name' => 'Kınık',          'sort_order' => 7],
        ['name' => 'Sultan',         'sort_order' => 8],
        ['name' => 'Juss',           'sort_order' => 9],
        ['name' => 'Meysu',          'sort_order' => 10],
        ['name' => 'Kardan',         'sort_order' => 11],
        ['name' => 'Tarihi Odunpazarı', 'sort_order' => 12],
        ['name' => 'Kardelen',       'sort_order' => 13],
        ['name' => 'Max Fly',        'sort_order' => 14],
        ['name' => 'Max Brew',       'sort_order' => 15],
        ['name' => 'Doğanay',        'sort_order' => 16],
        ['name' => 'As01',           'sort_order' => 17],
        ['name' => 'Nescafe',        'sort_order' => 18],
        ['name' => 'My Coffee',      'sort_order' => 19],
        ['name' => 'Kahve Dünyası',  'sort_order' => 20],
        ['name' => 'Pınar',          'sort_order' => 21],
        ['name' => 'Didi',           'sort_order' => 22],
        ['name' => 'Poli',           'sort_order' => 23],
        ['name' => 'Coca-Cola',      'sort_order' => 24],
        ['name' => 'Fanta',          'sort_order' => 25],
        ['name' => 'Pepsi',          'sort_order' => 26],
        ['name' => 'Cola Turka',     'sort_order' => 27],
        ['name' => 'Niğde Gazozu',   'sort_order' => 28],
        ['name' => 'Çamlıca',        'sort_order' => 29],
        ['name' => 'Gorilla',        'sort_order' => 30],
        ['name' => 'Red Bull',       'sort_order' => 31],
        ['name' => 'Hot Line',       'sort_order' => 32],
    ];

    public function run(): void
    {
        foreach ($this->brands as $data) {
            Brand::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name'       => $data['name'],
                    'slug'       => Str::slug($data['name']),
                    'is_active'  => true,
                    'sort_order' => $data['sort_order'],
                ],
            );
        }

        $this->command->info('✓ ' . count($this->brands) . ' Vanguard markası eklendi.');
    }
}
