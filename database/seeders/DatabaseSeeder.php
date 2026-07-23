<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Esto creará tu usuario administrador si no existe todavía
        User::firstOrCreate(
            ['email' => 'eder@platform.com'], // Busca si ya existe este correo
            [
                'name' => 'Eder Álvarez',
                'password' => Hash::make('admin12345') // Tu contraseña encriptada y segura
            ]
        );
    }
}
