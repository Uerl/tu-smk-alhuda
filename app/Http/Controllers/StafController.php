<?php

namespace App\Http\Controllers;

use App\Models\Dispensasi;
use App\Models\JanjiBayar;
use App\Models\Keringanan;
use App\Models\Pegawai;
use App\Models\Pembayaran;
use App\Models\Pengajuan;
use App\Models\Pengumuman;
use App\Models\Rekening;
use App\Models\Rombel;
use App\Models\SetorBank;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\TahunPelajaran;
use App\Models\TutupKas;
use App\Models\Riwayat;
use App\Tu\Hitung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StafController extends Controller
{
    public function dasbor()
    {
        $siswa = Hitung::siswaSiap();
        $harus = 0;
        $masuk = 0;
        $lewat = 0;
        $tingkat = [];
        foreach ($siswa as $murid) {
            $kunci = $murid->rombel->tingkat;
            $tingkat[$kunci] ??= ['harus' => 0, 'masuk' => 0];
            foreach (Hitung::tagihanSiswa($murid) as $tagihan) {
                $beban = Hitung::beban($murid, $tagihan);
                $harus += $beban['harus'];
                $bayar = min($beban['masuk'], $beban['harus']);
                $masuk += $bayar;
                $tingkat[$kunci]['harus'] += $beban['harus'];
                $tingkat[$kunci]['masuk'] += $bayar;
                if (Hitung::lewat($tagihan->tempo->toDateString(), $beban['sisa'])) {
                    $lewat++;
                }
            }
        }

        return view('tu.dasbor', [
            'harus' => $harus,
            'masuk' => $masuk,
            'sisa' => max($harus - $masuk, 0),
            'menunggu' => Pengajuan::query()->where('status', 'menunggu')->count(),
            'lewat' => $lewat,
            'tingkat' => $tingkat,
            'pengumuman' => Pengumuman::query()->where('aktif', true)->latest('id')->first(),
            'pegawai' => Pegawai::query()->orderBy('urutan')->get(),
            'petugas' => session('petugas_user_id'),
        ]);
    }

    public function pengumuman(Request $request)
    {
        $data = $request->validate(['judul' => 'required|string|max:150', 'isi' => 'required|string']);
        Pengumuman::query()->where('aktif', true)->update(['aktif' => false]);
        Pengumuman::query()->create([...$data, 'tanggal' => Hitung::hariIni(), 'petugas_id' => $request->user()->id, 'aktif' => true]);

        return back()->with('kabar', 'Pengumuman diperbarui.');
    }

    public function petugas(Request $request)
    {
        $data = $request->validate(['petugas_id' => 'required|exists:users,id']);
        session(['petugas_user_id' => (int) $data['petugas_id']]);

        return back();
    }

    public function tunggakan()
    {
        $baris = [];
        $segera = [];
        $batas = now()->addDays(7)->toDateString();
        foreach (Hitung::siswaSiap() as $murid) {
            $sisa = 0;
            foreach (Hitung::tagihanSiswa($murid) as $tagihan) {
                $beban = Hitung::beban($murid, $tagihan);
                if ($beban['sisa'] <= 0) {
                    continue;
                }
                $sisa += $beban['sisa'];
                $tempo = $tagihan->tempo->toDateString();
                if ($tempo >= Hitung::hariIni() && $tempo <= $batas) {
                    $segera[] = ['siswa' => $murid, 'tagihan' => $tagihan, 'sisa' => $beban['sisa']];
                }
            }
            if ($sisa > 0) {
                $baris[] = ['siswa' => $murid, 'sisa' => $sisa];
            }
        }
        usort($baris, fn ($a, $b) => $b['sisa'] <=> $a['sisa']);

        return view('tu.tunggakan', compact('baris', 'segera'));
    }

    public function janji(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tanggal' => 'required|date',
            'catatan' => 'nullable|string|max:255',
        ]);
        JanjiBayar::query()->updateOrCreate(
            ['siswa_id' => $data['siswa_id']],
            ['tanggal' => $data['tanggal'], 'catatan' => $data['catatan'] ?? '', 'petugas_id' => $request->user()->id],
        );

        return back()->with('kabar', 'Janji bayar disimpan.');
    }

    public function hapusJanji(Siswa $siswa)
    {
        $siswa->janji()?->delete();

        return back()->with('kabar', 'Janji bayar dihapus.');
    }

    public function kas(Request $request)
    {
        $query = Pembayaran::query()->with(['siswa', 'tagihan'])->orderByDesc('tanggal')->orderByDesc('id');
        if ($request->filled('mulai')) {
            $query->whereDate('tanggal', '>=', $request->string('mulai'));
        }
        if ($request->filled('akhir')) {
            $query->whereDate('tanggal', '<=', $request->string('akhir'));
        }
        $daftar = $query->get();
        $tanggal = $request->string('tutup', Hitung::hariIni())->toString();
        $tunai = (int) Pembayaran::query()->whereDate('tanggal', $tanggal)->where('metode', 'tunai')->sum('jumlah');
        $laci = (int) Pembayaran::query()->where('metode', 'tunai')->sum('jumlah') - (int) SetorBank::query()->sum('jumlah');

        return view('tu.kas', [
            'daftar' => $daftar,
            'total' => (int) $daftar->sum('jumlah'),
            'tunai' => $tunai,
            'tanggal' => $tanggal,
            'tutup' => TutupKas::query()->orderByDesc('tanggal')->get(),
            'setor' => SetorBank::query()->latest('id')->get(),
            'laci' => $laci,
        ]);
    }

    public function tutupKas(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'hitung' => 'required|integer|min:0',
            'catatan' => 'nullable|string|max:255',
        ]);
        $tunai = (int) Pembayaran::query()->whereDate('tanggal', $data['tanggal'])->where('metode', 'tunai')->sum('jumlah');
        TutupKas::query()->updateOrCreate(
            ['tanggal' => $data['tanggal']],
            ['hitung' => $data['hitung'], 'tunai_tercatat' => $tunai, 'selisih' => $data['hitung'] - $tunai, 'catatan' => $data['catatan'] ?? '', 'petugas_id' => $request->user()->id],
        );
        Riwayat::catat('tutup_kas', 'tutup_kas', null, 'Tutup kas '.$data['tanggal']);

        return back()->with('kabar', 'Kas harian ditutup.');
    }

    public function setor(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'jumlah' => 'required|integer|min:1',
            'berita' => 'nullable|string|max:150',
        ]);
        $laci = (int) Pembayaran::query()->where('metode', 'tunai')->sum('jumlah') - (int) SetorBank::query()->sum('jumlah');
        if ($data['jumlah'] > $laci) {
            return back()->with('galat', 'Setoran tidak boleh melebihi uang tunai di laci.');
        }
        $setor = SetorBank::query()->create([...$data, 'petugas_id' => $request->user()->id]);
        Riwayat::catat('setor', 'setor_bank', $setor->id, 'Setor '.$data['jumlah']);

        return back()->with('kabar', 'Setoran bank dicatat.');
    }

    public function unduhKas(Request $request): StreamedResponse
    {
        $daftar = Pembayaran::query()->with(['siswa', 'tagihan'])->orderBy('tanggal')->get();

        return response()->streamDownload(function () use ($daftar) {
            $berkas = fopen('php://output', 'w');
            fputcsv($berkas, ['Tanggal', 'Nomor', 'NIS', 'Nama', 'Tagihan', 'Metode', 'Jumlah']);
            foreach ($daftar as $item) {
                fputcsv($berkas, [$item->tanggal->toDateString(), $item->nomor, $item->siswa->nis, $item->siswa->nama, $item->tagihan->nama, $item->metode, $item->jumlah]);
            }
            fclose($berkas);
        }, 'kas-tu.csv', ['Content-Type' => 'text/csv']);
    }

    public function cocok()
    {
        return view('tu.cocok');
    }

    public function simpanCocok(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
        ]);
        $nis = strtoupper(trim(preg_replace('/^TU-/i', '', $data['kode']) ?? ''));
        $siswa = Siswa::query()->with(['rombel', 'keringanan', 'pembayaran', 'dispensasi'])->where('nis', $nis)->first();
        if (! $siswa) {
            return back()->with('galat', 'Kode bayar tidak ditemukan.')->withInput();
        }
        $bagi = Hitung::bagiTransfer($siswa, (int) $data['jumlah']);
        if ($bagi['hasil'] === []) {
            return back()->with('galat', 'Murid ini tidak punya sisa tagihan.')->withInput();
        }
        DB::transaction(function () use ($bagi, $siswa, $data, $request) {
            foreach ($bagi['hasil'] as $baris) {
                Pembayaran::query()->create([
                    'siswa_id' => $siswa->id,
                    'tagihan_id' => $baris['tagihan']->id,
                    'jumlah' => $baris['jumlah'],
                    'tanggal' => $data['tanggal'],
                    'nomor' => Hitung::nomorKwitansi($data['tanggal']),
                    'metode' => 'transfer',
                    'asal' => 'cocok',
                    'petugas_id' => $this->petugasId($request),
                ]);
            }
        });
        $pesan = 'Transfer dicocokkan.';
        if ($bagi['lebih'] > 0) {
            $pesan .= ' Sisa uang '.number_format($bagi['lebih'], 0, ',', '.').' tidak dipakai.';
        }

        return back()->with('kabar', $pesan);
    }

    public function laporan()
    {
        $siswa = Hitung::siswaSiap();
        $wali = [];
        $jurusan = [];
        $umur = ['belum' => 0, 'seminggu' => 0, 'sebulan' => 0, 'lama' => 0];
        $sekarang = now()->startOfDay();
        foreach ($siswa as $murid) {
            $namaWali = $murid->rombel->walikelas?->nama ?? '-';
            $kode = $murid->rombel->jurusan;
            $wali[$namaWali] ??= ['kelas' => [], 'siswa' => 0, 'sisa' => 0, 'lewat' => 0];
            $jurusan[$kode] ??= ['kelas' => [], 'siswa' => 0, 'sisa' => 0, 'ditahan' => 0];
            $wali[$namaWali]['kelas'][$murid->rombel->nama] = true;
            $jurusan[$kode]['kelas'][$murid->rombel->nama] = true;
            $wali[$namaWali]['siswa']++;
            $jurusan[$kode]['siswa']++;
            if (Hitung::statusAdministrasi($murid)['status'] === 'Ditahan') {
                $jurusan[$kode]['ditahan']++;
            }
            foreach (Hitung::tagihanSiswa($murid) as $tagihan) {
                $sisa = Hitung::beban($murid, $tagihan)['sisa'];
                if ($sisa <= 0) {
                    continue;
                }
                $wali[$namaWali]['sisa'] += $sisa;
                $jurusan[$kode]['sisa'] += $sisa;
                if (Hitung::lewat($tagihan->tempo->toDateString(), $sisa)) {
                    $wali[$namaWali]['lewat']++;
                }
                $hari = $tagihan->tempo->copy()->startOfDay()->diffInDays($sekarang, false);
                if ($hari <= 0) {
                    $umur['belum'] += $sisa;
                } elseif ($hari <= 7) {
                    $umur['seminggu'] += $sisa;
                } elseif ($hari <= 30) {
                    $umur['sebulan'] += $sisa;
                } else {
                    $umur['lama'] += $sisa;
                }
            }
        }

        return view('tu.laporan', compact('wali', 'jurusan', 'umur'));
    }

    public function unduhTunggakan(): StreamedResponse
    {
        $siswa = Hitung::siswaSiap();

        return response()->streamDownload(function () use ($siswa) {
            $berkas = fopen('php://output', 'w');
            fputcsv($berkas, ['NIS', 'Nama', 'Rombel', 'Walikelas', 'Sisa']);
            foreach ($siswa as $murid) {
                $sisa = 0;
                foreach (Hitung::tagihanSiswa($murid) as $tagihan) {
                    $sisa += Hitung::beban($murid, $tagihan)['sisa'];
                }
                if ($sisa > 0) {
                    fputcsv($berkas, [$murid->nis, $murid->nama, $murid->rombel->nama, $murid->rombel->walikelas?->nama, $sisa]);
                }
            }
            fclose($berkas);
        }, 'tunggakan-tu.csv', ['Content-Type' => 'text/csv']);
    }

    public function rombel()
    {
        $rombel = Rombel::query()->with(['walikelas', 'bk', 'siswa'])->orderBy('tingkat')->orderBy('nama')->get();

        return view('tu.rombel', compact('rombel'));
    }

    public function tagihan()
    {
        return view('tu.tagihan', [
            'tagihan' => Tagihan::query()->with(['rombel'])->latest('id')->get(),
            'rombel' => Rombel::query()->orderBy('nama')->get(),
            'siswa' => Siswa::query()->orderBy('nama')->get(),
            'tahun' => TahunPelajaran::query()->where('aktif', true)->first(),
        ]);
    }

    public function simpanTagihan(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:150',
            'nominal' => 'required|integer|min:0',
            'tempo' => 'required|date',
            'sasaran' => 'required|in:tingkat,rombel,siswa',
            'tingkat' => 'nullable|in:semua,X,XI,XII',
            'rombel_id' => 'nullable|exists:rombel,id',
            'siswa_id' => 'nullable|exists:siswa,id',
        ]);
        $tahun = TahunPelajaran::query()->where('aktif', true)->firstOrFail();
        Tagihan::query()->create([
            'tahun_pelajaran_id' => $tahun->id,
            'nama' => $data['nama'],
            'nominal' => $data['nominal'],
            'tempo' => $data['tempo'],
            'sasaran' => $data['sasaran'],
            'tingkat' => $data['sasaran'] === 'tingkat' ? ($data['tingkat'] ?? 'semua') : 'semua',
            'rombel_id' => $data['sasaran'] === 'rombel' ? $data['rombel_id'] : null,
            'siswa_id' => $data['sasaran'] === 'siswa' ? $data['siswa_id'] : null,
        ]);

        return back()->with('kabar', 'Tagihan ditambahkan.');
    }

    public function bulanDepan(Tagihan $tagihan)
    {
        $tempo = Hitung::geserBulan($tagihan->tempo->toDateString());
        $nama = Hitung::judulBulanDepan($tagihan->nama, $tagihan->tempo->toDateString());
        $ada = Tagihan::query()->where('nama', $nama)->whereDate('tempo', $tempo)->where('sasaran', $tagihan->sasaran)->where('rombel_id', $tagihan->rombel_id)->where('siswa_id', $tagihan->siswa_id)->exists();
        if ($ada) {
            return back()->with('kabar', 'Tagihan bulan depan sudah ada.');
        }
        $baru = $tagihan->replicate();
        $baru->nama = $nama;
        $baru->tempo = $tempo;
        $baru->save();

        return back()->with('kabar', 'Tagihan bulan depan dibuat.');
    }

    public function bayar(Request $request)
    {
        $siswa = Hitung::siswaSiap();
        if ($request->filled('tingkat')) {
            $tingkat = $request->string('tingkat')->toString();
            $siswa = $siswa->filter(fn ($murid) => $murid->rombel->tingkat === $tingkat);
        }
        if ($request->filled('rombel')) {
            $siswa = $siswa->where('rombel_id', (int) $request->string('rombel'));
        }
        if ($request->filled('bk')) {
            $siswa = $siswa->filter(fn ($murid) => $murid->rombel->bk?->nama === $request->string('bk')->toString());
        }

        return view('tu.bayar', [
            'siswa' => $siswa,
            'semua' => Hitung::siswaSiap(),
            'rombel' => Rombel::query()->with('bk')->orderBy('nama')->get(),
            'pengajuan' => Pengajuan::query()->with(['siswa.rombel', 'tagihan'])->where('status', 'menunggu')->latest('id')->get(),
            'tagihan' => Tagihan::query()->orderBy('nama')->get(),
        ]);
    }

    public function catat(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tagihan_id' => 'required|exists:tagihan,id',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'metode' => 'required|in:tunai,transfer',
        ]);
        $siswa = Siswa::query()->with(['rombel', 'keringanan', 'pembayaran'])->findOrFail($data['siswa_id']);
        $tagihan = Tagihan::query()->findOrFail($data['tagihan_id']);
        if (! Hitung::tagihanSiswa($siswa)->contains(fn ($item) => $item->id === $tagihan->id)) {
            return back()->with('galat', 'Tagihan ini tidak berlaku untuk siswa tersebut.');
        }
        $sisa = Hitung::beban($siswa, $tagihan)['sisa'];
        if ($data['jumlah'] > $sisa) {
            return back()->with('galat', 'Jumlah tidak boleh melebihi sisa tagihan.');
        }
        Pembayaran::query()->create([
            ...$data,
            'nomor' => Hitung::nomorKwitansi($data['tanggal']),
            'asal' => 'loket',
            'petugas_id' => $this->petugasId($request),
        ]);

        return back()->with('kabar', 'Pembayaran tercatat.');
    }

    public function putuskan(Request $request, Pengajuan $pengajuan)
    {
        $data = $request->validate([
            'status' => 'required|in:diterima,ditolak',
            'alasan' => 'nullable|string|max:255',
        ]);
        if ($pengajuan->status !== 'menunggu') {
            return back()->with('galat', 'Pengajuan ini sudah diputuskan.');
        }
        if ($data['status'] === 'ditolak' && trim((string) ($data['alasan'] ?? '')) === '') {
            return back()->with('galat', 'Alasan penolakan wajib diisi.');
        }
        if ($data['status'] === 'diterima') {
            $siswa = $pengajuan->siswa()->with(['rombel', 'keringanan', 'pembayaran'])->first();
            $sisa = Hitung::beban($siswa, $pengajuan->tagihan)['sisa'];
            if ($pengajuan->jumlah > $sisa) {
                return back()->with('galat', 'Jumlah pengajuan melebihi sisa tagihan.');
            }
            Pembayaran::query()->create([
                'siswa_id' => $pengajuan->siswa_id,
                'tagihan_id' => $pengajuan->tagihan_id,
                'jumlah' => $pengajuan->jumlah,
                'tanggal' => $pengajuan->tanggal,
                'nomor' => Hitung::nomorKwitansi($pengajuan->tanggal->toDateString()),
                'metode' => $pengajuan->metode,
                'asal' => 'verifikasi',
                'pengajuan_id' => $pengajuan->id,
                'petugas_id' => $this->petugasId($request),
            ]);
        }
        $pengajuan->update([
            'status' => $data['status'],
            'alasan' => $data['alasan'] ?? null,
            'petugas_id' => $this->petugasId($request),
            'diverifikasi_pada' => now(),
        ]);

        return back()->with('kabar', $data['status'] === 'diterima' ? 'Pembayaran diterima.' : 'Pengajuan ditolak.');
    }

    public function keringanan(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tagihan_id' => 'required|exists:tagihan,id',
            'jumlah' => 'required|integer|min:0',
            'alasan' => 'nullable|string|max:255',
        ]);
        $tagihan = Tagihan::query()->findOrFail($data['tagihan_id']);
        if ($data['jumlah'] > $tagihan->nominal) {
            return back()->with('galat', 'Keringanan tidak boleh melebihi nominal.');
        }
        if ($data['jumlah'] <= 0) {
            Keringanan::query()->where('siswa_id', $data['siswa_id'])->where('tagihan_id', $data['tagihan_id'])->delete();

            return back()->with('kabar', 'Keringanan dihapus.');
        }
        Keringanan::query()->updateOrCreate(
            ['siswa_id' => $data['siswa_id'], 'tagihan_id' => $data['tagihan_id']],
            ['jumlah' => $data['jumlah'], 'alasan' => $data['alasan'] ?: 'Keringanan', 'petugas_id' => $request->user()->id],
        );

        return back()->with('kabar', 'Keringanan disimpan.');
    }

    public function koreksi(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tagihan_id' => 'required|exists:tagihan,id',
            'nama' => 'required|string|max:150',
            'nominal' => 'required|integer|min:0',
            'tingkat' => 'nullable|in:semua,X,XI,XII',
            'jumlah' => 'required|integer|min:0',
        ]);
        $tagihan = Tagihan::query()->findOrFail($data['tagihan_id']);
        $tagihan->nama = $data['nama'];
        $tagihan->nominal = $data['nominal'];
        if (($tagihan->sasaran === 'tingkat' || ! $tagihan->sasaran) && ! empty($data['tingkat'])) {
            $tagihan->tingkat = $data['tingkat'];
        }
        $tagihan->save();
        $milik = Pembayaran::query()->where('siswa_id', $data['siswa_id'])->where('tagihan_id', $tagihan->id)->orderByDesc('id')->get();
        $sekarang = (int) $milik->sum('jumlah');
        $jumlah = (int) $data['jumlah'];
        if ($jumlah <= 0) {
            Pembayaran::query()->whereIn('id', $milik->pluck('id'))->delete();
        } elseif ($jumlah > $sekarang) {
            Pembayaran::query()->create([
                'siswa_id' => $data['siswa_id'],
                'tagihan_id' => $tagihan->id,
                'jumlah' => $jumlah - $sekarang,
                'tanggal' => Hitung::hariIni(),
                'nomor' => Hitung::nomorKwitansi(Hitung::hariIni()),
                'metode' => 'tunai',
                'asal' => 'koreksi',
                'petugas_id' => $this->petugasId($request),
            ]);
        } elseif ($jumlah < $sekarang) {
            $kurang = $sekarang - $jumlah;
            foreach ($milik as $item) {
                if ($kurang <= 0) {
                    break;
                }
                if ($item->jumlah <= $kurang) {
                    $kurang -= $item->jumlah;
                    $item->delete();
                } else {
                    $item->update(['jumlah' => $item->jumlah - $kurang]);
                    $kurang = 0;
                }
            }
        }
        Riwayat::catat('koreksi_bayar', 'tagihan', $tagihan->id, 'Koreksi pembayaran siswa '.$data['siswa_id']);

        return back()->with('kabar', 'Tagihan yang sudah terbayar diperbarui. Kwitansi lama tetap.');
    }

    public function administrasi()
    {
        $baris = Hitung::siswaSiap()->map(fn (Siswa $murid) => ['siswa' => $murid, 'status' => Hitung::statusAdministrasi($murid)]);

        return view('tu.administrasi', ['baris' => $baris, 'rekening' => Rekening::query()->where('aktif', true)->first()]);
    }

    public function dispensasi(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'sampai' => 'required|date',
            'alasan' => 'nullable|string|max:255',
        ]);
        Dispensasi::query()->where('siswa_id', $data['siswa_id'])->whereNull('dicabut_pada')->update(['dicabut_pada' => now()]);
        Dispensasi::query()->create([...$data, 'petugas_id' => $request->user()->id]);

        return back()->with('kabar', 'Dispensasi diberikan.');
    }

    public function cabutDispensasi(Siswa $siswa)
    {
        $siswa->dispensasi()->whereNull('dicabut_pada')->update(['dicabut_pada' => now()]);

        return back()->with('kabar', 'Dispensasi dicabut.');
    }

    private function petugasId(Request $request): int
    {
        $id = (int) session('petugas_user_id');
        if ($id > 0 && \App\Models\User::query()->whereKey($id)->exists()) {
            return $id;
        }

        return $request->user()->id;
    }
}
