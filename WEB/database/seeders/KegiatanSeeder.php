<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['kategori_id' => 1, 'kegiatan_nama' => 'Panitia Skripsi', 'status' => 'Belum', 'deskripsi' => 'Panitia Skripsi Mahasiswa Tahun 2025'],
            ['kategori_id' => 2, 'kegiatan_nama' => 'Panitia Akreditasi', 'status' => 'Belum', 'deskripsi' => 'Panitia Akreditasi Prodi PPLS 2025'],
            ['kategori_id' => 3, 'kegiatan_nama' => 'Panitia Audit Internal Polinema', 'status' => 'Berjalan', 'deskripsi' => 'Pengembangan Sistem Informasi Mahasiswa'],
        ];
    }
}
