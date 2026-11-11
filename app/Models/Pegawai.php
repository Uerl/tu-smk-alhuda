<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    protected $fillable = ['user_id', 'nip', 'nama', 'bagian', 'jabatan_tu', 'urutan'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
