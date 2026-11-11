<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul ?? 'Tata Usaha SMK Al Huda' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/tu.css">
</head>
<body>
@auth
@php $peran = auth()->user()->peran; @endphp
<div class="app">
    <aside class="rel no-cetak">
        <div class="merek"><small>SMK Al Huda</small><strong>Tata Usaha</strong></div>
        <nav class="menu">
            @if($peran === 'admin')<a href="{{ route('admin') }}" @class(['aktif' => request()->is('admin')])>Admin</a>@endif
            @if($peran !== 'murid')
                @foreach(['tu.dasbor'=>'Dasbor','tu.tunggakan'=>'Tunggakan','tu.kas'=>'Kas','tu.cocok'=>'Cocokkan','tu.laporan'=>'Laporan','tu.rombel'=>'Rombel','tu.tagihan'=>'Tagihan','tu.bayar'=>'Pembayaran','tu.administrasi'=>'Administrasi'] as $rute => $label)
                    <a href="{{ route($rute) }}" @class(['aktif' => request()->routeIs($rute)])>{{ $label }}</a>
                @endforeach
            @else
                <a class="aktif" href="{{ route('murid') }}">Keuanganku</a>
            @endif
        </nav>
    </aside>
    <div class="kolom">
        <header class="kepala no-cetak">
            <div>
                <p>{{ auth()->user()->name }}</p>
                <h1>{{ $judul ?? 'Pembayaran' }}</h1>
            </div>
            <form method="post" action="{{ route('keluar') }}">@csrf<button class="keluar">Keluar</button></form>
        </header>
        <main class="isi">
            @if(session('kabar'))<p class="kabar no-cetak">{{ session('kabar') }}</p>@endif
            @if(session('galat'))<p class="galat no-cetak">{{ session('galat') }}</p>@endif
            @yield('isi')
        </main>
    </div>
</div>
@else
<main class="masuk-layar">
    @yield('isi')
</main>
@endauth
<script>
document.querySelectorAll('[data-saring]').forEach(function (input) {
    var tabel = document.querySelector(input.getAttribute('data-saring'));
    if (!tabel) return;
    input.addEventListener('input', function () {
        var cari = input.value.toLowerCase();
        tabel.querySelectorAll('tbody tr').forEach(function (baris) {
            baris.hidden = baris.innerText.toLowerCase().indexOf(cari) === -1;
        });
    });
});
</script>
</body>
</html>
