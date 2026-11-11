<?php

namespace Database\Seeders;

use App\Models\Dispensasi;
use App\Models\JanjiBayar;
use App\Models\Keringanan;
use App\Models\Pegawai;
use App\Models\Pembayaran;
use App\Models\Pengajuan;
use App\Models\Pengumuman;
use App\Models\Rekening;
use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\TahunPelajaran;
use App\Models\User;
use App\Tu\SumberPresensi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tahun = TahunPelajaran::query()->create(['nama' => '2026/2027', 'aktif' => true]);
        $akun = [
            '19880101' => User::query()->create(['name' => 'Sri Lestari', 'username' => 'admin', 'password' => Hash::make('Tu12345'), 'peran' => 'admin']),
            '19900202' => User::query()->create(['name' => 'Bambang Nugroho', 'username' => 'bendahara', 'password' => Hash::make('Tu12345'), 'peran' => 'staf']),
            '19950303' => User::query()->create(['name' => 'Dewi Kartika', 'username' => 'staf', 'password' => Hash::make('Tu12345'), 'peran' => 'staf']),
        ];
        $jabatan = ['19880101' => 'kepala_tu', '19900202' => 'bendahara', '19950303' => 'staf_tu'];
        $urutan = 0;
        foreach (SumberPresensi::pegawai() as $orang) {
            $urutan++;
            Pegawai::query()->create([
                'user_id' => isset($akun[$orang['nip']]) ? $akun[$orang['nip']]->id : null,
                'nip' => $orang['nip'],
                'nama' => $orang['nama'],
                'bagian' => $orang['bagian'],
                'jabatan_tu' => $jabatan[$orang['nip']] ?? null,
                'urutan' => $urutan,
            ]);
        }
        $pegawai = Pegawai::query()->get()->keyBy('nama');
        foreach (SumberPresensi::rombel() as $kelas) {
            $wali = $kelas['nama'] === 'X TKJ 1' ? 'Ahmad Fauzi' : $kelas['walikelas'];
            Rombel::query()->create([
                'tahun_pelajaran_id' => $tahun->id,
                'tingkat' => $kelas['tingkat'],
                'jurusan' => $kelas['jurusan'],
                'nama' => $kelas['nama'],
                'walikelas_id' => $pegawai[$wali]->id,
                'bk_id' => $pegawai[$kelas['bk']]->id,
            ]);
        }
        $rombel = Rombel::query()->get()->keyBy('nama');
        $sandi = Hash::make('Murid123');
        $pengguna = [];
        $sekarang = now();
        foreach (SumberPresensi::siswa() as $murid) {
            $pengguna[] = [
                'name' => $murid['nama'],
                'username' => $murid['nis'],
                'password' => $sandi,
                'peran' => 'murid',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];
        }
        foreach (array_chunk($pengguna, 100) as $potong) {
            User::query()->insert($potong);
        }
        $userId = User::query()->where('peran', 'murid')->pluck('id', 'username');
        $muridBaris = [];
        foreach (SumberPresensi::siswa() as $murid) {
            $muridBaris[] = [
                'user_id' => $userId[$murid['nis']],
                'nis' => $murid['nis'],
                'nama' => $murid['nama'],
                'rombel_id' => $rombel[$murid['rombel']]->id,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];
        }
        foreach (array_chunk($muridBaris, 100) as $potong) {
            Siswa::query()->insert($potong);
        }
        Rekening::query()->create(['bank' => 'Bank Jatim', 'norek' => '001234567890', 'atas_nama' => 'SMK Al Huda Kota Kediri', 'aktif' => true]);
        $tagihan = [
            'spp' => Tagihan::query()->create(['tahun_pelajaran_id' => $tahun->id, 'nama' => 'SPP November 2026', 'nominal' => 150000, 'tingkat' => 'semua', 'sasaran' => 'tingkat', 'tempo' => '2026-11-10']),
            'seragam' => Tagihan::query()->create(['tahun_pelajaran_id' => $tahun->id, 'nama' => 'Seragam', 'nominal' => 350000, 'tingkat' => 'X', 'sasaran' => 'tingkat', 'tempo' => '2026-12-01']),
            'praktik' => Tagihan::query()->create(['tahun_pelajaran_id' => $tahun->id, 'nama' => 'Praktik jurusan', 'nominal' => 200000, 'tingkat' => 'semua', 'sasaran' => 'tingkat', 'tempo' => '2026-11-20']),
            'ulang' => Tagihan::query()->create(['tahun_pelajaran_id' => $tahun->id, 'nama' => 'Daftar ulang', 'nominal' => 500000, 'tingkat' => 'X', 'sasaran' => 'tingkat', 'tempo' => '2026-11-05']),
            'tour' => Tagihan::query()->create(['tahun_pelajaran_id' => $tahun->id, 'nama' => 'Studi tour', 'nominal' => 100000, 'tingkat' => 'X', 'sasaran' => 'rombel', 'rombel_id' => $rombel['X TKJ 1']->id, 'tempo' => '2026-12-15']),
        ];
        $bendahara = $akun['19900202']->id;
        $siswa = Siswa::query()->with('rombel')->orderBy('id')->get();
        $bayar = [];
        $nomer = 1;
        $janji = null;
        $ajukan = [];
        $dispensasi = null;
        foreach ($siswa as $orang) {
            $tingkat = $orang->rombel->tingkat;
            $kelas = $orang->rombel->nama;
            $tagih = [
                ['id' => $tagihan['spp']->id, 'nominal' => 150000],
                ['id' => $tagihan['praktik']->id, 'nominal' => 200000],
            ];
            if ($tingkat === 'X') {
                $tagih[] = ['id' => $tagihan['seragam']->id, 'nominal' => 350000];
                $tagih[] = ['id' => $tagihan['ulang']->id, 'nominal' => 500000];
            }
            if ($kelas === 'X TKJ 1') {
                $tagih[] = ['id' => $tagihan['tour']->id, 'nominal' => 100000];
            }
            $pola = ((int) substr($orang->nis, -2)) % 5;
            if ($orang->nis === '20240011') {
                $bayar[] = $this->bayar($orang->id, $tagihan['spp']->id, 150000, $bendahara, $nomer++);
                $bayar[] = $this->bayar($orang->id, $tagihan['seragam']->id, 200000, $bendahara, $nomer++);
                continue;
            }
            foreach ($tagih as $urut => $item) {
                $jumlah = match ($pola) {
                    0 => $item['nominal'],
                    1 => 0,
                    2 => $urut === 0 ? intdiv($item['nominal'], 2) : 0,
                    3 => $item['id'] === $tagihan['spp']->id ? $item['nominal'] : 0,
                    default => $item['id'] === $tagihan['praktik']->id ? 0 : $item['nominal'],
                };
                if ($jumlah > 0) {
                    $bayar[] = $this->bayar($orang->id, $item['id'], $jumlah, $bendahara, $nomer++);
                }
            }
            if ($pola === 1 && $janji === null) {
                $janji = $orang->id;
            }
            if ($pola === 1 && count($ajukan) < 8) {
                $ajukan[] = $orang;
            }
            if ($pola === 1 && $tingkat === 'XII' && $dispensasi === null) {
                $dispensasi = $orang->id;
            }
        }
        foreach (array_chunk($bayar, 200) as $potong) {
            Pembayaran::query()->insert($potong);
        }
        $siti = $siswa->firstWhere('nis', '20240011');
        Keringanan::query()->create(['siswa_id' => $siti->id, 'tagihan_id' => $tagihan['seragam']->id, 'jumlah' => 50000, 'alasan' => 'Beasiswa prestasi', 'petugas_id' => $bendahara]);
        if ($janji) {
            JanjiBayar::query()->create(['siswa_id' => $janji, 'tanggal' => '2026-11-18', 'catatan' => 'Menunggu gaji orang tua', 'petugas_id' => $bendahara]);
        }
        foreach ($ajukan as $orang) {
            Pengajuan::query()->create([
                'siswa_id' => $orang->id,
                'tagihan_id' => $tagihan['spp']->id,
                'jumlah' => 150000,
                'tanggal' => '2026-11-11',
                'metode' => 'transfer',
                'bukti_path' => 'bukti/contoh.jpg',
                'status' => 'menunggu',
            ]);
        }
        if ($dispensasi) {
            Dispensasi::query()->create(['siswa_id' => $dispensasi, 'sampai' => '2026-11-30', 'alasan' => 'Surat keterangan orang tua', 'petugas_id' => $bendahara]);
        }
        Pengumuman::query()->create(['judul' => 'Cara transfer', 'isi' => 'Cantumkan kode bayar TU-NIS pada berita transfer.', 'tanggal' => '2026-11-11', 'petugas_id' => $bendahara, 'aktif' => true]);
    }

    private function bayar(int $siswaId, int $tagihanId, int $jumlah, int $petugas, int $nomer): array
    {
        return [
            'siswa_id' => $siswaId,
            'tagihan_id' => $tagihanId,
            'jumlah' => $jumlah,
            'tanggal' => '2026-11-02',
            'nomor' => 'KWT-20261102-'.str_pad((string) $nomer, 4, '0', STR_PAD_LEFT),
            'metode' => $nomer % 3 === 0 ? 'transfer' : 'tunai',
            'asal' => 'loket',
            'petugas_id' => $petugas,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
