<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterHakAkses extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('master_hak_akses')->insert([
            [
                'id' => Str::uuid(), // Generate a UUID for the ID
                'Kode' => 'LEADER_HAK_AKSES',
                'Nama' => 'All Leader Access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(), // Generate a UUID for the ID
                'Kode' => 'USER_HAK_AKSES',
                'Nama' => 'All Member Eskul User Access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(), // Generate a UUID for the ID
                'Kode' => 'GUEST_HAK_AKSES',
                'Nama' => 'Guest',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
