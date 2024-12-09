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
        // Periode 2018-2025
        for ($year = 2018; $year <= 2025; $year++) {
            DB::table('m_periode')->updateOrInsert(
                ['periode' => $year],
                [
                    'tanggal_mulai' => "$year-01-01",
                    'tanggal_selesai' => "$year-12-31",
                    'status' => $year == 2025 ? 'Aktif' : 'Tidak Aktif',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // Ambil semua periode
        $periodeList = DB::table('m_periode')->get();

        // Data kategori kegiatan
        $kategori = DB::table('m_kategori')->pluck('kategori_id')->toArray();

        // Data dosen
        $dosen = DB::table('m_dosen')->get();

        // Nama kegiatan
        $kegiatanNames = [
            'Kegiatan Pelatihan',
            'Panitia Skripsi',
            'Koordinator Magang',
            'Panitia Akreditasi',
            'Koordinator Prakerin',
            'Koordinator Puskom',
            'Koordinator JPC',
            'Koordinator Lab',
        ];
        $startDate = Carbon::create(2024, 12, 5);

        // Seed 50 kegiatan
        for ($i = 1; $i <= 50; $i++) {
            $periode = $periodeList->random(); // Pilih periode secara acak
            $kegiatanSkala = ['Internal', 'Nasional', 'Internasional', 'Lain-Lain'][array_rand(['Internal', 'Nasional', 'Internasional', 'Lain-Lain'])];
            $kegiatanAnggaran = rand(1000000, 50000000);

            $tanggalMulai = $startDate->copy()->addMonths($i % 12);
            $tanggalSelesai = $tanggalMulai->copy()->addDays(rand(15, 30));
            $selisihHari = $tanggalMulai->diffInDays($tanggalSelesai);
            // Status kegiatan berdasarkan tahun periode
            $statusKegiatan = match ((int) $periode->periode) {
                2024 => 'Berjalan',
                2025 => 'Berjalan',
                default => 'Selesai',
            };
            $kegiatanId = DB::table('t_kegiatan')->insertGetId([
                'kategori_id' => $kategori[array_rand($kategori)],
                'periode_id' => $periode->periode_id,
                'kegiatan_nama' => $kegiatanNames[array_rand($kegiatanNames)],
                'deskripsi' => 'Deskripsi kegiatan periode ' . $periode->periode,
                'skala' => $kegiatanSkala,
                'anggaran' => $kegiatanAnggaran,
                'status' => $statusKegiatan,
                'tanggal_mulai' => $tanggalMulai->format('Y-m-d'),
                'tanggal_selesai' => $tanggalSelesai->format('Y-m-d'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Pilih 4-5 dosen secara acak
            $selectedDosen = $dosen->random(rand(4, 5))->values();
            // Tentukan peran khusus
            $peran = [
                ['dosen_id' => $selectedDosen[0]->dosen_id, 'peran_id' => 1],
                ['dosen_id' => $selectedDosen[1]->dosen_id, 'peran_id' => 3],
                ['dosen_id' => $selectedDosen[2]->dosen_id, 'peran_id' => 4],
            ];
            // Peran ID 2(anggota)
            for ($j = 3; $j < $selectedDosen->count(); $j++) {
                $peran[] = ['dosen_id' => $selectedDosen[$j]->dosen_id, 'peran_id' => 2];
            }

            // Seed agenda untuk kegiatan
            for ($j = 1; $j <= 2; $j++) {
                $agendaId = DB::table('t_agenda')->insertGetId([
                    'nama' => 'Agenda ' . $j . ' untuk ' . $kegiatanNames[array_rand($kegiatanNames)],
                    'tanggal_mulai' => $tanggalMulai->addDays(rand(0, 5))->format('Y-m-d'),
                    'tanggal_selesai' => $tanggalSelesai->addDays(rand(15, 30))->format('Y-m-d'),
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
                foreach ($peran as $singlePeran) {
                    DB::table('t_agenda_dosen')->insert([
                        'agenda_id' => $agendaId,
                        'dosen_id' => $singlePeran['dosen_id'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // Assign dosen ke t_dosen_kegiatan dengan bobot
            foreach ($peran as $singlePeran) {
                $bobot = $this->hitungBobot($kegiatanSkala, $kegiatanAnggaran, $selisihHari, $singlePeran['peran_id']);
                DB::table('t_dosen_kegiatan')->insert([
                    'kegiatan_id' => $kegiatanId,
                    'dosen_id' => $singlePeran['dosen_id'],
                    'peran_id' => $singlePeran['peran_id'],
                    'bobot' => $bobot,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function hitungBobot($skala, $anggaran, $selisihHari, $peranId)
    {
        // Bobot Skala
        $b1 = match ($skala) {
            'Internal' => 2,
            'Nasional' => 3,
            'Internasional' => 4,
            default => 1
        };

        // Bobot Anggaran
        $b2 = match (true) {
            $anggaran >= 1000000000 => 4,
            $anggaran >= 100000000 => 3,
            $anggaran >= 10000000 => 2,
            default => 1
        };

        // Bobot Waktu
        $b3 = match (true) {
            $selisihHari > 365 => 5,
            $selisihHari >= 274 => 4,
            $selisihHari >= 183 => 3,
            $selisihHari >= 91 => 2,
            default => 1
        };

        // Bobot Peran
        $b4 = match ($peranId) {
            1 => 5,
            3, 4 => 3,
            default => 1
        };

        // Hitung rata-rata
        return round(($b1 + $b2 + $b3 + $b4) / 4);
    }
}
