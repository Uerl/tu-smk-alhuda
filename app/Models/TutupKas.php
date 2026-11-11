<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutupKas extends Model
{
    protected $table = 'tutup_kas';

    protected $fillable = ['tanggal', 'hitung', 'tunai_tercatat', 'selisih', 'catatan', 'petugas_id'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }
}
