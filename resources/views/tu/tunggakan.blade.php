@extends('layouts.app', ['judul' => 'Tunggakan'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<section class="kartu">
    <h2>Jatuh tempo 7 hari</h2>
    <div class="daftar">
        @forelse($segera as $item)
            <p>{{ $item['siswa']->nama }} · {{ $item['tagihan']->nama }} · {{ Tampil::rupiah($item['sisa']) }} · {{ Tampil::tanggal($item['tagihan']->tempo->toDateString()) }}</p>
        @empty
            <p class="redup">Tidak ada tagihan yang segera jatuh tempo.</p>
        @endforelse
    </div>
</section>
<section class="daftar" style="margin-top:12px">
    @foreach($baris as $item)
        <article class="kartu">
            <div class="baris">
                <div>
                    <strong>{{ $item['siswa']->nama }}</strong>
                    <p class="redup">{{ $item['siswa']->nis }} · {{ $item['siswa']->rombel->nama }} · {{ $item['siswa']->rombel->walikelas?->nama }}</p>
                    @if($item['siswa']->janji)
                        <p class="redup">Janji {{ Tampil::tanggal($item['siswa']->janji->tanggal->toDateString()) }} · {{ $item['siswa']->janji->catatan }}</p>
                    @endif
                </div>
                <strong>{{ Tampil::rupiah($item['sisa']) }}</strong>
            </div>
            <div class="baris" style="margin-top:8px">
                <a class="tombol" href="{{ route('cetak.kartu', $item['siswa']) }}">Kartu</a>
                <a class="tombol" href="{{ route('cetak.surat', $item['siswa']) }}">Surat</a>
            </div>
            <form class="formulir" method="post" action="{{ route('tu.janji') }}" style="margin-top:8px;grid-template-columns:1fr 1fr auto">
                @csrf
                <input type="hidden" name="siswa_id" value="{{ $item['siswa']->id }}">
                <input type="date" name="tanggal" required value="{{ $item['siswa']->janji?->tanggal?->toDateString() ?? now()->toDateString() }}">
                <input name="catatan" placeholder="Catatan janji" value="{{ $item['siswa']->janji->catatan ?? '' }}">
                <button class="tombol utama">Simpan janji</button>
            </form>
            @if($item['siswa']->janji)
                <form method="post" action="{{ route('tu.janji.hapus', $item['siswa']) }}">@csrf @method('DELETE')<button class="tombol">Hapus janji</button></form>
            @endif
        </article>
    @endforeach
    @if($baris === [])<p class="redup">Tidak ada tunggakan.</p>@endif
</section>
@endsection
