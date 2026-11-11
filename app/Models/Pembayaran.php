<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = ['siswa_id', 'tagihan_id', 'jumlah', 'tanggal', 'nomor', 'metode', 'asal', 'pengajuan_id', 'petugas_id'];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'jumlah' => 'integer'];
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
