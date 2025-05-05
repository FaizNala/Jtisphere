<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['user_id' => 1, 'username' => 'Administrator', 'password' => Hash::make('12345'), 'created_at' => now()],
            ['user_id' => 2, 'username' => 'Pimpinan', 'password' => Hash::make('12345'), 'created_at' => now()],
            ['user_id' => 3, 'username' => 'Dosen', 'password' => Hash::make('12345'), 'created_at' => now()],
            ['user_id' => 4, 'username' => 'Ahmadi', 'password' => Hash::make('12345'), 'created_at' => now()],
            ['user_id' => 5, 'username' => 'Ely', 'password' => Hash::make('12345'), 'created_at' => now()],
            ['user_id' => 6, 'username' => 'Erfan', 'password' => Hash::make('12345'), 'created_at' => now()],
            ['user_id' => 7, 'username' => 'Faisal', 'password' => Hash::make('12345'), 'created_at' => now()],
            ['user_id' => 8, 'username' => 'Gunawan', 'password' => Hash::make('12345'), 'created_at' => now()],
            ['user_id' => 9, 'username' => 'Hendra', 'password' => Hash::make('12345'), 'created_at' => now()],
            ['user_id' => 10, 'username' => 'Imam', 'password' => Hash::make('12345'), 'created_at' => now()],
        ];

        DB::table('m_user')->insert($data);
    }
}
