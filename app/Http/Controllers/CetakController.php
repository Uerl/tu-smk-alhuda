<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Rekening;
use App\Models\Siswa;
use App\Tu\Hitung;

class CetakController extends Controller
{
    public function kwitansi(Pembayaran $pembayaran)
    {
        $this->boleh($pembayaran->siswa_id);
        $pembayaran->load(['siswa.rombel', 'tagihan', 'petugas']);

        return view('cetak.kwitansi', ['bayar' => $pembayaran]);
    }

    public function gabungan(Siswa $siswa)
    {
        $this->boleh($siswa->id);
        $siswa->load(['rombel', 'pembayaran.tagihan']);

        return view('cetak.gabungan', compact('siswa'));
    }

    public function kartu(Siswa $siswa)
    {
        $this->boleh($siswa->id);
        $siswa->load(['rombel.walikelas', 'keringanan', 'pembayaran', 'dispensasi']);

        return view('cetak.kartu', [
            'siswa' => $siswa,
            'tagihan' => Hitung::tagihanSiswa($siswa),
        ]);
    }

    public function surat(Siswa $siswa)
    {
        $this->boleh($siswa->id);
        $siswa->load(['rombel.walikelas', 'keringanan', 'pembayaran', 'dispensasi', 'janji']);

        return view('cetak.surat', [
            'siswa' => $siswa,
            'tagihan' => Hitung::tagihanSiswa($siswa),
            'rekening' => Rekening::query()->where('aktif', true)->first(),
        ]);
    }

    private function boleh(int $siswaId): void
    {
        $user = auth()->user();
        if (in_array($user->peran, ['admin', 'staf'], true)) {
            return;
        }
        abort_unless($user->peran === 'murid' && (int) $user->siswa?->id === $siswaId, 403);
    }
}
