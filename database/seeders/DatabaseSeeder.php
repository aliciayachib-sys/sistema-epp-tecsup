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

// Crear el usuario administrador
    \App\Models\User::create([
        'name' => 'Administrador',
        'email' => 'admin@tecsup.edu.pe',
        'password' => Hash::make('admin123'), // La contraseña será admin123
        'role' => 'Admin',
    ]);
    


    // 3. LLAMAR A LOS OTROS SEEDERS EN ORDEN
        $this->call([
            // EppSeeder::class, // SI TIENES UNO QUE LLENA CASCOS/GUANTES, PONLO AQUÍ
            MatrizDinamicaSeeder::class,
        ]);
}



}
