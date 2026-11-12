<?php

namespace Tests\Feature;

use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\User;
use App\Tu\BacaPresensi;
use App\Tu\Hitung;
use App\Tu\SumberPresensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AturanTuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_keringanan_mengurangi_sisa_seragam(): void
    {
        $siti = Siswa::query()->with(['rombel', 'keringanan', 'pembayaran'])->where('nis', '20240011')->first();
        $seragam = Hitung::tagihanSiswa($siti)->firstWhere('nama', 'Seragam');
        $beban = Hitung::beban($siti, $seragam);

        $this->assertSame(300000, $beban['harus']);
        $this->assertSame(100000, $beban['sisa']);
        $this->assertSame('Angsur', Hitung::statusBayar($beban['harus'], $beban['masuk']));
    }

    public function test_kewajiban_nol_berstatus_lunas(): void
    {
        $this->assertSame('Lunas', Hitung::statusBayar(0, 0));
    }

    public function test_tabel_presensi_terbaca_dan_data_contoh_bisa_dikosongkan(): void
    {
        $paket = BacaPresensi::dariHtml('<table><tr><th>NISN</th><th>Nama</th><th>Kelas</th><th>Rombel</th><th>Naik Kelas</th></tr><tr><td>0012345678</td><td>Ani</td><td>X</td><td>TITL-1</td><td>XI-PF → XII-PF</td></tr></table><table><tr><th>Nama</th><th>NIP</th><th>Bagian</th></tr><tr><td>Siti Aminah</td><td>19840611</td><td>Guru</td></tr></table>');
        $this->assertSame('Ani', $paket['siswa']['0012345678']['nama']);
        $this->assertSame('X TITL 1', $paket['siswa']['0012345678']['rombel']);
        $this->assertArrayNotHasKey('XI-PF → XII-PF', $paket['rombel']);
        $this->assertSame('guru', $paket['pegawai']['19840611']['bagian']);
        $panjang = BacaPresensi::kelas('TITL-1', 'X', 'Teknik Instalasi Tenaga Listrik');
        $this->assertSame('X TITL 1', $panjang['nama']);
        $this->assertSame('TITL', $panjang['jurusan']);
        $naik = BacaPresensi::kelas('XI-PF → XII-PF', 'XI → XII', '');
        $this->assertSame('XII PF', $naik['nama']);
        $this->assertSame('XII', $naik['tingkat']);
        $this->assertSame('PF', $naik['jurusan']);
        $rekap = BacaPresensi::rekapAbsensi('<h2>Rekap November 2026</h2><table><tr><th>NISN</th><th>Nama</th><th>Hadir</th><th>Sakit</th><th>Izin</th><th>Alpa</th></tr><tr><td>0012345678</td><td>Ani</td><td>18</td><td>1</td><td>0</td><td>2</td></tr></table><table><tr><th>NISN</th><th>Tanggal</th><th>Status</th></tr><tr><td>0099</td><td>2026-11-02</td><td>Hadir</td></tr><tr><td>0099</td><td>2026-11-03</td><td>Alpa</td></tr></table>');
        $this->assertSame('2026-11', $rekap['bulan']);
        $this->assertSame(18, $rekap['baris']['0012345678']['hadir']);
        $this->assertSame(2, $rekap['baris']['0012345678']['alpa']);
        $this->assertSame(1, $rekap['baris']['0099']['hadir']);
        $this->assertSame(1, $rekap['baris']['0099']['alpa']);

        SumberPresensi::kosongkanAkademik();
        $this->assertSame(0, Siswa::query()->count());
        $this->assertSame(0, Rombel::query()->count());
        $this->assertNotNull(User::query()->where('username', 'bendahara')->first());
    }

    public function test_pembayaran_tidak_boleh_melebihi_sisa(): void
    {
        $staf = User::query()->where('username', 'bendahara')->first();
        $siti = Siswa::query()->where('nis', '20240011')->first();
        $tagihan = Hitung::tagihanSiswa($siti->load(['rombel', 'keringanan', 'pembayaran']))->firstWhere('nama', 'Seragam');

        $this->actingAs($staf)->post('/tu/bayar', [
            'siswa_id' => $siti->id,
            'tagihan_id' => $tagihan->id,
            'jumlah' => 99999999,
            'tanggal' => '2026-11-11',
            'metode' => 'tunai',
        ])->assertSessionHas('galat');
    }

    public function test_murid_tidak_membuka_meja_staf(): void
    {
        $murid = User::query()->where('username', '20240011')->first();
        $this->actingAs($murid)->get('/tu')->assertForbidden();
        $this->actingAs($murid)->get('/murid')->assertOk();
    }
}
