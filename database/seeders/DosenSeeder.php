<?php

namespace Database\Seeders;

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
            ['dosen_id' => 4, 'user_id' => 4, 'nama' => 'Ahmadi Yuli Ananta ST', 'nip' => '198107052005011002', 'created_at' => now()],
            ['dosen_id' => 5, 'user_id' => 5, 'nama' => 'Ely Setyo Astuti, ST., MT', 'nip' => '197605152009122001', 'created_at' => now()],
            ['dosen_id' => 6, 'user_id' => 6, 'nama' => 'Erfan Rohadi, ST., M.Eng., Ph.D', 'nip' => '197201232008011006', 'created_at' => now()],
            ['dosen_id' => 7, 'user_id' => 7, 'nama' => 'Faisal Rahutomo ST., M.Kom., Dr.Eng', 'nip' => '197711162005011008', 'created_at' => now()],
            ['dosen_id' => 8, 'user_id' => 8, 'nama' => 'Gunawan Budi Prasetyo, ST., MMT', 'nip' => '197704242008121001', 'created_at' => now()],
            ['dosen_id' => 9, 'user_id' => 9, 'nama' => 'Hendra Pradibta, SE., M.Sc', 'nip' => '198305212006041003', 'created_at' => now()],
            ['dosen_id' => 10, 'user_id' => 10, 'nama' => 'Imam Fahrur Rozi, ST., MT', 'nip' => '198406102008121004', 'created_at' => now()],
        ];

        DB::table('m_dosen')->insert($data);
    }
}
