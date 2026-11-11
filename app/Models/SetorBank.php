<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetorBank extends Model
{
    protected $table = 'setor_bank';

    protected $fillable = ['tanggal', 'jumlah', 'berita', 'petugas_id'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }
}
