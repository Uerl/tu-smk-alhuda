<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = ['siswa_id', 'bulan', 'hadir', 'sakit', 'izin', 'alpa'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
