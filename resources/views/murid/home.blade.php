@extends('layouts.app', ['judul' => 'Halaman murid'])
@section('isi')
@php use App\Tu\Hitung; use App\Tu\Tampil; @endphp
@php
    $totalSisa = 0;
    foreach ($tagihan as $item) { $totalSisa += Hitung::beban($siswa, $item)['sisa']; }
@endphp
<section class="kartu">
    <div class="baris">
        <div>
            <p class="redup">{{ $siswa->rombel->nama }} · Walikelas {{ $siswa->rombel->walikelas?->nama }} · BK {{ $siswa->rombel->bk?->nama }}</p>
            <h2>{{ $siswa->nama }}</h2>
            <p class="redup">NIS {{ $siswa->nis }} · Kode bayar {{ Hitung::kode($siswa->nis) }}</p>
        </div>
        <span class="lencana {{ $status['status'] === 'Ditahan' ? 'buruk' : ($status['status'] === 'Dispensasi' ? 'tunggu' : '') }}">{{ $status['status'] }}</span>
    </div>
    <p>Sisa seluruh tagihan <strong>{{ Tampil::rupiah($totalSisa) }}</strong></p>
    <p class="redup">Transfer ke {{ $rekening->bank ?? '' }} {{ $rekening->norek ?? '' }} a.n. {{ $rekening->atas_nama ?? '' }}</p>
    @if($absensi)
        <p class="redup">Absensi bulan ini: hadir {{ $absensi->hadir }}, sakit {{ $absensi->sakit }}, izin {{ $absensi->izin }}, alpa {{ $absensi->alpa }}</p>
    @endif
    @if($siswa->janji)<p class="redup">Janji bayar {{ Tampil::tanggal($siswa->janji->tanggal->toDateString()) }} · {{ $siswa->janji->catatan }}</p>@endif
</section>
@if($pengumuman)
    <section class="kartu" style="margin-top:12px"><p class="redup">Pengumuman · {{ Tampil::tanggal($pengumuman->tanggal->toDateString()) }}</p><h3>{{ $pengumuman->judul }}</h3><p>{{ $pengumuman->isi }}</p></section>
@endif
<div class="daftar">
    @foreach($tagihan as $item)
        @php $beban = Hitung::beban($siswa, $item); $statusBayar = Hitung::statusBayar($beban['harus'], $beban['masuk']); @endphp
        <article class="kartu">
            <div class="baris">
                <div><strong>{{ $item->nama }}</strong><p class="redup">Tempo {{ Tampil::tanggal($item->tempo->toDateString()) }} · sisa {{ Tampil::rupiah($beban['sisa']) }}</p></div>
                <span class="lencana {{ $statusBayar === 'Belum' || Hitung::lewat($item->tempo->toDateString(), $beban['sisa']) ? 'buruk' : ($statusBayar === 'Angsur' ? 'tunggu' : '') }}">{{ $statusBayar }}</span>
            </div>
            @if($beban['sisa'] > 0)
                <details>
                    <summary>Ajukan bukti</summary>
                    <form class="formulir" method="post" action="{{ route('murid.ajukan') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="tagihan_id" value="{{ $item->id }}">
                        <label>Cara bayar<select name="metode"><option value="transfer">Transfer</option><option value="tunai">Tunai</option></select></label>
                        <label>Jumlah<input type="number" name="jumlah" min="1" max="{{ $beban['sisa'] }}" value="{{ $beban['sisa'] }}" required></label>
                        <label>Foto bukti<input type="file" name="bukti" accept="image/*" required></label>
                        <button class="tombol utama">Ajukan verifikasi</button>
                    </form>
                </details>
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
