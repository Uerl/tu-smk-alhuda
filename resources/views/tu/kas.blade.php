@extends('layouts.app', ['judul' => 'Kas'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<section class="kartu">
    <div class="baris">
        <h2>Kas</h2>
        <div class="baris"><strong>{{ Tampil::rupiah($total) }}</strong><a class="tombol" href="{{ route('tu.kas.csv') }}">Unduh</a></div>
    </div>
    <form class="formulir" method="get" style="grid-template-columns:1fr 1fr auto;margin-top:12px">
        <label>Dari tanggal<input type="date" name="mulai" value="{{ request('mulai') }}"></label>
        <label>Sampai tanggal<input type="date" name="akhir" value="{{ request('akhir') }}"></label>
        <button class="tombol">Saring</button>
    </form>
    <div class="daftar" style="margin-top:12px">
        @forelse($daftar as $item)
            <div class="baris">
                <div><strong>{{ $item->siswa->nama }} · {{ $item->tagihan->nama }}</strong><p class="redup">{{ $item->nomor }} · {{ $item->metode }} · {{ Tampil::tanggal($item->tanggal->toDateString()) }} · {{ Tampil::rupiah($item->jumlah) }}</p></div>
                <a class="tombol" href="{{ route('cetak.kwitansi', $item) }}">Kwitansi</a>
            </div>
        @empty
            <p class="redup">Tidak ada pembayaran pada tanggal ini.</p>
        @endforelse
    </div>
</section>
<form class="kartu formulir" method="post" action="{{ route('tu.kas.tutup') }}" style="margin-top:12px">
    @csrf
    <h2>Tutup kas</h2>
    <p class="redup">Uang tunai tercatat {{ Tampil::rupiah($tunai) }} pada tanggal yang dipilih.</p>
    <label>Tanggal<input type="date" name="tanggal" value="{{ $tanggal }}" required onchange="location='{{ route('tu.kas') }}?tutup='+this.value"></label>
    <label>Uang dihitung<input type="number" name="hitung" min="0" required value="{{ $tunai }}"></label>
    <label>Catatan<input name="catatan" placeholder="Selisih atau keterangan"></label>
    <button class="tombol utama">Simpan tutup kas</button>
    @foreach($tutup as $item)
        <p class="redup">{{ Tampil::tanggal($item->tanggal->toDateString()) }} · dihitung {{ Tampil::rupiah($item->hitung) }} · selisih {{ Tampil::rupiah($item->selisih) }} {{ $item->catatan }}</p>
    @endforeach
</form>
<form class="kartu formulir" method="post" action="{{ route('tu.kas.setor') }}" style="margin-top:12px">
    @csrf
    <h2>Setor ke bank</h2>
    <p class="redup">Sisa uang tunai di laci {{ Tampil::rupiah($laci) }}.</p>
    <label>Tanggal<input type="date" name="tanggal" required value="{{ now()->toDateString() }}"></label>
    <label>Jumlah setor<input type="number" name="jumlah" min="1" required></label>
    <label>Berita<input name="berita" placeholder="Setoran SPP"></label>
    <button class="tombol utama">Catat setoran</button>
    @foreach($setor as $item)
        <p class="redup">{{ Tampil::tanggal($item->tanggal->toDateString()) }} · {{ Tampil::rupiah($item->jumlah) }} {{ $item->berita }}</p>
    @endforeach
</form>
@endsection
