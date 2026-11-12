<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\Rekening;
use App\Tu\Hitung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MuridController extends Controller
{
    public function home()
    {
        $siswa = $this->siswa();

        return view('murid.home', [
            'siswa' => $siswa,
            'tagihan' => Hitung::tagihanSiswa($siswa),
            'status' => Hitung::statusAdministrasi($siswa),
            'rekening' => Rekening::query()->where('aktif', true)->first(),
            'pengumuman' => \App\Models\Pengumuman::query()->where('aktif', true)->latest('id')->first(),
        ]);
    }

    public function ajukan(Request $request)
    {
        $siswa = $this->siswa();
        $data = $request->validate([
            'tagihan_id' => 'required|exists:tagihan,id',
            'jumlah' => 'required|integer|min:1',
            'metode' => 'required|in:tunai,transfer',
            'bukti' => 'required|image|max:2048',
        ]);
        $tagihan = Hitung::tagihanSiswa($siswa)->firstWhere('id', (int) $data['tagihan_id']);
        if (! $tagihan) {
            return back()->with('galat', 'Tagihan ini bukan milik Anda.');
        }
        $sisa = Hitung::beban($siswa, $tagihan)['sisa'];
        if ($data['jumlah'] > $sisa) {
            return back()->with('galat', 'Jumlah tidak boleh melebihi sisa tagihan.');
        }
        $path = $request->file('bukti')->store('bukti', 'public');
        Pengajuan::query()->create([
            'siswa_id' => $siswa->id,
            'tagihan_id' => $tagihan->id,
            'jumlah' => $data['jumlah'],
            'tanggal' => Hitung::hariIni(),
            'metode' => $data['metode'],
            'bukti_path' => $path,
            'status' => 'menunggu',
        ]);

        return back()->with('kabar', 'Bukti pembayaran diajukan.');
    }

    public function bukti(Pengajuan $pengajuan)
    {
        $user = auth()->user();
        $milik = $user->peran === 'murid' && (int) $user->siswa?->id === (int) $pengajuan->siswa_id;
        if (! $milik && ! in_array($user->peran, ['admin', 'staf'], true)) {
            abort(403);
        }
        abort_unless(Storage::disk('public')->exists($pengajuan->bukti_path), 404);

        return Storage::disk('public')->response($pengajuan->bukti_path);
    }

    private function siswa()
    {
        return auth()->user()->siswa()->with(['rombel.walikelas', 'rombel.bk', 'keringanan', 'pembayaran.tagihan', 'pengajuan.tagihan', 'dispensasi', 'janji'])->firstOrFail();
    }
}
