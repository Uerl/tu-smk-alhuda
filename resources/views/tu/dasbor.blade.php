@extends('layouts.app', ['judul' => 'Dasbor pembayaran'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<section class="kisi">
    <article class="kartu"><p class="redup">Total tagihan</p><p class="angka">{{ Tampil::rupiah($harus) }}</p></article>
    <article class="kartu"><p class="redup">Terkumpul</p><p class="angka">{{ Tampil::rupiah($masuk) }}</p></article>
    <article class="kartu"><p class="redup">Sisa</p><p class="angka">{{ Tampil::rupiah($sisa) }}</p></article>
    <article class="kartu"><p class="redup">Bukti menunggu</p><p class="angka">{{ $menunggu }}</p></article>
    <article class="kartu"><p class="redup">Lewat tempo</p><p class="angka">{{ $lewat }}</p></article>
    <article class="kartu"><p class="redup">Jurusan</p><p class="angka">{{ count($jurusan) }}</p></article>
</section>
<section class="kisi-3" style="margin-top:14px">
    <article class="kartu">
        <h3>Progres tingkat</h3>
        @foreach($tingkat as $nama => $angka)
            @php $persen = $angka['harus'] > 0 ? round($angka['masuk'] / $angka['harus'] * 100) : 0; @endphp
            <div class="baris"><strong>{{ $nama }}</strong><span class="redup">{{ $persen }}%</span></div>
            <div class="progres"><span style="width:{{ $persen }}%"></span></div>
        @endforeach
        <h3 style="margin-top:8px">Progres jurusan</h3>
        @foreach($jurusan as $nama => $angka)
            @php $persen = $angka['harus'] > 0 ? round($angka['masuk'] / $angka['harus'] * 100) : 0; @endphp
            <div class="baris"><strong>{{ $nama }}</strong><span class="redup">{{ $angka['murid'] }} murid · {{ $persen }}%</span></div>
            <div class="progres"><span style="width:{{ $persen }}%"></span></div>
        @endforeach
    </article>
    <div class="daftar">
        <form class="kartu formulir" method="post" action="{{ route('tu.pengumuman') }}">
            @csrf
            <h3>Pengumuman murid</h3>
            <input name="judul" required value="{{ $pengumuman->judul ?? '' }}" placeholder="Judul">
            <textarea name="isi" required placeholder="Isi pengumuman">{{ $pengumuman->isi ?? '' }}</textarea>
            <button class="tombol utama">Simpan pengumuman</button>
        </form>
        <form class="kartu formulir" method="post" action="{{ route('tu.petugas') }}">
            @csrf
            <h3>Petugas kwitansi</h3>
            <select name="petugas_id" onchange="this.form.submit()">
                @foreach($pegawai->whereNotNull('user_id') as $orang)
                    <option value="{{ $orang->user_id }}" @selected((int) $petugas === (int) $orang->user_id || (! $petugas && $orang->jabatan_tu === 'bendahara'))>{{ $orang->nama }}</option>
                @endforeach
            </select>
        </form>
    </div>
</section>
@endsection
