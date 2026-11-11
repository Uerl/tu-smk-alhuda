<?php

namespace Database\Seeders;

use App\Models\JanjiBayar;
use App\Models\Keringanan;
use App\Models\Pegawai;
use App\Models\Pembayaran;
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
        foreach (SumberPresensi::siswa() as $murid) {
            $user = User::query()->create([
                'name' => $murid['nama'],
                'username' => $murid['nis'],
                'password' => Hash::make('Murid123'),
                'peran' => 'murid',
            ]);
            Siswa::query()->create([
                'user_id' => $user->id,
                'nis' => $murid['nis'],
                'nama' => $murid['nama'],
                'rombel_id' => $rombel[$murid['rombel']]->id,
            ]);
        }
        Rekening::query()->create(['bank' => 'Bank Jatim', 'norek' => '001234567890', 'atas_nama' => 'SMK Al Huda Kota Kediri', 'aktif' => true]);
        $siti = Siswa::query()->where('nis', '20240011')->first();
        $budi = Siswa::query()->where('nis', '20240018')->first();
        $citra = Siswa::query()->where('nis', '20230102')->first();
        $eka = Siswa::query()->where('nis', '20220007')->first();
        $tagihan = [
            'spp' => Tagihan::query()->create(['tahun_pelajaran_id' => $tahun->id, 'nama' => 'SPP November 2026', 'nominal' => 150000, 'tingkat' => 'semua', 'sasaran' => 'tingkat', 'tempo' => '2026-11-10']),
            'seragam' => Tagihan::query()->create(['tahun_pelajaran_id' => $tahun->id, 'nama' => 'Seragam', 'nominal' => 350000, 'tingkat' => 'X', 'sasaran' => 'tingkat', 'tempo' => '2026-12-01']),
            'praktik' => Tagihan::query()->create(['tahun_pelajaran_id' => $tahun->id, 'nama' => 'Praktik jurusan', 'nominal' => 200000, 'tingkat' => 'semua', 'sasaran' => 'tingkat', 'tempo' => '2026-11-20']),
            'ulang' => Tagihan::query()->create(['tahun_pelajaran_id' => $tahun->id, 'nama' => 'Daftar ulang', 'nominal' => 500000, 'tingkat' => 'X', 'sasaran' => 'tingkat', 'tempo' => '2026-11-05']),
            'tour' => Tagihan::query()->create(['tahun_pelajaran_id' => $tahun->id, 'nama' => 'Studi tour', 'nominal' => 100000, 'tingkat' => 'X', 'sasaran' => 'rombel', 'rombel_id' => $rombel['X TKJ 1']->id, 'tempo' => '2026-12-15']),
        ];
        $bendahara = $akun['19900202'];
        Pembayaran::query()->insert([
            ['siswa_id' => $siti->id, 'tagihan_id' => $tagihan['spp']->id, 'jumlah' => 150000, 'tanggal' => '2026-11-02', 'nomor' => 'KWT-20261102-001', 'metode' => 'tunai', 'asal' => 'loket', 'petugas_id' => $bendahara->id, 'created_at' => now(), 'updated_at' => now()],
            ['siswa_id' => $siti->id, 'tagihan_id' => $tagihan['seragam']->id, 'jumlah' => 200000, 'tanggal' => '2026-11-02', 'nomor' => 'KWT-20261102-002', 'metode' => 'tunai', 'asal' => 'loket', 'petugas_id' => $bendahara->id, 'created_at' => now(), 'updated_at' => now()],
            ['siswa_id' => $citra->id, 'tagihan_id' => $tagihan['spp']->id, 'jumlah' => 150000, 'tanggal' => '2026-11-03', 'nomor' => 'KWT-20261103-001', 'metode' => 'transfer', 'asal' => 'verifikasi', 'petugas_id' => $bendahara->id, 'created_at' => now(), 'updated_at' => now()],
            ['siswa_id' => $eka->id, 'tagihan_id' => $tagihan['spp']->id, 'jumlah' => 75000, 'tanggal' => '2026-11-04', 'nomor' => 'KWT-20261104-001', 'metode' => 'tunai', 'asal' => 'loket', 'petugas_id' => $bendahara->id, 'created_at' => now(), 'updated_at' => now()],
        ]);
        Keringanan::query()->create(['siswa_id' => $siti->id, 'tagihan_id' => $tagihan['seragam']->id, 'jumlah' => 50000, 'alasan' => 'Beasiswa prestasi', 'petugas_id' => $bendahara->id]);
        JanjiBayar::query()->create(['siswa_id' => $budi->id, 'tanggal' => '2026-11-18', 'catatan' => 'Menunggu gaji orang tua', 'petugas_id' => $bendahara->id]);
        Pengumuman::query()->create(['judul' => 'Cara transfer', 'isi' => 'Cantumkan kode bayar TU-NIS pada berita transfer.', 'tanggal' => '2026-11-11', 'petugas_id' => $bendahara->id, 'aktif' => true]);
    }
}
