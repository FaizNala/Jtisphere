<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DosenLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['dosen_id' => 1, 'level_id' => 1, 'created_at' => now()],
            ['dosen_id' => 1, 'level_id' => 3, 'created_at' => now()],
            ['dosen_id' => 2, 'level_id' => 2, 'created_at' => now()],
            ['dosen_id' => 2, 'level_id' => 3, 'created_at' => now()],
            ['dosen_id' => 3, 'level_id' => 3, 'created_at' => now()],
            ['dosen_id' => 4, 'level_id' => 2, 'created_at' => now()],
            ['dosen_id' => 4, 'level_id' => 3, 'created_at' => now()],
            ['dosen_id' => 5, 'level_id' => 1, 'created_at' => now()],
            ['dosen_id' => 5, 'level_id' => 3, 'created_at' => now()],
            ['dosen_id' => 6, 'level_id' => 3, 'created_at' => now()],
            ['dosen_id' => 7, 'level_id' => 3, 'created_at' => now()],
            ['dosen_id' => 8, 'level_id' => 3, 'created_at' => now()],
            ['dosen_id' => 9, 'level_id' => 3, 'created_at' => now()],
            ['dosen_id' => 10, 'level_id' => 3, 'created_at' => now()],
        ];

        DB::table('t_dosen_level')->insert($data);
    }
}
