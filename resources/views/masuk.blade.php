@extends('layouts.app')
@section('isi')
<section class="kartu masuk-kartu">
    <p class="redup">SMK Al Huda Kota Kediri</p>
    <h2>Tata Usaha</h2>
    <form class="formulir" method="post" action="{{ route('masuk.kirim') }}">
        @csrf
        <label>Username atau NIS<input name="username" value="{{ old('username') }}" required autofocus></label>
        <label>Kata sandi<input name="password" type="password" required></label>
        @error('username')<p class="galat">{{ $message }}</p>@enderror
        <button class="tombol utama">Masuk</button>
    </form>
    <div class="akun">
        <div>Admin · admin · Tu12345</div>
        <div>Bendahara · bendahara · Tu12345</div>
        <div>Murid contoh · 20240011 · Murid123</div>
    </div>
</section>
@endsection
