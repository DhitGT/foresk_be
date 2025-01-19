<?php

namespace App\Models;

use App\Models\instansi;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class eskul extends Model
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'logo',
        'badge',
        'gen',
        'alumni',
        'instagram_url',
        'whatsapp_number',
        'instansi_id',
        'leader_id',
    ];

    public function achievements()
    {
        return $this->hasMany(EskulAchivement::class, 'eskul_id');
    }

    public function webPages()
    {
        return $this->hasOne(eskul_web_page::class, 'eskul_id', 'id');
    }
    public function instansi()
    {
        return $this->hasOne(instansi::class, 'id', 'instansi_id');
    }

    // Define the relationship for kas
    public function kas()
    {
        return $this->hasOne(EskulKas::class, 'eskul_id', 'id');
    }

    public function eskulMembers()
    {
        return $this->hasMany(EskulMember::class, 'eskul_id');
    }

    public function kasLogs()
    {
        return $this->hasMany(EskulKasLog::class, 'eskul_id');
    }


}
