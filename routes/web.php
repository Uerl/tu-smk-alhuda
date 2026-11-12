<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\MuridController;
use App\Http\Controllers\StafController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('masuk'));
Route::get('/masuk', [AuthController::class, 'form'])->name('masuk');
Route::post('/masuk', [AuthController::class, 'masuk'])->name('masuk.kirim');

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'keluar'])->name('keluar');
    Route::get('/bukti/{pengajuan}', [MuridController::class, 'bukti'])->name('bukti');
    Route::get('/cetak/kwitansi/{pembayaran}', [CetakController::class, 'kwitansi'])->name('cetak.kwitansi');
    Route::get('/cetak/gabungan/{siswa}', [CetakController::class, 'gabungan'])->name('cetak.gabungan');
    Route::get('/cetak/kartu/{siswa}', [CetakController::class, 'kartu'])->name('cetak.kartu');
    Route::get('/cetak/surat/{siswa}', [CetakController::class, 'surat'])->name('cetak.surat');
});

Route::middleware(['auth', 'peran:admin,staf'])->prefix('tu')->group(function () {
    Route::get('/', [StafController::class, 'dasbor'])->name('tu.dasbor');
    Route::post('/pengumuman', [StafController::class, 'pengumuman'])->name('tu.pengumuman');
    Route::post('/petugas', [StafController::class, 'petugas'])->name('tu.petugas');
    Route::get('/tunggakan', [StafController::class, 'tunggakan'])->name('tu.tunggakan');
    Route::post('/janji', [StafController::class, 'janji'])->name('tu.janji');
    Route::delete('/janji/{siswa}', [StafController::class, 'hapusJanji'])->name('tu.janji.hapus');
    Route::get('/kas', [StafController::class, 'kas'])->name('tu.kas');
    Route::post('/kas/tutup', [StafController::class, 'tutupKas'])->name('tu.kas.tutup');
    Route::post('/kas/setor', [StafController::class, 'setor'])->name('tu.kas.setor');
    Route::get('/kas.csv', [StafController::class, 'unduhKas'])->name('tu.kas.csv');
    Route::get('/cocok', [StafController::class, 'cocok'])->name('tu.cocok');
    Route::post('/cocok', [StafController::class, 'simpanCocok'])->name('tu.cocok.simpan');
    Route::get('/laporan', [StafController::class, 'laporan'])->name('tu.laporan');
    Route::get('/tunggakan.csv', [StafController::class, 'unduhTunggakan'])->name('tu.tunggakan.csv');
    Route::get('/absensi', [StafController::class, 'absensi'])->name('tu.absensi');
    Route::get('/tagihan', [StafController::class, 'tagihan'])->name('tu.tagihan');
    Route::post('/tagihan', [StafController::class, 'simpanTagihan'])->name('tu.tagihan.simpan');
    Route::post('/tagihan/{tagihan}/bulan', [StafController::class, 'bulanDepan'])->name('tu.tagihan.bulan');
    Route::get('/bayar', [StafController::class, 'bayar'])->name('tu.bayar');
    Route::post('/bayar', [StafController::class, 'catat'])->name('tu.bayar.catat');
    Route::post('/pengajuan/{pengajuan}', [StafController::class, 'putuskan'])->name('tu.pengajuan');
    Route::post('/keringanan', [StafController::class, 'keringanan'])->name('tu.keringanan');
    Route::post('/koreksi', [StafController::class, 'koreksi'])->name('tu.koreksi');
    Route::get('/administrasi', [StafController::class, 'administrasi'])->name('tu.administrasi');
    Route::post('/dispensasi', [StafController::class, 'dispensasi'])->name('tu.dispensasi');
    Route::delete('/dispensasi/{siswa}', [StafController::class, 'cabutDispensasi'])->name('tu.dispensasi.cabut');
});

Route::middleware(['auth', 'peran:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin');
    Route::post('/kosongkan', [AdminController::class, 'kosongkan'])->name('admin.kosongkan');
    Route::post('/sinkron', [AdminController::class, 'sinkron'])->name('admin.sinkron');
    Route::post('/absensi', [AdminController::class, 'absensi'])->name('admin.absensi');
    Route::post('/jabatan/{pegawai}', [AdminController::class, 'jabatan'])->name('admin.jabatan');
    Route::post('/urutan/{pegawai}', [AdminController::class, 'urutan'])->name('admin.urutan');
    Route::post('/rombel/{rombel}', [AdminController::class, 'rombel'])->name('admin.rombel');
    Route::post('/rekening', [AdminController::class, 'rekening'])->name('admin.rekening');
});

Route::middleware(['auth', 'peran:murid'])->prefix('murid')->group(function () {
    Route::get('/', [MuridController::class, 'home'])->name('murid');
    Route::post('/ajukan', [MuridController::class, 'ajukan'])->name('murid.ajukan');
});
