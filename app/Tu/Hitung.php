<?php

namespace App\Tu;

use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Hitung
{
    public static function hariIni(): string
    {
        return now()->toDateString();
    }

    public static function kode(string $nis): string
    {
        return 'TU-'.$nis;
    }

    public static function tagihanSiswa(Siswa $siswa): Collection
    {
        $rombel = $siswa->rombel;
        $daftar = Tagihan::query()->where('tahun_pelajaran_id', $rombel->tahun_pelajaran_id)->get();

        return $daftar->filter(function (Tagihan $tagihan) use ($siswa, $rombel) {
            if ($tagihan->sasaran === 'siswa') {
                return (int) $tagihan->siswa_id === (int) $siswa->id;
            }
            if ($tagihan->sasaran === 'rombel') {
                return (int) $tagihan->rombel_id === (int) $rombel->id;
            }

            return $tagihan->tingkat === 'semua' || $tagihan->tingkat === $rombel->tingkat;
        })->values();
    }

    public static function beban(Siswa $siswa, Tagihan $tagihan): array
    {
        $potong = (int) $siswa->keringanan->where('tagihan_id', $tagihan->id)->sum('jumlah');
        $harus = max($tagihan->nominal - $potong, 0);
        $masuk = (int) $siswa->pembayaran->where('tagihan_id', $tagihan->id)->sum('jumlah');

        return [
            'harus' => $harus,
            'masuk' => $masuk,
            'sisa' => max($harus - $masuk, 0),
            'potong' => $potong,
        ];
    }

    public static function statusBayar(int $nominal, int $masuk): string
    {
        if ($nominal <= 0) {
            return 'Lunas';
        }
        if ($masuk <= 0) {
            return 'Belum';
        }
        if ($masuk < $nominal) {
            return 'Angsur';
        }

        return 'Lunas';
    }

    public static function lewat(string $tempo, int $sisa): bool
    {
        return $sisa > 0 && $tempo < self::hariIni();
    }

    public static function statusAdministrasi(Siswa $siswa): array
    {
        $lewat = 0;
        foreach (self::tagihanSiswa($siswa) as $tagihan) {
            $sisa = self::beban($siswa, $tagihan)['sisa'];
            if (self::lewat($tagihan->tempo->toDateString(), $sisa)) {
                $lewat += $sisa;
            }
        }
        $dispensasi = $siswa->dispensasi->first(fn ($item) => $item->dicabut_pada === null && $item->sampai->toDateString() >= self::hariIni());
        if ($dispensasi) {
            return ['status' => 'Dispensasi', 'lewat' => $lewat, 'alasan' => $dispensasi->alasan, 'sampai' => $dispensasi->sampai->toDateString()];
        }
        if ($lewat > 0) {
            return ['status' => 'Ditahan', 'lewat' => $lewat, 'alasan' => '', 'sampai' => ''];
        }

        return ['status' => 'Lancar', 'lewat' => 0, 'alasan' => '', 'sampai' => ''];
    }

    public static function nomorKwitansi(string $tanggal): string
    {
        $urutan = Pembayaran::query()->whereDate('tanggal', $tanggal)->count() + 1;

        return 'KWT-'.str_replace('-', '', $tanggal).'-'.str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);
    }

    public static function bagiTransfer(Siswa $siswa, int $jumlah): array
    {
        $urut = self::tagihanSiswa($siswa)
            ->map(fn (Tagihan $item) => ['item' => $item, 'sisa' => self::beban($siswa, $item)['sisa']])
            ->filter(fn ($baris) => $baris['sisa'] > 0)
            ->sortBy(fn ($baris) => $baris['item']->tempo->toDateString())
            ->values();
        $sisaUang = $jumlah;
        $hasil = [];
        foreach ($urut as $baris) {
            if ($sisaUang <= 0) {
                break;
            }
            $ambil = min($baris['sisa'], $sisaUang);
            $hasil[] = ['tagihan' => $baris['item'], 'jumlah' => $ambil];
            $sisaUang -= $ambil;
        }

        return ['hasil' => $hasil, 'lebih' => $sisaUang];
    }

    public static function geserBulan(string $tanggal): string
    {
        return Carbon::parse($tanggal)->addMonthNoOverflow()->toDateString();
    }

    public static function judulBulanDepan(string $nama, string $tempo): string
    {
        $berikut = Carbon::parse(self::geserBulan($tempo));
        $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $bersih = trim(preg_replace('/\b(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+\d{4}\b/u', '', $nama) ?? $nama);

        return trim(preg_replace('/\s+/', ' ', $bersih.' '.$namaBulan[$berikut->month - 1].' '.$berikut->year) ?? '');
    }

    public static function siswaSiap(): Collection
    {
        return Siswa::query()->with(['rombel.walikelas', 'rombel.bk', 'keringanan', 'pembayaran', 'dispensasi', 'janji', 'user'])->orderBy('nama')->get();
    }
}
