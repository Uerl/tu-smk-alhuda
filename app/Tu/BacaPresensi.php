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
        if (isset($rekam['nis'], $rekam['nama']) && empty($rekam['nip'])) {
            $tingkat = trim($rekam['kelas'] ?? '');
            $rombel = trim($rekam['rombel'] ?? '');
            if ($rombel === '' && ! preg_match('/^(XII|XI|X)$/i', $tingkat)) {
                $rombel = $tingkat;
                $tingkat = '';
            }
            if (self::riwayat($tingkat) || self::riwayat($rombel)) {
                return;
            }
            $kelas = self::kelas($rombel, $tingkat, $rekam['jurusan'] ?? '');
            $paket['siswa'][$rekam['nis']] = [
                'nis' => $rekam['nis'],
                'nama' => $rekam['nama'],
                'rombel' => $kelas['nama'],
            ];
            $paket['rombel'][$kelas['nama']] = array_merge($paket['rombel'][$kelas['nama']] ?? [], $kelas);

            return;
        }
        if (isset($rekam['nama']) && ! isset($rekam['nis'])) {
            $nip = $rekam['nip'] ?? ('G-'.substr(md5($rekam['nama']), 0, 12));
            $paket['pegawai'][$nip] = [
                'nip' => mb_substr($nip, 0, 30),
                'nama' => $rekam['nama'],
                'bagian' => self::bagian($rekam['bagian'] ?? ''),
            ];
        }
        $namaKelas = $rekam['rombel'] ?? ($rekam['kelas'] ?? '');
        if ($namaKelas !== '' && (isset($rekam['walikelas']) || isset($rekam['bk'])) && ! self::riwayat($namaKelas)) {
            $tingkat = preg_match('/^(XII|XI|X)$/i', $namaKelas) ? $namaKelas : ($rekam['kelas'] ?? '');
            $rombel = preg_match('/^(XII|XI|X)$/i', $namaKelas) ? ($rekam['rombel'] ?? '') : $namaKelas;
            $kelas = self::kelas($rombel, $tingkat, $rekam['jurusan'] ?? '');
            $paket['rombel'][$kelas['nama']] = array_merge($paket['rombel'][$kelas['nama']] ?? [], $kelas, array_filter([
                'walikelas' => $rekam['walikelas'] ?? null,
                'bk' => $rekam['bk'] ?? null,
            ]));
        }
    }

    private static function riwayat(string $teks): bool
    {
        return str_contains($teks, '→') || str_contains($teks, '->');
    }

    private static function kunci(string $judul): ?string
    {
        $judul = mb_strtolower(trim($judul));
        if (preg_match('/naik|riwayat|lama|history|sebelum/', $judul)) {
            return null;
        }
        if (str_contains($judul, 'nisn') || $judul === 'nis' || str_contains($judul, 'no induk')) {
            return 'nis';
        }
        if (str_contains($judul, 'wali')) {
            return 'walikelas';
        }
        if ($judul === 'bk' || str_contains($judul, 'guru bk') || str_contains($judul, 'bimbingan')) {
            return 'bk';
        }
        if (str_contains($judul, 'rombel') || str_contains($judul, 'rombongan')) {
            return 'rombel';
        }
        if ($judul === 'kelas' || $judul === 'tingkat' || str_contains($judul, 'tingkat')) {
            return 'kelas';
        }
        if (str_contains($judul, 'jurusan') || str_contains($judul, 'kompetensi')) {
            return 'jurusan';
        }
        if (str_contains($judul, 'nip') || str_contains($judul, 'nuptk')) {
            return 'nip';
        }
        if (str_contains($judul, 'jabatan') || str_contains($judul, 'bagian') || $judul === 'status') {
            return 'bagian';
        }
        if (str_contains($judul, 'nama')) {
            return 'nama';
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

    public static function rekapAbsensi(string $html): array
    {
        $baris = [];
        $dom = new DOMDocument();
        $internal = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($internal);
        foreach ($dom->getElementsByTagName('table') as $table) {
            $tabel = [];
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
                    $tabel[] = $sel;
                }
            }
            if (count($tabel) < 2) {
                continue;
            }
            $kunci = array_map(fn ($judul) => self::kunciAbsen($judul), $tabel[0]);
            $angka = array_intersect($kunci, ['hadir', 'sakit', 'izin', 'alpa']);
            foreach (array_slice($tabel, 1) as $isi) {
                $rekam = [];
                foreach ($kunci as $indeks => $nama) {
                    if ($nama && isset($isi[$indeks]) && $isi[$indeks] !== '') {
                        $rekam[$nama] = $isi[$indeks];
                    }
                }
                if (empty($rekam['nis'])) {
                    continue;
                }
                $nis = $rekam['nis'];
                if ($angka !== []) {
                    $baris[$nis] = [
                        'hadir' => self::angka($rekam['hadir'] ?? '0'),
                        'sakit' => self::angka($rekam['sakit'] ?? '0'),
                        'izin' => self::angka($rekam['izin'] ?? '0'),
                        'alpa' => self::angka($rekam['alpa'] ?? '0'),
                    ];
                    continue;
                }
                $jenis = self::jenis($rekam['status'] ?? '');
                if (! $jenis) {
                    continue;
                }
                $baris[$nis] ??= ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                $baris[$nis][$jenis]++;
            }
        }

        return ['bulan' => self::bulan($html), 'baris' => $baris];
    }

    private static function kunciAbsen(string $judul): ?string
    {
        $judul = mb_strtolower(trim($judul));
        if (preg_match('/naik|riwayat|lama|sebelum/', $judul)) {
            return null;
        }
        if (str_contains($judul, 'nisn') || $judul === 'nis') {
            return 'nis';
        }
        if (str_contains($judul, 'hadir') || $judul === 'h') {
            return 'hadir';
        }
        if (str_contains($judul, 'sakit') || $judul === 's') {
            return 'sakit';
        }
        if (str_contains($judul, 'izin') || $judul === 'i') {
            return 'izin';
        }
        if (preg_match('/alpa|alfa|alpha/', $judul) || $judul === 'a') {
            return 'alpa';
        }
        if (str_contains($judul, 'tanggal') || $judul === 'tgl') {
            return 'tanggal';
        }
        if (str_contains($judul, 'keterangan') || $judul === 'status' || str_contains($judul, 'kehadiran')) {
            return 'status';
        }
        if (str_contains($judul, 'nama')) {
            return 'nama';
        }

        return null;
    }

    private static function jenis(string $status): ?string
    {
        $status = mb_strtolower(trim($status));
        if (preg_match('/alpa|alfa|alpha|^a$/', $status)) {
            return 'alpa';
        }
        if (str_contains($status, 'sakit') || $status === 's') {
            return 'sakit';
        }
        if (str_contains($status, 'izin') || $status === 'i') {
            return 'izin';
        }
        if (str_contains($status, 'hadir') || str_contains($status, 'masuk') || $status === 'h') {
            return 'hadir';
        }

        return null;
    }

    private static function angka(string $nilai): int
    {
        return (int) preg_replace('/\D/', '', $nilai);
    }

    private static function bulan(string $html): string
    {
        $nama = ['januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12];
        if (preg_match('/\b(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s+(20\d{2})\b/i', $html, $cocok)) {
            return sprintf('%04d-%02d', $cocok[2], $nama[strtolower($cocok[1])]);
        }
        if (preg_match('/\b(20\d{2})-(0[1-9]|1[0-2])\b/', $html, $cocok)) {
            return $cocok[1].'-'.$cocok[2];
        }

        return date('Y-m');
    }
}
