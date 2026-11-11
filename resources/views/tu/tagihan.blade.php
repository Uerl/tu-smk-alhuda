@extends('layouts.app', ['judul' => 'Tagihan'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<form class="kartu formulir" method="post" action="{{ route('tu.tagihan.simpan') }}">
    @csrf
    <h2>Tagihan baru</h2>
    <label>Nama<input name="nama" required></label>
    <label>Nominal<input type="number" name="nominal" min="0" required></label>
    <label>Jatuh tempo<input type="date" name="tempo" required value="{{ now()->toDateString() }}"></label>
    <label>Sasaran
        <select name="sasaran" id="sasaran">
            <option value="tingkat">Tingkat</option>
            <option value="rombel">Satu rombel</option>
            <option value="siswa">Satu murid</option>
        </select>
    </label>
    <label>Tingkat
        <select name="tingkat"><option value="semua">Semua</option><option>X</option><option>XI</option><option>XII</option></select>
    </label>
    <label>Rombel
        <select name="rombel_id">@foreach($rombel as $kelas)<option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>@endforeach</select>
    </label>
    <label>Murid
        <select name="siswa_id">@foreach($siswa as $murid)<option value="{{ $murid->id }}">{{ $murid->nama }}</option>@endforeach</select>
    </label>
    <button class="tombol utama">Simpan tagihan</button>
</form>
<div class="tabel-bungkus">
    <table>
        <thead><tr><th>Tagihan</th><th>Nominal</th><th>Tempo</th><th>Sasaran</th><th></th></tr></thead>
        <tbody>
        @foreach($tagihan as $item)
            <tr>
                <td><strong>{{ $item->nama }}</strong></td>
                <td>{{ Tampil::rupiah($item->nominal) }}</td>
                <td>{{ Tampil::tanggal($item->tempo->toDateString()) }}</td>
                <td>{{ $item->sasaran }} {{ $item->tingkat }} {{ $item->rombel->nama ?? '' }}</td>
                <td><form method="post" action="{{ route('tu.tagihan.bulan', $item) }}">@csrf<button class="tombol">Bulan depan</button></form></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
