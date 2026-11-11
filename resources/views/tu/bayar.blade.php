@extends('layouts.app', ['judul' => 'Kasir'])
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
<section class="kisi-3">
    <div class="kartu">
        <form method="get" action="{{ route('tu.bayar') }}">
            <label>Cari murid
                <input name="q" value="{{ $cari }}" placeholder="Nama atau NIS, minimal 2 huruf" autofocus>
            </label>
            <button class="tombol utama" style="margin-top:12px">Cari</button>
        </form>
        @if(mb_strlen($cari) < 2)
            <p class="redup">Ketik nama atau NIS. Daftar lengkap tidak ditampilkan.</p>
        @elseif($hasil->isEmpty())
            <p class="redup">Tidak ada murid yang cocok.</p>
        @else
            <div class="daftar" style="margin-top:12px">
                @foreach($hasil as $murid)
                    <a class="baris" href="{{ route('tu.bayar', ['q' => $cari, 'murid' => $murid->id]) }}">
                        <span><strong>{{ $murid->nama }}</strong><br><span class="redup">{{ $murid->nis }} · {{ $murid->rombel->nama }}</span></span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
    <aside class="kartu">
        @if($terpilih)
            @php $tagihanMurid = Hitung::tagihanSiswa($terpilih); @endphp
            <h2>{{ $terpilih->nama }}</h2>
            <p class="redup">{{ $terpilih->nis }} · {{ $terpilih->rombel->nama }} · {{ Hitung::kode($terpilih->nis) }}</p>
            @foreach($tagihanMurid as $item)
                @php $beban = Hitung::beban($terpilih, $item); @endphp
                <p class="baris"><span>{{ $item->nama }} · {{ Hitung::statusBayar($beban['harus'], $beban['masuk']) }}</span><strong>{{ Tampil::rupiah($beban['sisa']) }}</strong></p>
            @endforeach
            <form class="formulir" method="post" action="{{ route('tu.bayar.catat') }}" style="margin-top:12px">
                @csrf
                <input type="hidden" name="siswa_id" value="{{ $terpilih->id }}">
                <label>Tagihan
                    <select name="tagihan_id" required>
                        @foreach($tagihanMurid as $item)
                            <option value="{{ $item->id }}">{{ $item->nama }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Jumlah<input type="number" name="jumlah" min="1" required></label>
                <label>Tanggal<input type="date" name="tanggal" required value="{{ now()->toDateString() }}"></label>
                <label>Cara
                    <select name="metode"><option value="tunai">Tunai</option><option value="transfer">Transfer</option></select>
                </label>
                <button class="tombol utama">Catat pembayaran</button>
            </form>
            <details style="margin-top:12px">
                <summary>Koreksi dan keringanan</summary>
                @foreach($tagihanMurid as $item)
                    @php $beban = Hitung::beban($terpilih, $item); @endphp
                    @if($beban['masuk'] > 0)
                        <form class="formulir" method="post" action="{{ route('tu.koreksi') }}">
                            @csrf
                            <input type="hidden" name="siswa_id" value="{{ $terpilih->id }}">
                            <input type="hidden" name="tagihan_id" value="{{ $item->id }}">
                            <input name="nama" value="{{ $item->nama }}">
                            <input type="number" name="nominal" min="0" value="{{ $item->nominal }}">
                            @if($item->sasaran === 'tingkat')
                                <select name="tingkat"><option value="semua" @selected($item->tingkat==='semua')>Semua</option>@foreach(['X','XI','XII'] as $t)<option @selected($item->tingkat===$t)>{{ $t }}</option>@endforeach</select>
                            @else
                                <input type="hidden" name="tingkat" value="{{ $item->tingkat }}">
                            @endif
                            <input type="number" name="jumlah" min="0" value="{{ $beban['masuk'] }}">
                            <button class="tombol">Koreksi {{ $item->nama }}</button>
                        </form>
                    @endif
                    <form class="baris" method="post" action="{{ route('tu.keringanan') }}">
                        @csrf
                        <input type="hidden" name="siswa_id" value="{{ $terpilih->id }}">
                        <input type="hidden" name="tagihan_id" value="{{ $item->id }}">
                        <input type="number" name="jumlah" min="0" placeholder="Keringanan" value="{{ $beban['potong'] ?: '' }}">
                        <input name="alasan" placeholder="Alasan">
                        <button class="tombol">Simpan</button>
                    </form>
                @endforeach
            </details>
            <p class="baris" style="margin-top:12px">
                <a class="tombol" href="{{ route('cetak.kartu', $terpilih) }}">Kartu</a>
                <a class="tombol" href="{{ route('cetak.gabungan', $terpilih) }}">Kwitansi</a>
            </p>
        @else
            <p class="redup">Pilih satu murid dari hasil pencarian untuk mencatat pembayaran.</p>
        @endif
    </aside>
</section>
@endsection
