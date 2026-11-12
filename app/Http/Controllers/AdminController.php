<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Rekening;
use App\Models\Rombel;
use App\Models\Riwayat;
use App\Tu\SumberPresensi;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index', [
            'pegawai' => Pegawai::query()->orderBy('urutan')->orderBy('nama')->get(),
            'rombel' => Rombel::query()->with(['walikelas', 'bk'])->orderBy('nama')->get(),
            'rekening' => Rekening::query()->where('aktif', true)->first(),
            'guru' => Pegawai::query()->orderBy('nama')->get(),
        ]);
    }

    public function kosongkan()
    {
        $hasil = SumberPresensi::kosongkanAkademik();
        Riwayat::catat('kosongkan', 'siswa', null, $hasil['murid'].' murid dihapus');

        return back()->with('kabar', 'Data contoh dihapus: '.$hasil['murid'].' murid, '.$hasil['rombel'].' rombel, '.$hasil['guru'].' guru dan BK. Akun TU, rekening, dan tagihan tetap.');
    }

    public function sinkron()
    {
        try {
            $kabar = SumberPresensi::sinkron();
        } catch (\Throwable $e) {
            return back()->with('galat', $e->getMessage());
        }
        Riwayat::catat('sinkron_presensi', 'siswa', null, $kabar);

        return back()->with('kabar', $kabar);
    }

    public function absensi()
    {
        try {
            $kabar = SumberPresensi::sinkronAbsensi();
        } catch (\Throwable $e) {
            return back()->with('galat', $e->getMessage());
        }
        Riwayat::catat('sinkron_absensi', 'absensi', null, $kabar);

        return back()->with('kabar', $kabar);
    }

    public function jabatan(Request $request, Pegawai $pegawai)
    {
        $data = $request->validate(['jabatan_tu' => 'nullable|in:kepala_tu,bendahara,staf_tu']);
        $pegawai->update(['jabatan_tu' => $data['jabatan_tu'] ?: null]);

        return back()->with('kabar', 'Jabatan TU diperbarui.');
    }

    public function urutan(Request $request, Pegawai $pegawai)
    {
        $arah = $request->string('arah')->toString();
        $daftar = Pegawai::query()->orderBy('urutan')->orderBy('id')->get()->values();
        $indeks = $daftar->search(fn ($item) => $item->id === $pegawai->id);
        $tukar = $arah === 'naik' ? $indeks - 1 : $indeks + 1;
        if ($indeks === false || $tukar < 0 || $tukar >= $daftar->count()) {
            return back();
        }
        $lain = $daftar[$tukar];
        $urutan = $pegawai->urutan;
        $pegawai->update(['urutan' => $lain->urutan]);
        $lain->update(['urutan' => $urutan]);

        return back()->with('kabar', 'Urutan pegawai TU diubah.');
    }

    public function rombel(Request $request, Rombel $rombel)
    {
        $data = $request->validate([
            'walikelas_id' => 'required|exists:pegawai,id',
            'bk_id' => 'required|exists:pegawai,id',
        ]);
        $rombel->update($data);

        return back()->with('kabar', 'Penugasan kelas diperbarui.');
    }

    public function rekening(Request $request)
    {
        $data = $request->validate([
            'bank' => 'required|string|max:100',
            'norek' => 'required|string|max:50',
            'atas_nama' => 'required|string|max:150',
        ]);
        Rekening::query()->where('aktif', true)->update(['aktif' => false]);
        Rekening::query()->create([...$data, 'aktif' => true]);

        return back()->with('kabar', 'Rekening diperbarui.');
    }
}
