@extends('layouts.app', ['judul' => 'Cocokkan transfer'])
@section('isi')
<form class="kartu formulir" method="post" action="{{ route('tu.cocok.simpan') }}">
    @csrf
    <h2>Cocokkan transfer</h2>
    <p class="redup">Uang dibagi ke tagihan yang belum lunas, dari jatuh tempo paling awal.</p>
    <label>Kode bayar<input name="kode" required value="{{ old('kode') }}" placeholder="TU-20240011"></label>
    <label>Jumlah<input type="number" name="jumlah" min="1" required value="{{ old('jumlah') }}"></label>
    <label>Tanggal<input type="date" name="tanggal" required value="{{ old('tanggal', now()->toDateString()) }}"></label>
    <button class="tombol utama">Catat transfer</button>
</form>
@endsection
