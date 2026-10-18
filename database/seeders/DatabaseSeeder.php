<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Grado;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
         User::updateOrCreate(
            ['email' => 'admin@edukia.test'],
            [
                'name'              => 'Administrador',
                'password'          => Hash::make('cambiar123'),
                'rol'               => 'admin',
                'activo'            => true,
                'email_verified_at' => now(),
            ]
        );
       
        
        Grado::firstOrCreate(['nombre' => '1°'], ['orden' => 1]);
        Grado::firstOrCreate(['nombre' => '2°'], ['orden' => 2]);
        Grado::firstOrCreate(['nombre' => '3°'], ['orden' => 3]);
        Grado::firstOrCreate(['nombre' => '4°'], ['orden' => 4]);
        Grado::firstOrCreate(['nombre' => '5°'], ['orden' => 5]);
        Grado::firstOrCreate(['nombre' => '6°'], ['orden' => 6]);
        Grado::firstOrCreate(['nombre' => '7°'], ['orden' => 7]);
        Grado::firstOrCreate(['nombre' => '8°'], ['orden' => 8]);
        Grado::firstOrCreate(['nombre' => '9°'], ['orden' => 9]);
        Grado::firstOrCreate(['nombre' => '10°'], ['orden' => 10]);
        Grado::firstOrCreate(['nombre' => '11°'], ['orden' => 11]);
    }
}
