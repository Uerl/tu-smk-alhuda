<?php

namespace App\Tu;

use DOMDocument;
use DOMElement;

class BacaPresensi
{
    public static function dariHtml(string $html): array
    {
        $paket = ['pegawai' => [], 'rombel' => [], 'siswa' => []];
        $dom = new DOMDocument();
        $internal = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($internal);
        foreach ($dom->getElementsByTagName('table') as $table) {
            $baris = [];
            foreach ($table->getElementsByTagName('tr') as $tr) {
                if (! $tr instanceof DOMElement) {
                    continue;
                }
                $sel = [];
                foreach ($tr->childNodes as $selSel) {
                    if (! $selSel instanceof DOMElement || ! in_array($selSel->nodeName, ['td', 'th'], true)) {
                        continue;
                    }
                    $sel[] = trim(preg_replace('/\s+/u', ' ', $selSel->textContent) ?? '');
                }
                if ($sel !== []) {
                    $baris[] = $sel;
                }
            }
            if (count($baris) < 2) {
                continue;
            }
            $kunci = array_map(fn ($judul) => self::kunci($judul), $baris[0]);
            foreach (array_slice($baris, 1) as $isi) {
                $rekam = [];
                foreach ($kunci as $indeks => $nama) {
                    if ($nama && isset($isi[$indeks]) && $isi[$indeks] !== '') {
                        $rekam[$nama] = $isi[$indeks];
                    }
                }
                self::masuk($paket, $rekam);
            }
        }

        return $paket;
    }

    public static function gabung(array $kiri, array $kanan): array
    {
        foreach (['pegawai', 'rombel', 'siswa'] as $jenis) {
            foreach ($kanan[$jenis] ?? [] as $kunci => $baris) {
                $kiri[$jenis][$kunci] = array_merge($kiri[$jenis][$kunci] ?? [], $baris);
            }
        }

        return $kiri;
    }

    private static function masuk(array &$paket, array $rekam): void
    {
        if (isset($rekam['nis'], $rekam['nama'])) {
            $kelas = self::kelas($rekam['kelas'] ?? '', $rekam['tingkat'] ?? '', $rekam['jurusan'] ?? '');
            $paket['siswa'][$rekam['nis']] = [
                'nis' => $rekam['nis'],
                'nama' => $rekam['nama'],
                'rombel' => $kelas['nama'],
            ];
            $paket['rombel'][$kelas['nama']] = array_merge($paket['rombel'][$kelas['nama']] ?? [], $kelas);
        }
        if (isset($rekam['nip'], $rekam['nama']) && ! isset($rekam['nis'])) {
            $paket['pegawai'][$rekam['nip']] = [
                'nip' => $rekam['nip'],
                'nama' => $rekam['nama'],
                'bagian' => self::bagian($rekam['bagian'] ?? ''),
            ];
        }
        if (isset($rekam['kelas']) && (isset($rekam['walikelas']) || isset($rekam['bk']))) {
            $kelas = self::kelas($rekam['kelas'], $rekam['tingkat'] ?? '', $rekam['jurusan'] ?? '');
            $paket['rombel'][$kelas['nama']] = array_merge($kelas, array_filter([
                'walikelas' => $rekam['walikelas'] ?? null,
                'bk' => $rekam['bk'] ?? null,
            ]));
        }
    }

    private static function kunci(string $judul): ?string
    {
        $judul = mb_strtolower($judul);
        foreach ([
            'nisn' => 'nis', 'nis' => 'nis', 'no induk' => 'nis',
            'nama siswa' => 'nama', 'nama murid' => 'nama', 'nama lengkap' => 'nama', 'nama guru' => 'nama', 'nama' => 'nama',
            'wali kelas' => 'walikelas', 'walikelas' => 'walikelas',
            'guru bk' => 'bk', 'pembimbing' => 'bk', 'bk' => 'bk',
            'rombongan' => 'kelas', 'rombel' => 'kelas', 'kelas' => 'kelas',
            'jurusan' => 'jurusan', 'tingkat' => 'tingkat',
            'nip' => 'nip', 'jabatan' => 'bagian', 'bagian' => 'bagian',
        ] as $cari => $kunci) {
            if (str_contains($judul, $cari)) {
                return $kunci;
            }
        }

        return null;
    }

    public static function kelas(string $nama, string $tingkat = '', string $jurusan = ''): array
    {
        $nama = trim($nama);
        if (preg_match('/^(X|XI|XII)\s*[-.]?\s*([A-Za-z]+)\s*[-.]?\s*(\d+)/u', $nama, $cocok)) {
            $tingkat = strtoupper($cocok[1]);
            $jurusan = strtoupper($cocok[2]);
            $nama = $tingkat.' '.$jurusan.' '.$cocok[3];
        }
        $tingkat = strtoupper($tingkat ?: 'X');
        $jurusan = strtoupper($jurusan ?: 'UMUM');
        if ($nama === '') {
            $nama = trim($tingkat.' '.$jurusan);
        }

        return ['nama' => $nama, 'tingkat' => $tingkat, 'jurusan' => $jurusan];
    }

    private static function bagian(string $jabatan): string
    {
        $jabatan = mb_strtolower($jabatan);
        if (str_contains($jabatan, 'bk') || str_contains($jabatan, 'bimbingan')) {
            return 'bk';
        }
        if (str_contains($jabatan, 'karyawan') || str_contains($jabatan, 'tu')) {
            return 'karyawan';
        }

        return 'guru';
    }
}
