<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'Analista Contable', 'is_active' => true],
            ['name' => 'Analista de Compras e Inventarios', 'is_active' => true],
            ['name' => 'Analista de Selección', 'is_active' => true],
            ['name' => 'Aprendiz', 'is_active' => true],
            ['name' => 'Asistente de Operaciones', 'is_active' => true],
            ['name' => 'Auditor(a)', 'is_active' => true],
            ['name' => 'Auxiliar Administrativo', 'is_active' => true],
            ['name' => 'Auxiliar Contable', 'is_active' => true],
            ['name' => 'Auxiliar de Capacitaciones y Formación', 'is_active' => true],
            ['name' => 'Auxiliar de Gestión Humana', 'is_active' => true],
            ['name' => 'Auxiliar de Mantenimiento', 'is_active' => true],
            ['name' => 'Auxiliar de SST', 'is_active' => true],
            ['name' => 'Auxiliar de Tesorería', 'is_active' => true],
            ['name' => 'Auxiliar de TI', 'is_active' => true],
            ['name' => 'Bar', 'is_active' => true],
            ['name' => 'Cajero(a)', 'is_active' => true],
            ['name' => 'Cocina', 'is_active' => true],
            ['name' => 'Contador(a)', 'is_active' => true],
            ['name' => 'Coordinador(a) de Calidad y Mejoramiento', 'is_active' => true],
            ['name' => 'Coordinador(a) de Inventarios', 'is_active' => true],
            ['name' => 'Coordinador(a) de Mantenimiento', 'is_active' => true],
            ['name' => 'Coordinador(a) de Mercadeo', 'is_active' => true],
            ['name' => 'Coordinador(a) de Planta', 'is_active' => true],
            ['name' => 'Coordinador(a) de Procesos', 'is_active' => true],
            ['name' => 'Coordinador(a) de Sede', 'is_active' => true],
            ['name' => 'Coordinador(a) de SST', 'is_active' => true],
            ['name' => 'Coordinador(a) de TI', 'is_active' => true],
            ['name' => 'Director(a) Administrativo(a)', 'is_active' => true],
            ['name' => 'Director(a) de Franquicias', 'is_active' => true],
            ['name' => 'Diseñador(a) Gráfico(a)', 'is_active' => true],
            ['name' => 'Entrenador(a) de Bar y Cocina', 'is_active' => true],
            ['name' => 'Gerente de Mercadeo', 'is_active' => true],
            ['name' => 'Gestor(a) de Comunicaciones', 'is_active' => true],
            ['name' => 'Hostess', 'is_active' => true],
            ['name' => 'Inventarios', 'is_active' => true],
            ['name' => 'Jefe de Producción', 'is_active' => true],
            ['name' => 'Jefe de Gestión Humana', 'is_active' => true],
            ['name' => 'Mesero(a)', 'is_active' => true],
            ['name' => 'Operario(a) de Producción', 'is_active' => true],
            ['name' => 'Servicios Generales', 'is_active' => true],
            ['name' => 'Supervisor(a) de Calidad y Mejoramiento', 'is_active' => true],
            ['name' => 'Supervisor(a) de Mantenimiento', 'is_active' => true],
            ['name' => 'Supervisor(a) de Planta', 'is_active' => true],
            ['name' => 'Supervisor(a) de SST', 'is_active' => true],
            ['name' => 'Tesorero(a)', 'is_active' => true],
            ['name' => 'CAPACITADOR EXTERNO', 'is_active' => true],
            ['name' => 'ASESOR EXTERNO', 'is_active' => true],
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(
                ['name' => $position['name']],
                $position
            );
        }

        $this->command->info('Positions seeded successfully.');
    }
}
