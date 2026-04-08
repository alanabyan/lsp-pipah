<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Akun Admin
        User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@mail.com',
            'password' => Hash::make('password123'),
            'jabatan'  => 'admin',
        ]);

        // 2. Akun Apoteker
        User::create([
            'name'     => 'Ahmad Apoteker',
            'email'    => 'apoteker@mail.com',
            'password' => Hash::make('password123'),
            'jabatan'  => 'apoteker',
        ]);

        // 3. Akun Karyawan
        User::create([
            'name'     => 'Siti Karyawan',
            'email'    => 'karyawan@mail.com',
            'password' => Hash::make('password123'),
            'jabatan'  => 'karyawan',
        ]);

        // 4. Akun Kasir
        User::create([
            'name'     => 'Budi Kasir',
            'email'    => 'kasir@mail.com',
            'password' => Hash::make('password123'),
            'jabatan'  => 'kasir',
        ]);

        // 5. Akun Pemilik
        User::create([
            'name'     => 'Pak Haji Pemilik',
            'email'    => 'pemilik@mail.com',
            'password' => Hash::make('password123'),
            'jabatan'  => 'pemilik',
        ]);
    }
}