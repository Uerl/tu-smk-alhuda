<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tagihan';

    protected $fillable = ['tahun_pelajaran_id', 'nama', 'nominal', 'tingkat', 'sasaran', 'rombel_id', 'siswa_id', 'tempo'];

    protected function casts(): array
    {
        return ['tempo' => 'date', 'nominal' => 'integer'];
    }

    public function rombel()
    {
        return $this->belongsTo(Rombel::class);
    }
}
