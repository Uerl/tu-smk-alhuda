<?php

namespace App\Tu;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class KlienPresensi
{
    public static function ambil(): array
    {
        $permintaan = self::masuk();
        $paket = ['pegawai' => [], 'rombel' => [], 'siswa' => []];
        foreach (['/guru.php', '/karyawan.php', '/pegawai.php'] as $jalan) {
            $jawab = $permintaan()->get($jalan);
            if ($jawab->successful() && ! str_contains($jawab->body(), 'id="formLogin"')) {
                $paket = BacaPresensi::gabung($paket, BacaPresensi::dariHtml($jawab->body()));
            }
        }
        $murid = $permintaan()->get('/siswa.php');
        if (! $murid->successful() || str_contains($murid->body(), 'id="formLogin"')) {
            throw new RuntimeException('Halaman murid presensi tidak terbuka setelah login.');
        }
        $paket = BacaPresensi::gabung($paket, BacaPresensi::dariHtml($murid->body()));
        if ($paket['siswa'] === []) {
            throw new RuntimeException('Tabel murid tidak berisi NISN, kelas, dan rombel. Data lama tidak dihapus.');
        }

        return $paket;
    }

    private static function masuk(): \Closure
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

        return $permintaan;
    }
}
