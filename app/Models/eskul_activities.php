<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class eskul_activities extends Model
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
        'cover_image',
        'gen',
        'date',
        'location',
        'title',
        'description',
    ];
    public function webPageActivitiesGalery()
    {
        return $this->hasMany(eskul_activities_galery::class, 'eskul_activities_id', 'id');
    }
}
