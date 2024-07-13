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
            'username'  => 'msorrentino',
            'email'     => 'msorrentino.cinf@gmail.com',
            'name'      => 'Marco',
            'surname'   => 'Sorrentino',
            'password'  => bcrypt('password'),
            'abilities' => json_encode(["system:admin"]),
        ]);
        
    }
}
