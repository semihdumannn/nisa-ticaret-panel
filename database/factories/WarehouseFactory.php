<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        $cities = ['Istanbul', 'Ankara', 'Izmir', 'Bursa', 'Antalya', 'Kocaeli', 'Adana'];
        $city   = $this->faker->randomElement($cities);

        return [
            'name'      => $city . ' Warehouse ' . strtoupper($this->faker->lexify('??')),
            'code'      => 'WH-' . strtoupper($this->faker->lexify('???')) . '-' . $this->faker->numerify('##'),
            'city'      => $city,
            'address'   => $this->faker->address(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
