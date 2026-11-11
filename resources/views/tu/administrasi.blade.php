@extends('layouts.app', ['judul' => 'Administrasi'])
@section('isi')
@php use App\Tu\Tampil; @endphp
<div class="daftar">
    @foreach($baris as $item)
        @php $status = $item['status']['status']; @endphp
        <article class="kartu baris">
            <div>
                <strong>{{ $item['siswa']->nama }}</strong>
                <p class="redup">{{ $item['siswa']->nis }} · {{ $item['siswa']->rombel->nama }}</p>
                @if($status !== 'Lancar')<p class="redup">Lewat {{ Tampil::rupiah($item['status']['lewat']) }} @if($item['status']['sampai']) sampai {{ Tampil::tanggal($item['status']['sampai']) }} @endif</p>@endif
            </div>
            <span class="lencana {{ $status === 'Ditahan' ? 'buruk' : ($status === 'Dispensasi' ? 'tunggu' : '') }}">{{ $status }}</span>
            @if($status === 'Dispensasi')
                <form method="post" action="{{ route('tu.dispensasi.cabut', $item['siswa']) }}">@csrf @method('DELETE')<button class="tombol">Cabut</button></form>
            @else
                <form class="baris" method="post" action="{{ route('tu.dispensasi') }}">
                    @csrf
                    <input type="hidden" name="siswa_id" value="{{ $item['siswa']->id }}">
                    <input type="date" name="sampai" required value="{{ now()->addDays(14)->toDateString() }}">
                    <input name="alasan" placeholder="Alasan dispensasi">
                    <button class="tombol">Dispensasi</button>
                </form>
            @endif
        </article>
    @endforeach
</div>
@endsection
