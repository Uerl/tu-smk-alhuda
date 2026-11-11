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
    public static function rombel(): array
    {
        return [
            ['nama' => 'X TKJ 1', 'tingkat' => 'X', 'jurusan' => 'TKJ', 'walikelas' => 'Siti Aminah', 'bk' => 'Nur Aini'],
            ['nama' => 'X TKR 1', 'tingkat' => 'X', 'jurusan' => 'TKR', 'walikelas' => 'Budi Hartono', 'bk' => 'Nur Aini'],
            ['nama' => 'X TITL 1', 'tingkat' => 'X', 'jurusan' => 'TITL', 'walikelas' => 'Rina Safitri', 'bk' => 'Nur Aini'],
            ['nama' => 'XI TKJ 1', 'tingkat' => 'XI', 'jurusan' => 'TKJ', 'walikelas' => 'Sari Wulandari', 'bk' => 'Dedi Pratama'],
            ['nama' => 'XI TSM 1', 'tingkat' => 'XI', 'jurusan' => 'TSM', 'walikelas' => 'Agus Wijaya', 'bk' => 'Dedi Pratama'],
            ['nama' => 'XI TKR 2', 'tingkat' => 'XI', 'jurusan' => 'TKR', 'walikelas' => 'Lilis Handayani', 'bk' => 'Dedi Pratama'],
            ['nama' => 'XII PF 1', 'tingkat' => 'XII', 'jurusan' => 'PF', 'walikelas' => 'Maya Putri', 'bk' => 'Hendra Kusuma'],
            ['nama' => 'XII TPM 1', 'tingkat' => 'XII', 'jurusan' => 'TPM', 'walikelas' => 'Joko Santoso', 'bk' => 'Hendra Kusuma'],
        ];
    }

    public static function siswa(): array
    {
        return [
            ['nis' => '20240011', 'nama' => 'Siti Rahmawati', 'rombel' => 'X TKJ 1'],
            ['nis' => '20240018', 'nama' => 'Budi Santoso', 'rombel' => 'X TKR 1'],
            ['nis' => '20240027', 'nama' => 'Gilang Wulandari', 'rombel' => 'X TITL 1'],
            ['nis' => '20230102', 'nama' => 'Citra Hartono', 'rombel' => 'XI TKJ 1'],
            ['nis' => '20230115', 'nama' => 'Dian Permata', 'rombel' => 'XI TSM 1'],
            ['nis' => '20230133', 'nama' => 'Hana Setiawan', 'rombel' => 'XI TKR 2'],
            ['nis' => '20220007', 'nama' => 'Eka Kusuma', 'rombel' => 'XII PF 1'],
            ['nis' => '20220021', 'nama' => 'Farah Pratama', 'rombel' => 'XII TPM 1'],
            ['nis' => '20240044', 'nama' => 'Indra Mahendra', 'rombel' => 'X TKJ 1'],
            ['nis' => '20230150', 'nama' => 'Kirana Aulia', 'rombel' => 'XI TKJ 1'],
        ];
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
                'password' => Hash::make('Murid123'),
                'peran' => 'murid',
            ]);
            Siswa::query()->create(['user_id' => $user->id, 'nis' => $murid['nis'], 'nama' => $murid['nama'], 'rombel_id' => $rombel->id]);
            $baru++;
        }

        return $baru;
    }
}
