@extends('layouts.app', ['judul' => 'Absensi'])
@section('isi')
@php
    $namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    [$tahun, $angka] = array_pad(explode('-', $bulan), 2, '1');
    $judulBulan = ($namaBulan[(int) $angka] ?? $bulan).' '.$tahun;
@endphp
<section class="kartu baris">
    <div>
        <h2>Absensi {{ $judulBulan }}</h2>
        <p class="redup">Rekap dari aplikasi presensi. Pencatatan harian tetap di sana.</p>
    </div>
    <form method="get" action="{{ route('tu.absensi') }}" class="baris">
        <label>Bulan<input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()"></label>
        <label>Rombel
            <select name="rombel" onchange="this.form.submit()">
                <option value="0">Semua</option>
                @foreach($rombel as $kelas)
                    <option value="{{ $kelas->id }}" @selected($rombelId === $kelas->id)>{{ $kelas->nama }}</option>
                @endforeach
            </select>
        </label>
    </form>
</section>
<section class="tabel-bungkus">
    <table>
        <thead><tr><th>Murid</th><th>Hadir</th><th>Sakit</th><th>Izin</th><th>Alpa</th><th></th></tr></thead>
        <tbody>
        @forelse($absensi as $item)
            @php $total = $item->hadir + $item->sakit + $item->izin + $item->alpa; @endphp
            <tr>
                <td><strong>{{ $item->siswa->nama }}</strong><br><span class="redup">{{ $item->siswa->nis }} · {{ $item->siswa->rombel->nama }}</span></td>
                <td>{{ $item->hadir }}</td>
                <td>{{ $item->sakit }}</td>
                <td>{{ $item->izin }}</td>
                <td>@if($item->alpa > 0)<span class="lencana buruk">{{ $item->alpa }}</span>@else 0 @endif</td>
                <td>{{ $total > 0 ? (int) round($item->hadir / $total * 100) : 0 }}%</td>
            </tr>
        @empty
            <tr><td colspan="6">Belum ada rekap bulan ini. Admin menariknya dari presensi.</td></tr>
        @endforelse
        </tbody>
    </table>
</section>
@endsection
