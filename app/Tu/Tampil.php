<?php

namespace App\Tu;

use Carbon\Carbon;

class Tampil
{
    public static function rupiah(int $nilai): string
    {
        return 'Rp '.number_format($nilai, 0, ',', '.');
    }

    public static function tanggal(?string $nilai): string
    {
        if (! $nilai) {
            return '-';
        }
        $nama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $waktu = Carbon::parse($nilai);

        return $waktu->day.' '.$nama[$waktu->month - 1].' '.$waktu->year;
    }
}
