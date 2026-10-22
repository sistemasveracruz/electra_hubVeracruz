<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('Seeders de desenvolvimento não são executados em produção.');

            return;
        }

        // Torna os dados gerados pelo Faker reproduzíveis em um banco recém-migrado.
        fake('pt_BR')->seed(20261021);

        // Ordem de dependência entre as tabelas.
        $this->call([
            ReferenciaSeeder::class,
            InstitucionalSeeder::class,
            EstruturaAcademicaSeeder::class,
            GradeCurricularSeeder::class,
            FuncionarioSeeder::class,
            FamiliaAlunoSeeder::class,
        ]);
    }
}
