@extends('layouts.app', ['judul' => 'Tagihan'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<form class="kartu formulir" method="get">
    <h2>Cari murid</h2>
    <p class="redup">Dipakai hanya jika sasarannya satu murid.</p>
    <label>Nama atau NIS<input name="q" value="{{ $cari }}" placeholder="Minimal 2 huruf"></label>
    <button class="tombol">Cari</button>
</form>
<form class="kartu formulir" method="post" action="{{ route('tu.tagihan.simpan') }}">
    @csrf
    <h2>Tagihan baru</h2>
    <label>Nama<input name="nama" required></label>
    <label>Nominal<input type="number" name="nominal" min="0" required></label>
    <label>Jatuh tempo<input type="date" name="tempo" required value="{{ now()->toDateString() }}"></label>
    <label>Sasaran
        <select name="sasaran" id="sasaran">
            <option value="tingkat">Satu tingkat atau semua</option>
            <option value="rombel">Satu rombel</option>
            <option value="siswa">Satu murid</option>
        </select>
    </label>
    <div data-untuk="tingkat">
        <label>Tingkat
            <select name="tingkat"><option value="semua">Semua</option><option>X</option><option>XI</option><option>XII</option></select>
        </label>
    </div>
    <div data-untuk="rombel">
        <label>Rombel
            <select name="rombel_id">@foreach($rombel as $kelas)<option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>@endforeach</select>
        </label>
    </div>
    <div data-untuk="siswa">
        <label>Murid
            <select name="siswa_id">
                <option value="">Pilih hasil pencarian</option>
                @foreach($calon as $murid)<option value="{{ $murid->id }}">{{ $murid->rombel->nama }} · {{ $murid->nama }}</option>@endforeach
            </select>
        </label>
    </div>
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
<script>
(function () {
    var pilih = document.getElementById('sasaran');
    if (!pilih) return;
    function atur() {
        document.querySelectorAll('[data-untuk]').forEach(function (el) {
            el.hidden = el.getAttribute('data-untuk') !== pilih.value;
        });
    }
    pilih.addEventListener('change', atur);
    atur();
})();
</script>
@endsection
