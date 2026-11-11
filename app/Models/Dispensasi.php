<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispensasi extends Model
{
    protected $table = 'dispensasi';

    protected $fillable = ['siswa_id', 'sampai', 'alasan', 'petugas_id', 'dicabut_pada'];

    protected function casts(): array
    {
        return ['sampai' => 'date', 'dicabut_pada' => 'datetime'];
    }
}
