@extends('layouts.app', ['judul' => 'Kartu iuran'])
@section('isi')
@php use App\Tu\Tampil; use App\Tu\Hitung; @endphp
<div class="no-cetak baris"><button class="tombol utama" onclick="print()">Cetak</button><button class="tombol" onclick="history.back()">Tutup</button></div>
<article class="kartu" style="max-width:640px;margin:16px auto">
    <p style="text-align:center">SMK AL HUDA KOTA KEDIRI</p>
    <h2 style="text-align:center">Kartu iuran</h2>
    <p><strong>{{ $siswa->nama }}</strong> · {{ $siswa->nis }} · {{ $siswa->rombel->nama }}</p>
    <p class="redup">Kode {{ Hitung::kode($siswa->nis) }} · {{ Hitung::statusAdministrasi($siswa)['status'] }}</p>
    @php $total = 0; @endphp
    @foreach($tagihan as $item)
        @php $beban = Hitung::beban($siswa, $item); $total += $beban['sisa']; @endphp
        <p class="baris"><span>{{ $item->nama }}<br><span class="redup">Tempo {{ Tampil::tanggal($item->tempo->toDateString()) }}</span></span><strong>{{ Tampil::rupiah($beban['sisa']) }}</strong></p>
    @endforeach
    <h3>Jumlah sisa {{ Tampil::rupiah($total) }}</h3>
</article>
@endsection
