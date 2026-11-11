@extends('layouts.app', ['judul' => 'Rombel'])
@section('isi')
<p class="redup">Isi rombel mengikuti tarikan admin. Staf tidak menambah kelas.</p>
<div class="daftar" style="margin-top:12px">
    @foreach($rombel as $kelas)
        <article class="kartu">
            <strong>{{ $kelas->nama }}</strong>
            <p class="redup">Walikelas {{ $kelas->walikelas?->nama }} · BK {{ $kelas->bk?->nama }} · {{ $kelas->siswa->count() }} siswa</p>
            @foreach($kelas->siswa as $murid)<p>{{ $murid->nis }} · {{ $murid->nama }}</p>@endforeach
        </article>
    @endforeach
</div>
@endsection
