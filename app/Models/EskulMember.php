<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class EskulMember extends Model
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

    protected $table = "eskul_members";

    protected $fillable = [
        'name',
        'id',
        'gen',
        'eskul_id',
        'instansi_id',
    ];

    public function eskul()
    {
        return $this->hasOne(eskul::class, 'id', 'eskul_id');
    }
}
