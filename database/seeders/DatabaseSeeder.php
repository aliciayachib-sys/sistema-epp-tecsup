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

// CAMBIO AQUÍ: Usamos updateOrCreate para que no falle si ya existe
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@tecsup.edu.pe'], // Si encuentra este email...
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'role' => 'Admin',
                // Agregamos esto para que NO te mande a la pantalla de error 404 (primer-ingreso)
                'created_at' => now()->subDays(1),
                'updated_at' => now(),
            ]
        );
    


    // 3. LLAMAR A LOS OTROS SEEDERS EN ORDEN
        $this->call([
            // EppSeeder::class, // SI TIENES UNO QUE LLENA CASCOS/GUANTES, PONLO AQUÍ
            MatrizDinamicaSeeder::class,
        ]);
}



}
