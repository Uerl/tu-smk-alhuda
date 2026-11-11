@extends('layouts.app')
@section('isi')
<div class="masuk-layar">
    <section class="kartu masuk-kartu">
        <h2>Masuk Tata Usaha</h2>
        <p class="redup">Pembayaran murid SMK Al Huda Kota Kediri</p>
        <form class="formulir" method="post" action="{{ route('masuk.kirim') }}" style="margin-top:16px">
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
</div>
@endsection
