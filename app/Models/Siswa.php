<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = ['user_id', 'nis', 'nama', 'rombel_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rombel()
    {
        return $this->belongsTo(Rombel::class);
    }

    public function keringanan()
    {
        return $this->hasMany(Keringanan::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class);
    }

    public function dispensasi()
    {
        return $this->hasMany(Dispensasi::class);
    }

    public function janji()
    {
        return $this->hasOne(JanjiBayar::class);
    }
}
