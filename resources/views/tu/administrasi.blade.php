@extends('layouts.app', ['judul' => 'Administrasi'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<section class="kartu">
    <h2>Data murid</h2>
    <input class="saring" data-saring="#tabel-murid" placeholder="Cari NISN, nama, atau kelas">
</section>
<div class="tabel-bungkus">
    <table id="tabel-murid">
        <thead><tr><th>NISN</th><th>Nama</th><th>Kelas</th><th>Total pernah dibayar</th><th>Dispensasi</th></tr></thead>
        <tbody>
        @forelse($baris as $item)
            @php $status = $item['status']['status']; @endphp
            <tr>
                <td>{{ $item['siswa']->nis }}</td>
                <td><strong>{{ $item['siswa']->nama }}</strong></td>
                <td>{{ $item['siswa']->rombel->nama }}</td>
                <td>{{ Tampil::rupiah($item['terbayar']) }}</td>
                <td>
                    <span class="lencana {{ $status === 'Ditahan' ? 'buruk' : ($status === 'Dispensasi' ? 'tunggu' : '') }}">{{ $status }}</span>
                    @if($status === 'Dispensasi')
                        <p class="redup">Sampai {{ Tampil::tanggal($item['status']['sampai']) }} · {{ $item['status']['alasan'] }}</p>
                        <form method="post" action="{{ route('tu.dispensasi.cabut', $item['siswa']) }}">@csrf @method('DELETE')<button class="tombol">Cabut</button></form>
                    @else
                        <form class="baris" method="post" action="{{ route('tu.dispensasi') }}">
                            @csrf
                            <input type="hidden" name="siswa_id" value="{{ $item['siswa']->id }}">
                            <input type="date" name="sampai" required value="{{ now()->addDays(14)->toDateString() }}">
                            <input name="alasan" placeholder="Alasan">
                            <button class="tombol">Beri</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5">Belum ada murid.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<section class="kartu" style="margin-top:14px">
    <h2>Data jurusan</h2>
    <form class="formulir" method="post" action="{{ route('tu.jurusan.simpan') }}">
        @csrf
        <label>Jurusan<input name="nama" required maxlength="80" placeholder="Contoh TKJ"></label>
        <label>Deskripsi<input name="deskripsi" maxlength="500" placeholder="Keterangan singkat"></label>
        <button class="tombol utama">Tambah</button>
    </form>
</section>
<div class="tabel-bungkus">
    <table>
        <thead><tr><th>Jurusan</th><th>Deskripsi</th><th></th></tr></thead>
        <tbody>
        @forelse($jurusan as $item)
            <tr>
                <td><strong>{{ $item->nama }}</strong></td>
                <td>{{ $item->deskripsi }}</td>
                <td>
                    <form method="post" action="{{ route('tu.jurusan.hapus', $item) }}">@csrf @method('DELETE')<button class="tombol">Hapus</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="3">Belum ada jurusan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<section class="kartu" style="margin-top:14px">
    <h2>Rombel</h2>
    <form class="formulir" method="post" action="{{ route('tu.rombel.simpan') }}">
        @csrf
        <label>Tingkat
            <select name="tingkat" required>
                <option value="X">X</option>
                <option value="XI">XI</option>
                <option value="XII">XII</option>
            </select>
        </label>
        <label>Rombel<input name="nama" required maxlength="50" placeholder="Contoh X TKJ 1"></label>
        <label>Jurusan
            <select name="jurusan" required>
                @foreach($jurusan as $item)
                    <option value="{{ $item->nama }}">{{ $item->nama }}</option>
                @endforeach
            </select>
        </label>
        <button class="tombol utama" @disabled($jurusan->isEmpty())>Tambah</button>
    </form>
    @if($jurusan->isEmpty())<p class="redup">Tambah jurusan dulu sebelum membuat rombel.</p>@endif
</section>
<div class="tabel-bungkus">
    <table>
        <thead><tr><th>Rombel</th><th>Jurusan</th><th></th></tr></thead>
        <tbody>
        @forelse($rombel as $kelas)
            <tr>
                <td><strong>{{ $kelas->nama }}</strong><br><span class="redup">tingkat {{ $kelas->tingkat }} · {{ $kelas->siswa_count }} murid</span></td>
                <td>{{ $kelas->jurusan }}</td>
                <td>
                    @if($kelas->siswa_count === 0)
                        <form method="post" action="{{ route('tu.rombel.hapus', $kelas) }}">@csrf @method('DELETE')<button class="tombol">Hapus</button></form>
                    @else
                        <span class="redup">Masih ada murid</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="3">Belum ada rombel.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<section class="kartu" style="margin-top:14px"><h2>Walikelas dan BK</h2></section>
<div class="tabel-bungkus">
    <table>
        <thead><tr><th>Rombel</th><th>Walikelas</th><th>BK</th><th></th></tr></thead>
        <tbody>
        @forelse($rombel as $kelas)
            <tr>
                <td><strong>{{ $kelas->nama }}</strong></td>
                <td>
                    <select name="walikelas_id" form="petugas-{{ $kelas->id }}">
                        <option value="">Belum ada</option>
                        @foreach($guru as $orang)
                            <option value="{{ $orang->id }}" @selected($kelas->walikelas_id === $orang->id)>{{ $orang->nama }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="bk_id" form="petugas-{{ $kelas->id }}">
                        <option value="">Belum ada</option>
                        @foreach($guru as $orang)
                            <option value="{{ $orang->id }}" @selected($kelas->bk_id === $orang->id)>{{ $orang->nama }}</option>
                        @endforeach
                    </select>
                </td>
                <td><button class="tombol utama" form="petugas-{{ $kelas->id }}" @disabled($guru->isEmpty())>Simpan</button></td>
            </tr>
        @empty
            <tr><td colspan="4">Belum ada rombel.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@foreach($rombel as $kelas)
    <form id="petugas-{{ $kelas->id }}" method="post" action="{{ route('tu.rombel.petugas', $kelas) }}">@csrf</form>
@endforeach
@endsection
