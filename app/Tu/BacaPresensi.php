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
            $paket['rombel'][$kelas['nama']] = array_merge($paket['rombel'][$kelas['nama']] ?? [], $kelas, array_filter([
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
        $nama = self::sekarang($nama);
        $tingkat = self::sekarang($tingkat);
        $kode = self::kodeJurusan(self::sekarang($jurusan));
        if (preg_match('/^(XII|XI|X)\s*[-.]?\s*([A-Za-z]+)(?:\s*[-.]?\s*(\d+))?$/u', $nama, $cocok)) {
            $tingkat = $cocok[1];
            $kode = strtoupper($cocok[2]);
            $nama = $tingkat.' '.$kode.(isset($cocok[3]) && $cocok[3] !== '' ? ' '.$cocok[3] : '');
        } elseif (preg_match('/^([A-Za-z]+)\s*[-.]?\s*(\d+)$/u', $nama, $cocok)) {
            $kode = strtoupper($cocok[1]);
            if (in_array($tingkat, ['X', 'XI', 'XII'], true)) {
                $nama = $tingkat.' '.$kode.' '.$cocok[2];
            }
        }
        if (! in_array($tingkat, ['X', 'XI', 'XII'], true)) {
            $tingkat = 'X';
        }
        if ($kode === '') {
            $kode = 'UMUM';
        }
        if ($nama === '') {
            $nama = trim($tingkat.' '.$kode);
        }

        return [
            'nama' => mb_substr($nama, 0, 80),
            'tingkat' => $tingkat,
            'jurusan' => mb_substr($kode, 0, 20),
        ];
    }

    private static function sekarang(string $teks): string
    {
        $teks = trim(preg_replace('/\s*(?:→|->|=>|—|–|>)\s*/u', '->', $teks) ?? '');
        if (str_contains($teks, '->')) {
            $bagian = array_values(array_filter(array_map('trim', explode('->', $teks))));
            $teks = $bagian === [] ? '' : (string) end($bagian);
        }

        return strtoupper($teks);
    }

    private static function kodeJurusan(string $jurusan): string
    {
        $jurusan = strtoupper(trim($jurusan));
        $peta = [
            'TEKNIK INSTALASI TENAGA LISTRIK' => 'TITL',
            'TEKNIK KOMPUTER DAN JARINGAN' => 'TKJ',
            'TEKNIK KENDARAAN RINGAN' => 'TKR',
            'TEKNIK SEPEDA MOTOR' => 'TSM',
            'TEKNIK DAN BISNIS SEPEDA MOTOR' => 'TBSM',
            'TEKNIK PEMESINAN' => 'TPM',
            'TEKNIK PENGELASAN' => 'TPL',
            'PRODUKSI FILM' => 'PF',
            'DESAIN KOMUNIKASI VISUAL' => 'DKV',
            'AKUNTANSI DAN KEUANGAN LEMBAGA' => 'AKL',
            'PERHOTELAN' => 'PH',
        ];
        if (isset($peta[$jurusan])) {
            return $peta[$jurusan];
        }
        if (preg_match('/^[A-Z]{2,8}$/', $jurusan)) {
            return $jurusan;
        }
        if ($jurusan === '') {
            return '';
        }
        $inisial = '';
        foreach (preg_split('/\s+/', $jurusan) as $kata) {
            if (in_array($kata, ['DAN', 'DI', 'KE', 'YANG', 'TENAGA'], true)) {
                continue;
            }
            $inisial .= mb_substr($kata, 0, 1);
        }

        return $inisial !== '' ? $inisial : mb_substr($jurusan, 0, 20);
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
