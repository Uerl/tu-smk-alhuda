@extends('layouts.app', ['judul' => 'Admin TU'])
@section('isi')
<section class="kartu baris">
    <div>
        <h2>Tarik dari presensi</h2>
        <p class="redup">Jabatan TU yang sudah diatur tidak ditimpa. Pembayaran tidak dihapus. X TKJ 1 sengaja masih Ahmad Fauzi sampai ditarik ulang.</p>
    </div>
    <form method="post" action="{{ route('admin.pegawai') }}">@csrf<button class="tombol utama">Tarik pegawai</button></form>
    <form method="post" action="{{ route('admin.murid') }}">@csrf<button class="tombol">Tarik murid</button></form>
</section>
<section class="kartu" style="margin-top:12px">
    <h2>Pegawai TU</h2>
    @foreach($pegawai as $orang)
        <div class="baris" style="margin-top:8px">
            <div><strong>{{ $orang->nama }}</strong><p class="redup">{{ $orang->nip }} · {{ $orang->bagian }}</p></div>
            <form method="post" action="{{ route('admin.jabatan', $orang) }}">@csrf
                <select name="jabatan_tu" onchange="this.form.submit()">
                    <option value="">Bukan pengurus TU</option>
                    @foreach(['kepala_tu'=>'Kepala TU','bendahara'=>'Bendahara','staf_tu'=>'Staf TU'] as $kunci => $label)
                        <option value="{{ $kunci }}" @selected($orang->jabatan_tu===$kunci)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
            <form method="post" action="{{ route('admin.urutan', $orang) }}">@csrf<button class="tombol" name="arah" value="naik">Naik</button></form>
            <form method="post" action="{{ route('admin.urutan', $orang) }}">@csrf<button class="tombol" name="arah" value="turun">Turun</button></form>
        </div>
    @endforeach
</section>
<section class="daftar" style="margin-top:12px">
    @foreach($rombel as $kelas)
        <form class="kartu formulir" method="post" action="{{ route('admin.rombel', $kelas) }}" style="grid-template-columns:1fr 1fr 1fr auto">
            @csrf
            <strong>{{ $kelas->nama }}</strong>
            <label>Walikelas<select name="walikelas_id">@foreach($guru as $orang)<option value="{{ $orang->id }}" @selected($kelas->walikelas_id===$orang->id)>{{ $orang->nama }}</option>@endforeach</select></label>
            <label>BK<select name="bk_id">@foreach($guru as $orang)<option value="{{ $orang->id }}" @selected($kelas->bk_id===$orang->id)>{{ $orang->nama }}</option>@endforeach</select></label>
            <button class="tombol utama">Simpan</button>
        </form>
    @endforeach
</section>
<form class="kartu formulir" method="post" action="{{ route('admin.rekening') }}" style="margin-top:12px">
    @csrf
    <h2>Rekening sekolah</h2>
    <label>Bank<input name="bank" required value="{{ $rekening->bank ?? '' }}"></label>
    <label>Nomor rekening<input name="norek" required value="{{ $rekening->norek ?? '' }}"></label>
    <label>Atas nama<input name="atas_nama" required value="{{ $rekening->atas_nama ?? '' }}"></label>
    <button class="tombol utama">Simpan rekening</button>
</form>
@endsection
