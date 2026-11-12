<?php

namespace App\Tu;

use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SumberPresensi
{
    public static function jurusan(): array
    {
        return ['TKJ', 'TKR', 'TPM', 'TSM', 'TITL', 'PF'];
    }

    public static function rombel(): array
    {
        $guru = ['Siti Aminah', 'Budi Hartono', 'Rina Safitri', 'Sari Wulandari', 'Agus Wijaya', 'Lilis Handayani', 'Maya Putri', 'Joko Santoso'];
        $bk = ['Nur Aini', 'Dedi Pratama', 'Hendra Kusuma'];
        $hasil = [];
        $urut = 0;
        foreach (['X', 'XI', 'XII'] as $tingkat) {
            foreach (self::jurusan() as $jurusan) {
                foreach ([1, 2] as $nomor) {
                    $hasil[] = [
                        'nama' => $tingkat.' '.$jurusan.' '.$nomor,
                        'tingkat' => $tingkat,
                        'jurusan' => $jurusan,
                        'walikelas' => $guru[$urut % count($guru)],
                        'bk' => $bk[$urut % count($bk)],
                    ];
                    $urut++;
                }
            }
        }

        return $hasil;
    }

    public static function siswa(): array
    {
        $wanita = ['Siti', 'Ayu', 'Citra', 'Dewi', 'Eka', 'Farah', 'Gilang', 'Hana', 'Intan', 'Kirana', 'Laras', 'Maya', 'Nisa', 'Putri', 'Rani'];
        $pria = ['Budi', 'Andi', 'Cahyo', 'Dimas', 'Fajar', 'Galang', 'Hendra', 'Ilham', 'Joko', 'Rizky', 'Teguh', 'Wahyu', 'Yoga', 'Zaki', 'Arif'];
        $belakang = ['Rahmawati', 'Santoso', 'Saputra', 'Wijaya', 'Pratama', 'Kusuma', 'Handayani', 'Permata', 'Setiawan', 'Aulia', 'Mahendra', 'Lestari', 'Nugroho', 'Wulandari', 'Hartono'];
        $hasil = [];
        $terpakai = [];
        $hitung = ['X' => 10, 'XI' => 10, 'XII' => 10];
        foreach (self::rombel() as $indeks => $kelas) {
            $tahun = $kelas['tingkat'] === 'X' ? '2024' : ($kelas['tingkat'] === 'XI' ? '2023' : '2022');
            for ($n = 1; $n <= 15; $n++) {
                $hitung[$kelas['tingkat']]++;
                $nis = $tahun.str_pad((string) $hitung[$kelas['tingkat']], 4, '0', STR_PAD_LEFT);
                $perempuan = $n % 2 === 1;
                if ($nis === '20240011') {
                    $nama = 'Siti Rahmawati';
                } else {
                    $depan = $perempuan ? $wanita[($n + $indeks) % count($wanita)] : $pria[($n + $indeks) % count($pria)];
                    $nama = $depan.' '.$belakang[($n + $indeks * 2) % count($belakang)];
                    if (isset($terpakai[$nama])) {
                        $nama .= ' '.substr($nis, -3);
                    }
                }
                $terpakai[$nama] = true;
                $hasil[] = ['nis' => $nis, 'nama' => $nama, 'rombel' => $kelas['nama'], 'perempuan' => $perempuan || $nis === '20240011'];
            }
        }

        return $hasil;
    }

    public static function pegawai(): array
    {
        return [
            ['nip' => '19840611', 'nama' => 'Ahmad Fauzi', 'bagian' => 'guru'],
            ['nip' => '19851112', 'nama' => 'Budi Hartono', 'bagian' => 'guru'],
            ['nip' => '19900121', 'nama' => 'Rina Safitri', 'bagian' => 'guru'],
            ['nip' => '19880708', 'nama' => 'Sari Wulandari', 'bagian' => 'guru'],
            ['nip' => '19820919', 'nama' => 'Agus Wijaya', 'bagian' => 'guru'],
            ['nip' => '19911230', 'nama' => 'Lilis Handayani', 'bagian' => 'guru'],
            ['nip' => '19870314', 'nama' => 'Maya Putri', 'bagian' => 'guru'],
            ['nip' => '19791202', 'nama' => 'Joko Santoso', 'bagian' => 'guru'],
            ['nip' => '19920817', 'nama' => 'Siti Aminah', 'bagian' => 'guru'],
            ['nip' => '19860404', 'nama' => 'Nur Aini', 'bagian' => 'bk'],
            ['nip' => '19830909', 'nama' => 'Dedi Pratama', 'bagian' => 'bk'],
            ['nip' => '19810622', 'nama' => 'Hendra Kusuma', 'bagian' => 'bk'],
            ['nip' => '19880101', 'nama' => 'Sri Lestari', 'bagian' => 'karyawan'],
            ['nip' => '19900202', 'nama' => 'Bambang Nugroho', 'bagian' => 'karyawan'],
            ['nip' => '19950303', 'nama' => 'Dewi Kartika', 'bagian' => 'karyawan'],
        ];
    }

    public static function tarikPegawai(): int
    {
        $baru = 0;
        $urutan = (int) Pegawai::query()->max('urutan');
        foreach (self::pegawai() as $orang) {
            if (Pegawai::query()->where('nip', $orang['nip'])->exists()) {
                continue;
            }
            $urutan++;
            Pegawai::query()->create([...$orang, 'urutan' => $urutan]);
            $baru++;
        }

        return $baru;
    }

    public static function tarikMurid(): int
    {
        $tahun = TahunPelajaran::query()->where('aktif', true)->firstOrFail();
        foreach (self::rombel() as $kelas) {
            $wali = Pegawai::query()->where('nama', $kelas['walikelas'])->first();
            $bk = Pegawai::query()->where('nama', $kelas['bk'])->first();
            Rombel::query()->updateOrCreate(
                ['tahun_pelajaran_id' => $tahun->id, 'nama' => $kelas['nama']],
                ['tingkat' => $kelas['tingkat'], 'jurusan' => $kelas['jurusan'], 'walikelas_id' => $wali?->id, 'bk_id' => $bk?->id],
            );
        }
        $baru = 0;
        $sandi = Hash::make('Murid123');
        foreach (self::siswa() as $murid) {
            $rombel = Rombel::query()->where('tahun_pelajaran_id', $tahun->id)->where('nama', $murid['rombel'])->first();
            if (! $rombel) {
                continue;
            }
            $siswa = Siswa::query()->where('nis', $murid['nis'])->first();
            if ($siswa) {
                $siswa->update(['nama' => $murid['nama'], 'rombel_id' => $rombel->id]);
                $siswa->user?->update(['name' => $murid['nama']]);
                continue;
            }
            $user = User::query()->create([
                'name' => $murid['nama'],
                'username' => $murid['nis'],
                'password' => $sandi,
                'peran' => 'murid',
            ]);
            Siswa::query()->create(['user_id' => $user->id, 'nis' => $murid['nis'], 'nama' => $murid['nama'], 'rombel_id' => $rombel->id]);
            $baru++;
        }

        return $baru;
    }

    public static function kosongkanAkademik(): array
    {
        return DB::transaction(function () {
            $murid = User::query()->where('peran', 'murid')->pluck('id');
            $jumlahMurid = Siswa::query()->count();
            $jumlahRombel = Rombel::query()->count();
            Siswa::query()->delete();
            User::query()->whereIn('id', $murid)->delete();
            Rombel::query()->delete();
            $guru = Pegawai::query()->whereNull('jabatan_tu')->count();
            Pegawai::query()->whereNull('jabatan_tu')->delete();

            return ['murid' => $jumlahMurid, 'rombel' => $jumlahRombel, 'guru' => $guru];
        });
    }

    public static function sinkron(): string
    {
        $paket = KlienPresensi::ambil();

        return DB::transaction(function () use ($paket) {
            $tahun = TahunPelajaran::query()->where('aktif', true)->firstOrFail();
            $urutan = (int) Pegawai::query()->max('urutan');
            foreach ($paket['pegawai'] as $orang) {
                $ada = Pegawai::query()->where('nip', $orang['nip'])->first();
                if ($ada) {
                    $ada->update(['nama' => $orang['nama'], 'bagian' => $orang['bagian']]);
                    continue;
                }
                $urutan++;
                Pegawai::query()->create([...$orang, 'urutan' => $urutan]);
            }
            foreach ($paket['rombel'] as $kelas) {
                self::simpanRombel($tahun->id, $kelas);
            }
            $sandi = Hash::make('Murid123');
            $baru = 0;
            foreach ($paket['siswa'] as $murid) {
                $pecah = BacaPresensi::kelas($murid['rombel']);
                $rombel = Rombel::query()->where('tahun_pelajaran_id', $tahun->id)->where('nama', $pecah['nama'])->first();
                if (! $rombel) {
                    $rombel = self::simpanRombel($tahun->id, $pecah);
                }
                $siswa = Siswa::query()->where('nis', $murid['nis'])->first();
                if ($siswa) {
                    $siswa->update(['nama' => $murid['nama'], 'rombel_id' => $rombel->id]);
                    $siswa->user?->update(['name' => $murid['nama']]);
                    continue;
                }
                $user = User::query()->create([
                    'name' => $murid['nama'],
                    'username' => $murid['nis'],
                    'password' => $sandi,
                    'peran' => 'murid',
                ]);
                Siswa::query()->create(['user_id' => $user->id, 'nis' => $murid['nis'], 'nama' => $murid['nama'], 'rombel_id' => $rombel->id]);
                $baru++;
            }
            $nis = array_keys($paket['siswa']);
            $lama = Siswa::query()->whereNotIn('nis', $nis)->pluck('user_id');
            Siswa::query()->whereNotIn('nis', $nis)->delete();
            User::query()->whereIn('id', $lama)->delete();
            $nama = Rombel::query()->where('tahun_pelajaran_id', $tahun->id)->pluck('nama', 'id');
            foreach ($nama as $id => $label) {
                if (! isset($paket['rombel'][$label]) && ! in_array($label, array_column($paket['siswa'], 'rombel'), true) && Siswa::query()->where('rombel_id', $id)->doesntExist()) {
                    Rombel::query()->whereKey($id)->delete();
                }
            }
            if ($paket['pegawai'] !== []) {
                Pegawai::query()->whereNull('jabatan_tu')->whereNotIn('nip', array_keys($paket['pegawai']))->delete();
            }

            return 'Sinkron selesai. '.$baru.' murid baru, '.count($paket['siswa']).' murid, '.Rombel::query()->count().' rombel.';
        });
    }

    private static function simpanRombel(int $tahunId, array $kelas): Rombel
    {
        $wali = isset($kelas['walikelas']) ? Pegawai::query()->where('nama', $kelas['walikelas'])->first() : null;
        $bk = isset($kelas['bk']) ? Pegawai::query()->where('nama', $kelas['bk'])->first() : null;
        $data = ['tingkat' => $kelas['tingkat'], 'jurusan' => $kelas['jurusan']];
        if ($wali) {
            $data['walikelas_id'] = $wali->id;
        }
        if ($bk) {
            $data['bk_id'] = $bk->id;
        }

        return Rombel::query()->updateOrCreate(
            ['tahun_pelajaran_id' => $tahunId, 'nama' => $kelas['nama']],
            $data,
        );
    }

    public static function sinkronAbsensi(): string
    {
        $paket = KlienPresensi::absensi();
        $siswa = Siswa::query()->whereIn('nis', array_keys($paket['baris']))->pluck('id', 'nis');
        $tersimpan = 0;
        foreach ($paket['baris'] as $nis => $angka) {
            if (! isset($siswa[$nis])) {
                continue;
            }
            Absensi::query()->updateOrCreate(
                ['siswa_id' => $siswa[$nis], 'bulan' => $paket['bulan']],
                $angka,
            );
            $tersimpan++;
        }
        if ($tersimpan === 0) {
            throw new RuntimeException('Absensi terbaca, tetapi NISN-nya belum ada di Tata Usaha. Tarik murid dulu.');
        }

        return 'Absensi '.$paket['bulan'].' tersimpan untuk '.$tersimpan.' murid.';
    }
}
