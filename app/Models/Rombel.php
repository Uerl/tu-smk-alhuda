<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rombel extends Model
{
    protected $table = 'rombel';

    protected $fillable = ['tahun_pelajaran_id', 'tingkat', 'jurusan', 'nama', 'walikelas_id', 'bk_id'];

    public function walikelas()
    {
        return $this->belongsTo(Pegawai::class, 'walikelas_id');
    }

    public function bk()
    {
        return $this->belongsTo(Pegawai::class, 'bk_id');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }
}
