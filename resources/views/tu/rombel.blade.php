@extends('layouts.app', ['judul' => 'Rombel'])
@section('isi')
<p class="redup">Enam jurusan, dua rombel tiap tingkat. Walikelas dan BK diatur dari halaman admin.</p>
@foreach($rombel->groupBy('jurusan') as $nama => $daftar)
    <section class="grup">
        <h2>{{ $nama }}</h2>
        <div class="kisi-rombel">
            @foreach($daftar as $kelas)
                <article class="kartu">
                    <div class="baris">
                        <strong>{{ $kelas->nama }}</strong>
                        <span class="lencana">{{ $kelas->siswa->count() }} murid</span>
                    </div>
                    <p class="redup">Walikelas {{ $kelas->walikelas?->nama }}<br>BK {{ $kelas->bk?->nama }}</p>
                    <details>
                        <summary>Lihat nama</summary>
                        @foreach($kelas->siswa as $murid)<p>{{ $murid->nis }} · {{ $murid->nama }}</p>@endforeach
                    </details>
                </article>
            @endforeach
        </div>
    </section>
@endforeach
@endsection
