@extends('layouts.app', ['judul' => 'Pembayaran'])
@section('isi')
@php use App\Tu\Hitung; use App\Tu\Tampil; @endphp
<section class="kartu">
    <h2>Bukti menunggu</h2>
    @forelse($pengajuan as $item)
        <article style="margin-top:10px">
            <strong>{{ $item->siswa->nama }}</strong> · {{ $item->tagihan->nama }} · {{ Tampil::rupiah($item->jumlah) }} · {{ $item->metode }}
            <p><a href="{{ route('bukti', $item) }}">Lihat bukti</a></p>
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
<form class="kartu formulir" method="get" style="margin-top:12px;grid-template-columns:repeat(4,1fr)">
    <label>Tingkat<select name="tingkat"><option value="">Semua</option>@foreach(['X','XI','XII'] as $t)<option @selected(request('tingkat')===$t)>{{ $t }}</option>@endforeach</select></label>
    <label>Rombel<select name="rombel"><option value="">Semua</option>@foreach($rombel as $kelas)<option value="{{ $kelas->id }}" @selected((string) request('rombel')===(string) $kelas->id)>{{ $kelas->nama }}</option>@endforeach</select></label>
    <label>BK<select name="bk"><option value="">Semua</option>@foreach($rombel->pluck('bk.nama')->filter()->unique() as $nama)<option @selected(request('bk')===$nama)>{{ $nama }}</option>@endforeach</select></label>
    <button class="tombol">Saring</button>
</form>
<form class="kartu formulir" method="post" action="{{ route('tu.bayar.catat') }}" style="margin-top:12px">
    @csrf
    <h2>Catat di loket</h2>
    <label>Murid<select name="siswa_id" required>@foreach($semua as $murid)<option value="{{ $murid->id }}">{{ $murid->nama }} · {{ $murid->rombel->nama }}</option>@endforeach</select></label>
    <label>Tagihan<select name="tagihan_id" required>@foreach($tagihan as $item)<option value="{{ $item->id }}">{{ $item->nama }}</option>@endforeach</select></label>
    <label>Jumlah<input type="number" name="jumlah" min="1" required></label>
    <label>Tanggal<input type="date" name="tanggal" required value="{{ now()->toDateString() }}"></label>
    <label>Cara<select name="metode"><option value="tunai">Tunai</option><option value="transfer">Transfer</option></select></label>
    <button class="tombol utama">Catat pembayaran</button>
</form>
<div class="daftar" style="margin-top:12px">
    @foreach($siswa as $murid)
        <article class="kartu">
            <div class="baris">
                <div><strong>{{ $murid->nama }}</strong><p class="redup">{{ $murid->nis }} · {{ $murid->rombel->nama }} · kode {{ Hitung::kode($murid->nis) }}</p></div>
                <div class="baris">
                    <a class="tombol" href="{{ route('cetak.kartu', $murid) }}">Kartu iuran</a>
                    <a class="tombol" href="{{ route('cetak.gabungan', $murid) }}">Kwitansi gabungan</a>
                </div>
            </div>
            @foreach(Hitung::tagihanSiswa($murid) as $tagihanItem)
                @php $beban = Hitung::beban($murid, $tagihanItem); @endphp
                <div class="baris" style="margin-top:8px">
                    <span>{{ $tagihanItem->nama }} · {{ Hitung::statusBayar($beban['harus'], $beban['masuk']) }} @if(Hitung::lewat($tagihanItem->tempo->toDateString(), $beban['sisa'])) · Lewat tempo @endif</span>
                    <span>Sisa {{ Tampil::rupiah($beban['sisa']) }}</span>
                </div>
                @if($beban['masuk'] > 0)
                    <form class="formulir" method="post" action="{{ route('tu.koreksi') }}" style="grid-template-columns:1.4fr .8fr .6fr .8fr auto">
                        @csrf
                        <input type="hidden" name="siswa_id" value="{{ $murid->id }}">
                        <input type="hidden" name="tagihan_id" value="{{ $tagihanItem->id }}">
                        <input name="nama" value="{{ $tagihanItem->nama }}">
                        <input type="number" name="nominal" min="0" value="{{ $tagihanItem->nominal }}">
                        @if($tagihanItem->sasaran === 'tingkat')
                            <select name="tingkat"><option value="semua" @selected($tagihanItem->tingkat==='semua')>Semua</option>@foreach(['X','XI','XII'] as $t)<option @selected($tagihanItem->tingkat===$t)>{{ $t }}</option>@endforeach</select>
                        @else
                            <input type="hidden" name="tingkat" value="{{ $tagihanItem->tingkat }}">
                            <span class="redup">{{ $tagihanItem->sasaran }}</span>
                        @endif
                        <input type="number" name="jumlah" min="0" value="{{ $beban['masuk'] }}">
                        <button class="tombol">Koreksi</button>
                    </form>
                @endif
                <form class="baris" method="post" action="{{ route('tu.keringanan') }}" style="margin-top:6px">
                    @csrf
                    <input type="hidden" name="siswa_id" value="{{ $murid->id }}">
                    <input type="hidden" name="tagihan_id" value="{{ $tagihanItem->id }}">
                    <input type="number" name="jumlah" min="0" placeholder="Keringanan" value="{{ $beban['potong'] ?: '' }}">
                    <input name="alasan" placeholder="Alasan keringanan">
                    <button class="tombol">Keringanan</button>
                </form>
            @endforeach
        </article>
    @endforeach
</div>
@endsection
