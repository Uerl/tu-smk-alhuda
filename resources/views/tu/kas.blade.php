@extends('layouts.app', ['judul' => 'Kas'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<section class="kartu">
    <div class="baris">
        <div><h2>Kas harian</h2><p class="redup">Total pada saringan {{ Tampil::rupiah($total) }}</p></div>
        <a class="tombol" href="{{ route('tu.kas.csv') }}">Unduh CSV</a>
    </div>
    <form class="formulir" method="get">
        <label>Dari tanggal<input type="date" name="mulai" value="{{ request('mulai') }}"></label>
        <label>Sampai tanggal<input type="date" name="akhir" value="{{ request('akhir') }}"></label>
        <button class="tombol">Saring</button>
    </form>
</section>
<div class="kisi-3">
    <div class="tabel-bungkus">
        <table>
            <thead><tr><th>Murid</th><th>Tagihan</th><th>Jumlah</th><th></th></tr></thead>
            <tbody>
            @forelse($daftar as $item)
                <tr>
                    <td><strong>{{ $item->siswa->nama }}</strong><br><span class="redup">{{ $item->nomor }} · {{ Tampil::tanggal($item->tanggal->toDateString()) }}</span></td>
                    <td>{{ $item->tagihan->nama }}<br><span class="redup">{{ $item->metode }}</span></td>
                    <td>{{ Tampil::rupiah($item->jumlah) }}</td>
                    <td><a class="tombol" href="{{ route('cetak.kwitansi', $item) }}">Kwitansi</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="redup">Tidak ada pembayaran pada tanggal ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="daftar">
        <form class="kartu formulir" method="post" action="{{ route('tu.kas.tutup') }}">
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
        <form class="kartu formulir" method="post" action="{{ route('tu.kas.setor') }}">
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
    </div>
</div>
@endsection
