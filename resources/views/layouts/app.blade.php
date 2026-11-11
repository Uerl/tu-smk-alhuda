<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul ?? 'Tata Usaha SMK Al Huda' }}</title>
    <link rel="stylesheet" href="/tu.css">
</head>
<body>
@php $peran = auth()->user()?->peran; @endphp
<header class="kepala no-cetak">
    <div class="kepala-dalam">
        <div>
            <p>SMK Al Huda Kota Kediri</p>
            <h1>{{ $judul ?? 'Tata Usaha · Pembayaran' }}</h1>
        </div>
        @auth
            <form method="post" action="{{ route('keluar') }}">@csrf<button class="keluar">Keluar</button></form>
        @endauth
    </div>
</header>
<main class="isi">
    @if($peran === 'admin' || $peran === 'staf')
        <nav class="menu no-cetak">
            @if($peran === 'admin')<a href="{{ route('admin') }}" @class(['aktif' => request()->is('admin')])>Admin</a>@endif
            @foreach(['tu.dasbor'=>'Dasbor','tu.tunggakan'=>'Tunggakan','tu.kas'=>'Kas','tu.cocok'=>'Cocokkan','tu.laporan'=>'Laporan','tu.rombel'=>'Rombel','tu.tagihan'=>'Tagihan','tu.bayar'=>'Pembayaran','tu.administrasi'=>'Administrasi'] as $rute => $label)
                <a href="{{ route($rute) }}" @class(['aktif' => request()->routeIs($rute)])>{{ $label }}</a>
            @endforeach
        </nav>
    @endif
    @if(session('kabar'))<p class="kabar no-cetak">{{ session('kabar') }}</p>@endif
    @if(session('galat'))<p class="galat no-cetak">{{ session('galat') }}</p>@endif
    @yield('isi')
</main>
</body>
</html>
