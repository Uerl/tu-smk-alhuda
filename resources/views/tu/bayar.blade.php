@extends('layouts.app', ['judul' => 'Pembayaran'])
@section('isi')
@php use App\Tu\Hitung; use App\Tu\Tampil; @endphp
<section class="kartu">
    <h2>Bukti menunggu</h2>
    @forelse($pengajuan as $item)
        <article class="baris" style="padding:8px 0;border-top:1px solid var(--line)">
            <div><strong>{{ $item->siswa->nama }}</strong><p class="redup">{{ $item->tagihan->nama }} · {{ Tampil::rupiah($item->jumlah) }} · {{ $item->metode }} · <a href="{{ route('bukti', $item) }}">Lihat bukti</a></p></div>
            <form method="post" action="{{ route('tu.pengajuan', $item) }}" class="baris">
                @csrf
                <input name="alasan" placeholder="Alasan jika ditolak">
                <button class="tombol utama" name="status" value="diterima">Terima</button>
                <button class="tombol" name="status" value="ditolak">Tolak</button>
            </form>
        </article>
    @empty
        <p class="redup">Tidak ada bukti yang menunggu.</p>
    @endforelse
</section>
<form class="kartu formulir" method="get">
    <label>Tingkat<select name="tingkat"><option value="">Semua</option>@foreach(['X','XI','XII'] as $t)<option @selected(request('tingkat')===$t)>{{ $t }}</option>@endforeach</select></label>
    <label>Rombel<select name="rombel"><option value="">Semua</option>@foreach($rombel as $kelas)<option value="{{ $kelas->id }}" @selected((string) request('rombel')===(string) $kelas->id)>{{ $kelas->nama }}</option>@endforeach</select></label>
    <label>BK<select name="bk"><option value="">Semua</option>@foreach($rombel->pluck('bk.nama')->filter()->unique() as $nama)<option @selected(request('bk')===$nama)>{{ $nama }}</option>@endforeach</select></label>
    <button class="tombol">Saring</button>
</form>
<form class="kartu formulir" method="post" action="{{ route('tu.bayar.catat') }}">
    @csrf
    <h2>Catat di loket</h2>
    <label>Murid<select name="siswa_id" required>@foreach($semua as $murid)<option value="{{ $murid->id }}">{{ $murid->rombel->nama }} · {{ $murid->nama }}</option>@endforeach</select></label>
    <label>Tagihan<select name="tagihan_id" required>@foreach($tagihan as $item)<option value="{{ $item->id }}">{{ $item->nama }}</option>@endforeach</select></label>
    <label>Jumlah<input type="number" name="jumlah" min="1" required></label>
    <label>Tanggal<input type="date" name="tanggal" required value="{{ now()->toDateString() }}"></label>
    <label>Cara<select name="metode"><option value="tunai">Tunai</option><option value="transfer">Transfer</option></select></label>
    <button class="tombol utama">Catat pembayaran</button>
</form>
<input class="saring" data-saring="#tabel-bayar" placeholder="Cari nama, NIS, atau kelas">
<div class="tabel-bungkus">
    <table id="tabel-bayar">
        <thead><tr><th>Murid</th><th>Tagihan</th><th></th></tr></thead>
        <tbody>
        @foreach($siswa as $murid)
            <tr>
                <td><strong>{{ $murid->nama }}</strong><br><span class="redup">{{ $murid->nis }} · {{ $murid->rombel->nama }} · {{ Hitung::kode($murid->nis) }}</span></td>
                <td>
                    @foreach(Hitung::tagihanSiswa($murid) as $tagihanItem)
                        @php $beban = Hitung::beban($murid, $tagihanItem); @endphp
                        <p class="baris"><span>{{ $tagihanItem->nama }} · {{ Hitung::statusBayar($beban['harus'], $beban['masuk']) }} @if(Hitung::lewat($tagihanItem->tempo->toDateString(), $beban['sisa'])) <span class="lencana buruk">Lewat</span> @endif</span><strong>{{ Tampil::rupiah($beban['sisa']) }}</strong></p>
                    @endforeach
                    <details>
                        <summary>Koreksi dan keringanan</summary>
                        @foreach(Hitung::tagihanSiswa($murid) as $tagihanItem)
                            @php $beban = Hitung::beban($murid, $tagihanItem); @endphp
                            @if($beban['masuk'] > 0)
                                <form class="formulir" method="post" action="{{ route('tu.koreksi') }}">
                                    @csrf
                                    <input type="hidden" name="siswa_id" value="{{ $murid->id }}">
                                    <input type="hidden" name="tagihan_id" value="{{ $tagihanItem->id }}">
                                    <input name="nama" value="{{ $tagihanItem->nama }}">
                                    <input type="number" name="nominal" min="0" value="{{ $tagihanItem->nominal }}">
                                    @if($tagihanItem->sasaran === 'tingkat')
                                        <select name="tingkat"><option value="semua" @selected($tagihanItem->tingkat==='semua')>Semua</option>@foreach(['X','XI','XII'] as $t)<option @selected($tagihanItem->tingkat===$t)>{{ $t }}</option>@endforeach</select>
                                    @else
                                        <input type="hidden" name="tingkat" value="{{ $tagihanItem->tingkat }}">
                                    @endif
                                    <input type="number" name="jumlah" min="0" value="{{ $beban['masuk'] }}">
                                    <button class="tombol">Koreksi {{ $tagihanItem->nama }}</button>
                                </form>
                            @endif
                            <form class="baris" method="post" action="{{ route('tu.keringanan') }}">
                                @csrf
                                <input type="hidden" name="siswa_id" value="{{ $murid->id }}">
                                <input type="hidden" name="tagihan_id" value="{{ $tagihanItem->id }}">
                                <input type="number" name="jumlah" min="0" placeholder="Keringanan" value="{{ $beban['potong'] ?: '' }}">
                                <input name="alasan" placeholder="Alasan">
                                <button class="tombol">Simpan</button>
                            </form>
                        @endforeach
                    </details>
                </td>
                <td>
                    <a class="tombol" href="{{ route('cetak.kartu', $murid) }}">Kartu</a>
                    <a class="tombol" href="{{ route('cetak.gabungan', $murid) }}">Kwitansi</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
