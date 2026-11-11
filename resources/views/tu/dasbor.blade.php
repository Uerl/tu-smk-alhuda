@extends('layouts.app', ['judul' => 'Tata Usaha · Pembayaran'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<section class="kisi">
    <article class="kartu"><p class="redup">Total tagihan</p><h2>{{ Tampil::rupiah($harus) }}</h2></article>
    <article class="kartu"><p class="redup">Terkumpul</p><h2>{{ Tampil::rupiah($masuk) }}</h2></article>
    <article class="kartu"><p class="redup">Sisa</p><h2>{{ Tampil::rupiah($sisa) }}</h2></article>
    <article class="kartu"><p class="redup">Bukti menunggu</p><h2>{{ $menunggu }}</h2></article>
    <article class="kartu"><p class="redup">Lewat tempo</p><h2>{{ $lewat }}</h2></article>
</section>
<section class="kartu" style="margin-top:12px">
    <h3>Progres tingkat</h3>
    @foreach($tingkat as $nama => $angka)
        @php $persen = $angka['harus'] > 0 ? round($angka['masuk'] / $angka['harus'] * 100) : 0; @endphp
        <p>{{ $nama }} · {{ $persen }}%</p>
        <div class="progres"><span style="width:{{ $persen }}%"></span></div>
    @endforeach
</section>
<section class="kartu" style="margin-top:12px">
    <h3>Pengumuman murid</h3>
    <form class="formulir" method="post" action="{{ route('tu.pengumuman') }}">
        @csrf
        <input name="judul" required value="{{ $pengumuman->judul ?? '' }}" placeholder="Judul">
        <textarea name="isi" required placeholder="Isi pengumuman">{{ $pengumuman->isi ?? '' }}</textarea>
        <button class="tombol utama">Simpan pengumuman</button>
    </form>
</section>
<form class="kartu baris" style="margin-top:12px" method="post" action="{{ route('tu.petugas') }}">
    @csrf
    <label>Petugas di kwitansi baru
        <select name="petugas_id" onchange="this.form.submit()">
            @foreach($pegawai->whereNotNull('user_id') as $orang)
                <option value="{{ $orang->user_id }}" @selected((int) $petugas === (int) $orang->user_id || (! $petugas && $orang->jabatan_tu === 'bendahara'))>{{ $orang->nama }}</option>
            @endforeach
        </select>
    </label>
</form>
@endsection
