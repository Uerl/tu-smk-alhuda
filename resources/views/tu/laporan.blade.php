@extends('layouts.app', ['judul' => 'Laporan'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<div class="baris">
    <h2>Umur piutang</h2>
    <a class="tombol utama" href="{{ route('tu.tunggakan.csv') }}">Unduh tunggakan</a>
</div>
<section class="kisi">
    <article class="kartu"><p class="redup">Belum jatuh tempo</p><p class="angka">{{ Tampil::rupiah($umur['belum']) }}</p></article>
    <article class="kartu"><p class="redup">Lewat 1–7 hari</p><p class="angka">{{ Tampil::rupiah($umur['seminggu']) }}</p></article>
    <article class="kartu"><p class="redup">Lewat 8–30 hari</p><p class="angka">{{ Tampil::rupiah($umur['sebulan']) }}</p></article>
    <article class="kartu"><p class="redup">Lewat lebih 30 hari</p><p class="angka">{{ Tampil::rupiah($umur['lama']) }}</p></article>
</section>
<div class="kisi-3">
    <section class="tabel-bungkus">
        <table>
            <thead><tr><th>Walikelas</th><th>Kelas</th><th>Murid</th><th>Sisa</th><th>Lewat</th></tr></thead>
            <tbody>
            @foreach($wali as $nama => $item)
                <tr>
                    <td><strong>{{ $nama }}</strong></td>
                    <td>{{ implode(', ', array_keys($item['kelas'])) }}</td>
                    <td>{{ $item['siswa'] }}</td>
                    <td>{{ Tampil::rupiah($item['sisa']) }}</td>
                    <td>{{ $item['lewat'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
    <section class="tabel-bungkus">
        <table>
            <thead><tr><th>Jurusan</th><th>Murid</th><th>Sisa</th><th>Ditahan</th></tr></thead>
            <tbody>
            @foreach($jurusan as $nama => $item)
                <tr>
                    <td><strong>{{ $nama }}</strong></td>
                    <td>{{ $item['siswa'] }}</td>
                    <td>{{ Tampil::rupiah($item['sisa']) }}</td>
                    <td>{{ $item['ditahan'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
</div>
@endsection
