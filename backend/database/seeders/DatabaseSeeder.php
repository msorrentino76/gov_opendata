<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::create([
            'cf'        => 'SRRMRC76T06G273G',
            'email'     => 'msorrentino.cinf@gmail.com',
            'password'  => bcrypt('password'),
            'abilities' => json_encode(["system:admin"]),
        ]);
        
    }
}
