@extends('layouts.app', ['judul' => 'Administrasi'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<input class="saring" data-saring="#tabel-administrasi" placeholder="Cari nama, NIS, kelas, atau status">
<div class="tabel-bungkus">
    <table id="tabel-administrasi">
        <thead><tr><th>Murid</th><th>Kelas</th><th>Status</th><th>Dispensasi</th></tr></thead>
        <tbody>
        @foreach($baris as $item)
            @php $status = $item['status']['status']; @endphp
            <tr>
                <td><strong>{{ $item['siswa']->nama }}</strong><br><span class="redup">{{ $item['siswa']->nis }}</span></td>
                <td>{{ $item['siswa']->rombel->nama }}</td>
                <td>
                    <span class="lencana {{ $status === 'Ditahan' ? 'buruk' : ($status === 'Dispensasi' ? 'tunggu' : '') }}">{{ $status }}</span>
                    @if($status !== 'Lancar')<p class="redup">Lewat {{ Tampil::rupiah($item['status']['lewat']) }} @if($item['status']['sampai']) sampai {{ Tampil::tanggal($item['status']['sampai']) }} @endif</p>@endif
                </td>
                <td>
                    @if($status === 'Dispensasi')
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
        @endforeach
        </tbody>
    </table>
</div>
@endsection
