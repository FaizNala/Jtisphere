<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Tambah Periode
        $periods = [
            ['periode' => 2024, 'tanggal_mulai' => '2024-01-01', 'tanggal_selesai' => '2024-12-31', 'status' => 'Tidak Aktif', 'created_at' => $now, 'updated_at' => $now],
            ['periode' => 2025, 'tanggal_mulai' => '2025-01-01', 'tanggal_selesai' => '2025-12-31', 'status' => 'Aktif', 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($periods as $period) {
            DB::table('m_periode')->updateOrInsert(
                ['periode' => $period['periode']],
                $period
            );
        }

        // Ambil periode aktif
        $periode = DB::table('m_periode')->where('status', 'Aktif')->first();

        // Periksa jika tidak ada periode aktif
        if (!$periode) {
            $this->command->error('Tidak ada periode yang aktif. Pastikan tabel m_periode memiliki data.');
            return;
        }

        // Data dosen dari DosenSeeder
        $dosen = DB::table('m_dosen')->get();

        // Data kategori kegiatan
        $kategori = DB::table('m_kategori')->pluck('kategori_id')->toArray();

        // Seed kegiatan
        $startDate = Carbon::create(2024, 12, 5);
        for ($i = 1; $i <= 20; $i++) {
            $kegiatanId = DB::table('t_kegiatan')->insertGetId([
                'kategori_id' => $kategori[array_rand($kategori)],
                'periode_id' => $periode->periode_id,
                'kegiatan_nama' => 'Kegiatan Dummy ' . $i,
                'deskripsi' => 'Deskripsi kegiatan dummy ' . $i,
                'skala' => ['Internal', 'Nasional', 'Internasional', 'Lain-Lain'][array_rand(['Internal', 'Nasional', 'Internasional', 'Lain-Lain'])],
                'anggaran' => rand(1000000, 50000000),
                'status' => ['Belum', 'Berjalan', 'Selesai'][array_rand(['Belum', 'Berjalan', 'Selesai'])],
                'tanggal_mulai' => $startDate->copy()->addDays(rand(0, 30))->format('Y-m-d'),
                'tanggal_selesai' => $startDate->copy()->addDays(rand(31, 60))->format('Y-m-d'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Seed agenda untuk kegiatan
            $assignedDosen = []; // Array untuk menyimpan dosen yang ter-assign di agenda
            for ($j = 1; $j <= 2; $j++) {
                $agendaId = DB::table('t_agenda')->insertGetId([
                    'nama' => 'Agenda Kegiatan Dummy ' . $i . '-' . $j,
                    'tanggal_mulai' => $startDate->copy()->addDays(rand(0, 30))->format('Y-m-d'),
                    'tanggal_selesai' => $startDate->copy()->addDays(rand(31, 60))->format('Y-m-d'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Insert ke t_kegiatan_agenda
                DB::table('t_kegiatan_agenda')->insert([
                    'kegiatan_id' => $kegiatanId,
                    'agenda_id' => $agendaId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Seed dosen di agenda
                $randomDosen = $dosen->random();
                DB::table('t_agenda_dosen')->insert([
                    'agenda_id' => $agendaId,
                    'dosen_id' => $randomDosen->dosen_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Tambahkan dosen ke array assignedDosen
                $assignedDosen[] = $randomDosen->dosen_id;
            }

            // Assign dosen dari agenda ke t_dosen_kegiatan
            foreach (array_unique($assignedDosen) as $dosenId) {
                DB::table('t_dosen_kegiatan')->insert([
                    'kegiatan_id' => $kegiatanId,
                    'dosen_id' => $dosenId,
                    'peran_id' => rand(1, 4), // Asumsi ada 4 peran di tabel m_peran
                    'bobot' => rand(1, 5),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
