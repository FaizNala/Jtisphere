<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['peran_kode' => 'PIC'  , 'peran_nama' => 'Penanggung Jawab'],
            ['peran_kode' => 'AGT', 'peran_nama' => 'Anggota']
        ];
        DB::table('m_peran')->insert($data);
    }
}
