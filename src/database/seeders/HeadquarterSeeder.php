<?php

namespace Database\Seeders;

use App\Models\Headquarter;
use Illuminate\Database\Seeder;

class HeadquarterSeeder extends Seeder
{
    public function run(): void
    {
        $headquarters = [
            ['name' => 'Bochalema', 'is_active' => true],
            ['name' => 'Chipichape', 'is_active' => true],
            ['name' => 'Ciudad Jardín', 'is_active' => true],
            ['name' => 'Despensa', 'is_active' => true],
            ['name' => 'Flora', 'is_active' => true],
            ['name' => 'Granada', 'is_active' => true],
            ['name' => 'Jardín Plaza', 'is_active' => true],
            ['name' => 'Limonar', 'is_active' => true],
            ['name' => 'Llanogrande', 'is_active' => true],
            ['name' => 'Oficina', 'is_active' => true],
            ['name' => 'Pance', 'is_active' => true],
            ['name' => 'San Fernando', 'is_active' => true],
            ['name' => 'Unicentro', 'is_active' => true],
            ['name' => 'ENTIDAD EXTERNA', 'is_active' => true],
        ];

        foreach ($headquarters as $headquarter) {
            Headquarter::firstOrCreate(
                ['name' => $headquarter['name']],
                $headquarter
            );
        }

        $this->command->info('Headquarters seeded successfully.');
    }
}
