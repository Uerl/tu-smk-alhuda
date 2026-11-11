<?php

use App\Tu\SumberPresensi;
use Illuminate\Support\Facades\Artisan;

Artisan::command('tu:kosongkan-akademik', function () {
    $hasil = SumberPresensi::kosongkanAkademik();
    $this->info('Dihapus: '.$hasil['murid'].' murid, '.$hasil['rombel'].' rombel, '.$hasil['guru'].' guru dan BK.');
})->purpose('Hapus data contoh angkatan, rombel, murid, walikelas, dan BK');

Artisan::command('tu:sinkron-presensi', function () {
    $this->info(SumberPresensi::sinkron());
})->purpose('Tarik rombel, murid, walikelas, dan BK dari aplikasi presensi');
