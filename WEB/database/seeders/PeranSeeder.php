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
            ['peran_kode' => 'PIC'  , 'peran_nama' => 'Penanggung Jawab', 'is_pic' => true],
            ['peran_kode' => 'AGT'  , 'peran_nama' => 'Anggota', 'is_pic' => false],
            ['peran_kode' => 'SKR'  , 'peran_nama' => 'Sekretaris', 'is_pic' => true],
            ['peran_kode' => 'BEN'  , 'peran_nama' => 'Bendahara', 'is_pic' => true]
        ];
        DB::table('m_peran')->insert($data);
    }
}
