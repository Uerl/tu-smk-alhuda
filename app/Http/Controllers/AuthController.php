<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function form()
    {
        if ($user = Auth::user()) {
            return redirect($this->tujuan($user->peran));
        }

        return view('masuk');
    }

    public function masuk(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
        if (! Auth::attempt(['username' => $data['username'], 'password' => $data['password'], 'aktif' => true], true)) {
            return back()->withErrors(['username' => 'Username atau kata sandi tidak cocok.'])->onlyInput('username');
        }
        $request->session()->regenerate();

        return redirect($this->tujuan(Auth::user()->peran));
    }

    public function keluar(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('masuk');
    }

    private function tujuan(string $peran): string
    {
        return match ($peran) {
            'admin' => '/admin',
            'murid' => '/murid',
            default => '/tu',
        };
    }
}
