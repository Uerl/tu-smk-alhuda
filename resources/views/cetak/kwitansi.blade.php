@extends('layouts.app', ['judul' => 'Kwitansi'])
@section('isi')
@php use App\Tu\Tampil; use App\Tu\Hitung; @endphp
<div class="no-cetak baris"><button class="tombol utama" onclick="print()">Cetak</button><button class="tombol" onclick="history.back()">Tutup</button></div>
<article class="kartu" style="max-width:640px;margin:16px auto">
    <p style="text-align:center">SMK AL HUDA KOTA KEDIRI</p>
    <h2 style="text-align:center">Kwitansi</h2>
    <p>{{ $bayar->nomor }}</p>
    <p>Telah diterima dari <strong>{{ $bayar->siswa->nama }}</strong></p>
    <p class="redup">{{ $bayar->siswa->nis }} · {{ $bayar->siswa->rombel->nama }} · {{ Hitung::kode($bayar->siswa->nis) }}</p>
    <p>Untuk pembayaran {{ $bayar->tagihan->nama }}</p>
    <p>{{ Tampil::tanggal($bayar->tanggal->toDateString()) }} · {{ $bayar->metode }}</p>
    <h2>{{ Tampil::rupiah($bayar->jumlah) }}</h2>
    <p style="text-align:center;margin-top:48px">Diterima oleh {{ $bayar->petugas->name ?? 'Bendahara TU' }}<br><br>________________</p>
</article>
@endsection
