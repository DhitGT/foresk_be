<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterEskulAbsensi extends Model
{
    use HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();

        });
    }

    protected $fillable = [
        'id',
        'eskul_report_activity_id',
        'eskul_id',
        'absent_code',
    ];

    public function eskulReportActivity()
    {
        return $this->hasOne(eskul_report_activity::class, 'id', 'eskul_report_activity_id');
    }

    public function eskul()
    {
        return $this->hasOne(eskul::class, 'id', 'eskul_id');
    }

    public function eskulAbsen()
    {
        return $this->hasMany(EskulAbsensi::class, 'absent_code', 'absent_code');
    }

}
