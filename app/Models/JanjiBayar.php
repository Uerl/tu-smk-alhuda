<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JanjiBayar extends Model
{
    protected $table = 'janji_bayar';

    protected $fillable = ['siswa_id', 'tanggal', 'catatan', 'petugas_id'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }
}
