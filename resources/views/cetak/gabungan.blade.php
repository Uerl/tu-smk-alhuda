@extends('layouts.app', ['judul' => 'Kwitansi gabungan'])
@section('isi')
@php use App\Tu\Tampil; use App\Tu\Hitung; @endphp
<div class="no-cetak baris"><button class="tombol utama" onclick="print()">Cetak</button><button class="tombol" onclick="history.back()">Tutup</button></div>
<article class="kartu" style="max-width:640px;margin:16px auto">
    <p style="text-align:center">SMK AL HUDA KOTA KEDIRI</p>
    <h2 style="text-align:center">Kwitansi gabungan</h2>
    <p>Telah diterima dari <strong>{{ $siswa->nama }}</strong></p>
    <p class="redup">{{ $siswa->nis }} · {{ $siswa->rombel->nama }} · {{ Hitung::kode($siswa->nis) }}</p>
    @foreach($siswa->pembayaran as $item)
        <p class="baris"><span>{{ $item->tagihan->nama }}<br><span class="redup">{{ $item->nomor }} · {{ Tampil::tanggal($item->tanggal->toDateString()) }}</span></span><strong>{{ Tampil::rupiah($item->jumlah) }}</strong></p>
    @endforeach
    <h2>{{ Tampil::rupiah((int) $siswa->pembayaran->sum('jumlah')) }}</h2>
</article>
@endsection
