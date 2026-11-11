<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['name', 'username', 'email', 'password', 'peran', 'aktif'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'aktif' => 'boolean'];
    }

    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    public function pegawai()
    {
        return $this->hasOne(Pegawai::class);
    }
}
