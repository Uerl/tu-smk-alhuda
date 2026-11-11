@extends('layouts.app', ['judul' => 'Tunggakan'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<section class="kartu" style="margin-bottom:14px">
    <h2>Jatuh tempo 7 hari</h2>
    <div class="daftar">
        @forelse($segera as $item)
            <p>{{ $item['siswa']->nama }} · {{ $item['siswa']->rombel->nama }} · {{ $item['tagihan']->nama }} · {{ Tampil::rupiah($item['sisa']) }} · {{ Tampil::tanggal($item['tagihan']->tempo->toDateString()) }}</p>
        @empty
            <p class="redup">Tidak ada tagihan yang segera jatuh tempo.</p>
        @endforelse
    </div>
</section>
<input class="saring" data-saring="#tabel-tunggakan" placeholder="Cari nama, NIS, atau kelas">
<div class="tabel-bungkus">
    <table id="tabel-tunggakan">
        <thead><tr><th>Murid</th><th>Kelas</th><th>Sisa</th><th>Janji</th><th></th></tr></thead>
        <tbody>
        @foreach($baris as $item)
            <tr>
                <td><strong>{{ $item['siswa']->nama }}</strong><br><span class="redup">{{ $item['siswa']->nis }}</span></td>
                <td>{{ $item['siswa']->rombel->nama }}<br><span class="redup">{{ $item['siswa']->rombel->walikelas?->nama }}</span></td>
                <td>{{ Tampil::rupiah($item['sisa']) }}</td>
                <td>
                    @if($item['siswa']->janji)
                        {{ Tampil::tanggal($item['siswa']->janji->tanggal->toDateString()) }}<br><span class="redup">{{ $item['siswa']->janji->catatan }}</span>
                    @else
                        <span class="redup">Belum ada</span>
                    @endif
                    <form class="baris" method="post" action="{{ route('tu.janji') }}" style="margin-top:8px">
                        @csrf
                        <input type="hidden" name="siswa_id" value="{{ $item['siswa']->id }}">
                        <input type="date" name="tanggal" required value="{{ $item['siswa']->janji?->tanggal?->toDateString() ?? now()->toDateString() }}">
                        <input name="catatan" placeholder="Catatan" value="{{ $item['siswa']->janji->catatan ?? '' }}">
                        <button class="tombol utama">Simpan</button>
                    </form>
                </td>
                <td>
                    <a class="tombol" href="{{ route('cetak.kartu', $item['siswa']) }}">Kartu</a>
                    <a class="tombol" href="{{ route('cetak.surat', $item['siswa']) }}">Surat</a>
                    @if($item['siswa']->janji)
                        <form method="post" action="{{ route('tu.janji.hapus', $item['siswa']) }}">@csrf @method('DELETE')<button class="tombol">Hapus janji</button></form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
