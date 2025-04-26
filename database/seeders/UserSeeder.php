<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Saka',
            'email' => 'saka@gmail.com',
            'password' => Hash::make('123123'),
        ]);

        User::create([
            'name' => 'Wijaya',
            'email' => 'wijaya@gmail.com',
            'password' => Hash::make('123123'),
        ]);
    }
}
