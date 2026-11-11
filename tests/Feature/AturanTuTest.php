<?php

namespace Tests\Feature;

use App\Models\Siswa;
use App\Models\User;
use App\Tu\Hitung;
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
