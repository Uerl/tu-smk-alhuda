<?php

namespace App\Tu;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class KlienPresensi
{
    public static function ambil(): array
    {
        $dasar = rtrim((string) config('presensi.url'), '/');
        $user = (string) config('presensi.user');
        $sandi = (string) config('presensi.sandi');
        if ($user === '' || $sandi === '') {
            throw new RuntimeException('Isi PRESENSI_USER dan PRESENSI_SANDI di berkas .env, lalu tarik ulang.');
        }
        $kue = new CookieJar();
        $permintaan = function () use ($dasar, $kue) {
            return Http::withOptions(['cookies' => $kue])->baseUrl($dasar)->timeout(25)->accept('text/html,application/json');
        };
        $masuk = $permintaan()->asForm()->post('/index.php', [
            'username' => $user,
            'password' => $sandi,
            'device_id' => 'tu-smk-alhuda',
            'device_cred' => '',
        ]);
        if (! $masuk->successful() || str_contains($masuk->body(), 'id="formLogin"')) {
            throw new RuntimeException('Presensi menolak masuk. Periksa username dan kata sandi di .env.');
        }
        $paket = ['pegawai' => [], 'rombel' => [], 'siswa' => []];
        $halaman = ['/dashboard.php', '/siswa.php', '/guru.php', '/walikelas.php', '/jadwal.php', '/rekap.php'];
        $sudah = [];
        while ($halaman !== []) {
            $jalan = array_shift($halaman);
            if (isset($sudah[$jalan]) || count($sudah) > 12) {
                continue;
            }
            $sudah[$jalan] = true;
            $jawab = $permintaan()->get($jalan);
            if (! $jawab->successful() || str_contains($jawab->body(), 'id="formLogin"')) {
                continue;
            }
            $paket = BacaPresensi::gabung($paket, BacaPresensi::dariHtml($jawab->body()));
            if (preg_match_all('/href="([^"]+\.php[^"]*)"/', $jawab->body(), $cocok)) {
                foreach ($cocok[1] as $tautan) {
                    $path = parse_url(html_entity_decode($tautan), PHP_URL_PATH) ?: '';
                    if ($path === '' || str_contains($path, 'logout') || str_contains($path, 'keluar')) {
                        continue;
                    }
                    $halaman[] = str_starts_with($path, '/') ? $path : '/'.$path;
                }
            }
        }
        if ($paket['siswa'] === []) {
            throw new RuntimeException('Masuk ke presensi berhasil, tetapi tabel murid tidak ditemukan. Sinkron dibatalkan, data lama tidak dihapus.');
        }

        return $paket;
    }
}
