# PPDB SMK Al Huda Kota Kediri

Aplikasi Laravel ini PPDB yang baru. Bukan PPDB lama. Pratinjau web tetap acuan tampilannya.

Aturan yang sudah dikunci ada di kode `app/Ppdb`:

- Kuota hanya murid yang dikunci.
- Operator hanya verifikasi sampai kandidat.
- DU Terima hanya panitia pembayaran, setelah uang tercatat angsur atau lunas.
- Nomor `PPDB2026-0007` tidak dicetak. Angka kiri per jurusan. Angka tengah keluar saat DU Terima dan tidak dipakai ulang.
- Pindah jurusan meninggalkan tanda di jurusan lama.
- Mengundurkan diri tidak menghapus akun dan tidak menimpa status uang.
- Hapus data tahun tidak menghapus murid yang sudah masuk auth pusat.

Auth pusat yang sudah ada disambungkan lewat `AUTH_PUSAT_URL`. Kalau kosong, penerimaan murid dicatat dulu, tidak membuat akun CBT baru.
