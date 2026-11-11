<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('peran');
            $table->boolean('aktif')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('tahun_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 20);
            $table->boolean('aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nip', 30)->unique();
            $table->string('nama');
            $table->string('bagian', 20);
            $table->string('jabatan_tu', 20)->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('rombel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
            $table->string('tingkat', 5);
            $table->string('jurusan', 20);
            $table->string('nama', 50);
            $table->foreignId('walikelas_id')->nullable()->constrained('pegawai')->nullOnDelete();
            $table->foreignId('bk_id')->nullable()->constrained('pegawai')->nullOnDelete();
            $table->timestamps();
            $table->unique(['tahun_pelajaran_id', 'nama']);
        });

        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nis', 30)->unique();
            $table->string('nama');
            $table->foreignId('rombel_id')->constrained('rombel');
            $table->timestamps();
        });

        Schema::create('rekening', function (Blueprint $table) {
            $table->id();
            $table->string('bank');
            $table->string('norek', 50);
            $table->string('atas_nama');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
            $table->string('nama');
            $table->unsignedBigInteger('nominal');
            $table->string('tingkat', 10)->default('semua');
            $table->string('sasaran', 10);
            $table->foreignId('rombel_id')->nullable()->constrained('rombel')->nullOnDelete();
            $table->foreignId('siswa_id')->nullable()->constrained('siswa')->nullOnDelete();
            $table->date('tempo');
            $table->timestamps();
        });

        Schema::create('keringanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('tagihan_id')->constrained('tagihan')->cascadeOnDelete();
            $table->unsignedBigInteger('jumlah');
            $table->string('alasan');
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['siswa_id', 'tagihan_id']);
        });

        Schema::create('pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('tagihan_id')->constrained('tagihan');
            $table->unsignedBigInteger('jumlah');
            $table->date('tanggal');
            $table->string('metode', 20);
            $table->string('bukti_path');
            $table->string('status', 20)->default('menunggu');
            $table->string('alasan')->nullable();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('tagihan_id')->constrained('tagihan');
            $table->bigInteger('jumlah');
            $table->date('tanggal');
            $table->string('nomor', 30)->unique();
            $table->string('metode', 20);
            $table->string('asal', 20);
            $table->foreignId('pengajuan_id')->nullable()->constrained('pengajuan')->nullOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('janji_bayar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->unique()->constrained('siswa')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('catatan')->nullable();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dispensasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->date('sampai');
            $table->string('alasan')->nullable();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dicabut_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('tutup_kas', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->unsignedBigInteger('hitung');
            $table->unsignedBigInteger('tunai_tercatat');
            $table->bigInteger('selisih');
            $table->string('catatan')->nullable();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('setor_bank', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedBigInteger('jumlah');
            $table->string('berita')->nullable();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('isi');
            $table->date('tanggal');
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('riwayat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('aksi', 50);
            $table->string('subjek', 50);
            $table->unsignedBigInteger('subjek_id')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        foreach ([
            'riwayat', 'pengumuman', 'setor_bank', 'tutup_kas', 'dispensasi', 'janji_bayar',
            'pembayaran', 'pengajuan', 'keringanan', 'tagihan', 'rekening', 'siswa', 'rombel',
            'pegawai', 'tahun_pelajaran', 'users',
        ] as $tabel) {
            Schema::dropIfExists($tabel);
        }
    }
};
