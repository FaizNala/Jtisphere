<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['dosen_id' => 1, 'user_id' => 1, 'nama' => 'Dika Rizky Yunianto S.Kom., M.Kom.', 'nip' => '199206062019031017', 'created_at' => now()],
            ['dosen_id' => 2, 'user_id' => 2, 'nama' => 'Dr. Eng. Rosa Andrie Asmara, S.T., M.T.', 'nip' => '198010102005011001', 'created_at' => now()],
            ['dosen_id' => 3, 'user_id' => 3, 'nama' => 'Banni Satria Andoko, S.Kom., M.Si', 'nip' => '198108092010121002', 'created_at' => now()],
        ];

        DB::table('m_dosen')->insert($data);
    }
}
