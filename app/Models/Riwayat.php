<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Riwayat extends Model
{
    protected $table = 'riwayat';

    public $timestamps = false;

    protected $fillable = ['user_id', 'aksi', 'subjek', 'subjek_id', 'keterangan', 'created_at'];

    public static function catat(string $aksi, string $subjek, ?int $subjekId, string $keterangan): void
    {
        static::query()->create([
            'user_id' => auth()->id(),
            'aksi' => $aksi,
            'subjek' => $subjek,
            'subjek_id' => $subjekId,
            'keterangan' => $keterangan,
            'created_at' => now(),
        ]);
    }
}
