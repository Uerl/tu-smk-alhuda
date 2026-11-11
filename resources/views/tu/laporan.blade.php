@extends('layouts.app', ['judul' => 'Laporan'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<div class="baris"><h2>Laporan walikelas</h2><a class="tombol utama" href="{{ route('tu.tunggakan.csv') }}">Unduh tunggakan</a></div>
<div class="daftar" style="margin-top:12px">
    @foreach($wali as $nama => $item)
        <article class="kartu"><strong>{{ $nama }}</strong><p class="redup">{{ implode(', ', array_keys($item['kelas'])) }} · {{ $item['siswa'] }} siswa</p><p>Sisa {{ Tampil::rupiah($item['sisa']) }} · {{ $item['lewat'] }} tagihan lewat tempo</p></article>
    @endforeach
</div>
<h2 style="margin-top:16px">Rekap jurusan</h2>
<div class="daftar">
    @foreach($jurusan as $nama => $item)
        <article class="kartu"><strong>{{ $nama }}</strong><p class="redup">{{ implode(', ', array_keys($item['kelas'])) }} · {{ $item['siswa'] }} siswa</p><p>Sisa {{ Tampil::rupiah($item['sisa']) }} · {{ $item['ditahan'] }} murid ditahan</p></article>
    @endforeach
</div>
<h2 style="margin-top:16px">Umur piutang</h2>
<section class="kisi">
    <article class="kartu"><p class="redup">Belum jatuh tempo</p><strong>{{ Tampil::rupiah($umur['belum']) }}</strong></article>
    <article class="kartu"><p class="redup">Lewat 1–7 hari</p><strong>{{ Tampil::rupiah($umur['seminggu']) }}</strong></article>
    <article class="kartu"><p class="redup">Lewat 8–30 hari</p><strong>{{ Tampil::rupiah($umur['sebulan']) }}</strong></article>
    <article class="kartu"><p class="redup">Lewat lebih 30 hari</p><strong>{{ Tampil::rupiah($umur['lama']) }}</strong></article>
</section>
@endsection
