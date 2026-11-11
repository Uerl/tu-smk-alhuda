<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keringanan extends Model
{
    protected $table = 'keringanan';

    protected $fillable = ['siswa_id', 'tagihan_id', 'jumlah', 'alasan', 'petugas_id'];
}
