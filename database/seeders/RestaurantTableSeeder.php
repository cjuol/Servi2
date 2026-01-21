<?php

namespace Database\Seeders;

use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class RestaurantTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tables = [
            // Mesas interiores
            ['name' => 'Mesa 1', 'capacity' => 4, 'is_available' => true],
            ['name' => 'Mesa 2', 'capacity' => 4, 'is_available' => true],
            ['name' => 'Mesa 3', 'capacity' => 2, 'is_available' => true],
            ['name' => 'Mesa 4', 'capacity' => 6, 'is_available' => true],
            ['name' => 'Mesa 5', 'capacity' => 4, 'is_available' => true],
            
            // Mesas de terraza
            ['name' => 'Terraza 1', 'capacity' => 4, 'is_available' => true],
            ['name' => 'Terraza 2', 'capacity' => 4, 'is_available' => true],
            ['name' => 'Terraza 3', 'capacity' => 2, 'is_available' => true],
            ['name' => 'Terraza 4', 'capacity' => 6, 'is_available' => true],
            ['name' => 'Terraza 5', 'capacity' => 4, 'is_available' => true],
        ];

        foreach ($tables as $table) {
            RestaurantTable::create($table);
        }
    }
}
