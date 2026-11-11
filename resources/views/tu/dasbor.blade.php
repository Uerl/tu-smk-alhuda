@extends('layouts.app', ['judul' => 'Dasbor'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<section class="kisi">
    <article class="kartu stat"><p class="redup">Total tagihan</p><p class="angka">{{ Tampil::rupiah($harus) }}</p></article>
    <article class="kartu stat"><p class="redup">Terkumpul</p><p class="angka">{{ Tampil::rupiah($masuk) }}</p></article>
    <article class="kartu stat"><p class="redup">Sisa</p><p class="angka">{{ Tampil::rupiah($sisa) }}</p></article>
    <article class="kartu stat"><p class="redup">Bukti menunggu</p><p class="angka">{{ $menunggu }}</p></article>
    <article class="kartu stat"><p class="redup">Lewat tempo</p><p class="angka">{{ $lewat }}</p></article>
</section>
<section class="kartu">
    <h2>Progres jurusan</h2>
    @forelse($jurusan as $nama => $angka)
        @php $persen = $angka['harus'] > 0 ? round($angka['masuk'] / $angka['harus'] * 100) : 0; @endphp
        <div class="baris"><strong>{{ $nama }}</strong><span class="redup">{{ $angka['murid'] }} murid · {{ $persen }}%</span></div>
        <div class="progres"><span style="width:{{ $persen }}%"></span></div>
    @empty
        <p class="redup">Belum ada jurusan.</p>
    @endforelse
</section>
@endsection
