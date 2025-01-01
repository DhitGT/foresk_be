<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class eskul_web_page extends Model
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
        'instansi_id',
        'eskul_id',
    ];

    public function webPageGalery()
    {
        return $this->hasMany(eskul_web_page_galery::class, 'eskul_id', 'eskul_id');
    }
    public function webPageActivities()
    {
        return $this->hasMany(eskul_activities::class, 'eskul_id', 'eskul_id');
    }
}
