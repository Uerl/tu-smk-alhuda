<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $fillable = ['judul', 'isi', 'tanggal', 'petugas_id', 'aktif'];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'aktif' => 'boolean'];
    }
}
