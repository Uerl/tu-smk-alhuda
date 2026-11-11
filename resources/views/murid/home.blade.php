@extends('layouts.app', ['judul' => 'Halaman murid'])
@section('isi')
@php use App\Tu\Hitung; use App\Tu\Tampil; @endphp
<section class="kartu">
    <h2>{{ $siswa->nama }}</h2>
    <p class="redup">{{ $siswa->nis }} · {{ $siswa->rombel->nama }} · Walikelas {{ $siswa->rombel->walikelas?->nama }} · BK {{ $siswa->rombel->bk?->nama }}</p>
    <p>Kode bayar <strong>{{ Hitung::kode($siswa->nis) }}</strong></p>
    <span class="lencana {{ $status['status'] === 'Ditahan' ? 'buruk' : ($status['status'] === 'Dispensasi' ? 'tunggu' : '') }}">{{ $status['status'] }}</span>
    @if($siswa->janji)<p>Janji bayar {{ Tampil::tanggal($siswa->janji->tanggal->toDateString()) }} · {{ $siswa->janji->catatan }}</p>@endif
</section>
@if($pengumuman)
    <section class="kartu" style="margin-top:12px"><p class="redup">Pengumuman · {{ Tampil::tanggal($pengumuman->tanggal->toDateString()) }}</p><h3>{{ $pengumuman->judul }}</h3><p>{{ $pengumuman->isi }}</p></section>
@endif
<div class="daftar" style="margin-top:12px">
    @foreach($tagihan as $item)
        @php $beban = Hitung::beban($siswa, $item); @endphp
        <article class="kartu">
            <div class="baris">
                <div><strong>{{ $item->nama }}</strong><p class="redup">Tempo {{ Tampil::tanggal($item->tempo->toDateString()) }} @if($beban['potong'] > 0) · keringanan {{ Tampil::rupiah($beban['potong']) }} @endif</p></div>
                <span class="lencana {{ Hitung::lewat($item->tempo->toDateString(), $beban['sisa']) ? 'buruk' : '' }}">{{ Hitung::statusBayar($beban['harus'], $beban['masuk']) }}</span>
            </div>
            <p>Sisa {{ Tampil::rupiah($beban['sisa']) }}</p>
            @if($beban['sisa'] > 0)
                <form class="formulir" method="post" action="{{ route('murid.ajukan') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tagihan_id" value="{{ $item->id }}">
                    <p class="redup">Transfer atau setor ke {{ $rekening->bank ?? '' }} {{ $rekening->norek ?? '' }} a.n. {{ $rekening->atas_nama ?? '' }}</p>
                    <label>Cara bayar<select name="metode"><option value="transfer">Transfer</option><option value="tunai">Tunai</option></select></label>
                    <label>Jumlah<input type="number" name="jumlah" min="1" max="{{ $beban['sisa'] }}" value="{{ $beban['sisa'] }}" required></label>
                    <label>Foto bukti<input type="file" name="bukti" accept="image/*" required></label>
                    <button class="tombol utama">Ajukan verifikasi</button>
                </form>
            @endif
        </article>
    @endforeach
</div>
<section class="kartu" style="margin-top:12px">
    <h3>Rekapitulasi</h3>
    @foreach($siswa->pengajuan as $item)
        <p>{{ $item->tagihan->nama }} · {{ Tampil::rupiah($item->jumlah) }} · {{ $item->status }} {{ $item->alasan }}</p>
    @endforeach
    @foreach($siswa->pembayaran as $item)
        <p class="baris"><span>{{ $item->tagihan->nama }} · {{ $item->nomor }} · {{ Tampil::tanggal($item->tanggal->toDateString()) }}</span><a class="tombol" href="{{ route('cetak.kwitansi', $item) }}">Kwitansi</a></p>
    @endforeach
</section>
@endsection
