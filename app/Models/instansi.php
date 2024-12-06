<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class instansi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'owner_id',
        'total_organization',

    ];
}
