@extends('layouts.app', ['judul' => 'Surat tunggakan'])
@section('isi')
@php use App\Tu\Tampil; use App\Tu\Hitung; $total = 0; @endphp
<div class="no-cetak baris">
    <button class="tombol utama" onclick="print()">Cetak</button>
    <button class="tombol" onclick="navigator.clipboard.writeText(document.getElementById('pesan').innerText)">Salin pesan</button>
    <button class="tombol" onclick="history.back()">Tutup</button>
</div>
<article class="kartu" id="pesan" style="max-width:640px;margin:16px auto">
    <p style="text-align:center">SMK AL HUDA KOTA KEDIRI</p>
    <h2 style="text-align:center">Pemberitahuan tunggakan</h2>
    <p>Yth. orang tua {{ $siswa->nama }}</p>
    <p class="redup">{{ $siswa->nis }} · {{ $siswa->rombel->nama }} · walikelas {{ $siswa->rombel->walikelas?->nama }}</p>
    @foreach($tagihan as $item)
        @php $sisa = Hitung::beban($siswa, $item)['sisa']; if ($sisa <= 0) continue; $total += $sisa; @endphp
        <p class="baris"><span>{{ $item->nama }} · tempo {{ Tampil::tanggal($item->tempo->toDateString()) }}</span><strong>{{ Tampil::rupiah($sisa) }}</strong></p>
    @endforeach
    <h3>Jumlah sisa {{ Tampil::rupiah($total) }}</h3>
    <p>Kode bayar {{ Hitung::kode($siswa->nis) }}</p>
    @if($rekening)<p>Transfer ke {{ $rekening->bank }} {{ $rekening->norek }} a.n. {{ $rekening->atas_nama }}</p>@endif
</article>
@endsection
