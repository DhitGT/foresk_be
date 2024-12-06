<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MasterHakAkses extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        DB::table('master_hak_akses')->insert([
            [
                'Kode' => 'ADM',
                'Nama' => 'Administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Kode' => 'USR',
                'Nama' => 'User',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Kode' => 'GUEST',
                'Nama' => 'Guest',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
