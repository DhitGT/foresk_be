<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class eskul_report_activity extends Model
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

        // static::creating(function ($model) {
        //     $model->id = (string) Str::uuid();
        // });
    }


    protected $fillable = [
        'id',
        'eskul_id',
        'picture',
        'description',
        'date_start',
        'date_end',
        'absent_code',
        'title',
        'instansi_id',
        'location',
    ];
    public function eskul()
    {
        return $this->hasOne(eskul::class, 'id', 'eskul_id');
    }

}
