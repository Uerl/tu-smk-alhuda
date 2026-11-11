<?php

namespace App\Tu;

use App\Models\Pegawai;
use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
}
