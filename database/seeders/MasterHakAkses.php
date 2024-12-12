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
                'Kode' => 'LEADER_HAK_AKSES',
                'Nama' => 'All Leader Acces',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Kode' => 'USER_HAK_AKSES',
                'Nama' => 'All Member Eskul User Acces',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Kode' => 'GUEST_HAK_AKSES',
                'Nama' => 'Guest',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
