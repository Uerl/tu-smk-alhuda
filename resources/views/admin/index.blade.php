@extends('layouts.app', ['judul' => 'Admin'])
@section('isi')
<section class="kartu">
    <h2>Data dari presensi</h2>
    <p class="redup">Pertama guru dan karyawan, kemudian murid: NISN, kelas, dan rombel. Sumber {{ config('presensi.url') }}. Akun tata usaha, rekening, dan tagihan tidak dihapus.</p>
    <div class="baris">
        <form method="post" action="{{ route('admin.kosongkan') }}">@csrf<button class="tombol">Kosongkan data contoh</button></form>
        <form method="post" action="{{ route('admin.sinkron') }}">@csrf<button class="tombol utama">Sinkron dari presensi</button></form>
    </div>
</section>
<section class="tabel-bungkus" style="margin-top:14px">
    <table>
        <thead><tr><th>Pegawai</th><th>Jabatan TU</th><th>Urutan</th></tr></thead>
        <tbody>
        @foreach($pegawai as $orang)
            <tr>
                <td><strong>{{ $orang->nama }}</strong><br><span class="redup">{{ $orang->nip }} · {{ $orang->bagian }}</span></td>
                <td>
                    <form method="post" action="{{ route('admin.jabatan', $orang) }}">@csrf
                        <select name="jabatan_tu" onchange="this.form.submit()">
                            <option value="">Bukan pengurus TU</option>
                            @foreach(['kepala_tu'=>'Kepala TU','bendahara'=>'Bendahara','staf_tu'=>'Staf TU'] as $kunci => $label)
                                <option value="{{ $kunci }}" @selected($orang->jabatan_tu===$kunci)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </td>
                <td class="baris">
                    <form method="post" action="{{ route('admin.urutan', $orang) }}">@csrf<button class="tombol" name="arah" value="naik">Naik</button></form>
                    <form method="post" action="{{ route('admin.urutan', $orang) }}">@csrf<button class="tombol" name="arah" value="turun">Turun</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</section>
<section class="tabel-bungkus" style="margin-top:14px">
    <table>
        <thead><tr><th>Rombel</th><th>Walikelas</th><th>BK</th><th></th></tr></thead>
        <tbody>
        @foreach($rombel as $kelas)
            <tr>
                <td><strong>{{ $kelas->nama }}</strong><br><span class="redup">{{ $kelas->jurusan }} · tingkat {{ $kelas->tingkat }}</span></td>
                <td><select name="walikelas_id" form="rombel-{{ $kelas->id }}">@foreach($guru as $orang)<option value="{{ $orang->id }}" @selected($kelas->walikelas_id===$orang->id)>{{ $orang->nama }}</option>@endforeach</select></td>
                <td><select name="bk_id" form="rombel-{{ $kelas->id }}">@foreach($guru as $orang)<option value="{{ $orang->id }}" @selected($kelas->bk_id===$orang->id)>{{ $orang->nama }}</option>@endforeach</select></td>
                <td><button class="tombol utama" form="rombel-{{ $kelas->id }}">Simpan</button></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</section>
@foreach($rombel as $kelas)
    <form id="rombel-{{ $kelas->id }}" method="post" action="{{ route('admin.rombel', $kelas) }}">@csrf</form>
@endforeach
<form class="kartu formulir" method="post" action="{{ route('admin.rekening') }}" style="margin-top:14px">
    @csrf
    <h2>Rekening sekolah</h2>
    <label>Bank<input name="bank" required value="{{ $rekening->bank ?? '' }}"></label>
    <label>Nomor rekening<input name="norek" required value="{{ $rekening->norek ?? '' }}"></label>
    <label>Atas nama<input name="atas_nama" required value="{{ $rekening->atas_nama ?? '' }}"></label>
    <button class="tombol utama">Simpan rekening</button>
</form>
@endsection
