<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['kategori_nama' => 'Terprogram'],
            ['kategori_nama' => 'Non-Program'],
            ['kategori_nama' => 'Non-JTI'],
        ];
        DB::table('m_kategori')->insert($data);
    }
}
