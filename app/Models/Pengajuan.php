<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $table = 'pengajuan';

    protected $fillable = ['siswa_id', 'tagihan_id', 'jumlah', 'tanggal', 'metode', 'bukti_path', 'status', 'alasan', 'petugas_id', 'diverifikasi_pada'];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'diverifikasi_pada' => 'datetime', 'jumlah' => 'integer'];
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }
}
