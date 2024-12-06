<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterHakAkses extends Model
{
    use HasFactory;

    protected $table = "master_hak_akses";

    protected $fillable = [
        'name',
        'role',
        'email',
        'password',
    ];
}
