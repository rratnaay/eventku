# EventKu — Event Discovery & Management Platform

Platform untuk menemukan event, mendaftar sebagai peserta, dan membantu panitia mengelola acara.
Dibangun dengan **Laravel 13**, **Blade**, **Tailwind CSS v4**, **Vite**, dan **MySQL**.

## Fitur

- **Publik**: Home, Jelajah Event (cari, filter kategori/kota/tanggal/status, urutkan), Detail Event, About, Contact (pesan tersimpan di DB).
- **Autentikasi**: registrasi, login, logout, validasi, throttling. Peran `user` dan `admin`.
- **Pendaftaran event**: transaksi database + `SELECT … FOR UPDATE` pada baris event, unique index `(user_id, event_id)`, kode registrasi unik (`EVK-XXXXXXXX`), pembatalan (kuota kembali), My Registrations & Registration Detail.
- **Admin**: dashboard (statistik, grafik 14 hari, tabel terbaru), CRUD event (upload poster tervalidasi), kelola kategori, kelola pendaftar & status.
- **Keamanan**: CSRF, hashing password, form request, policy (user tidak bisa melihat/mengubah pendaftaran orang lain), middleware `admin`, `role`/`created_by` tidak mass-assignable.

## Menjalankan secara lokal

Prasyarat: PHP ≥ 8.3, Composer, Node.js ≥ 20, MySQL 8.

```bash
composer install
npm install
cp .env.example .env          # Windows PowerShell: Copy-Item .env.example .env
php artisan key:generate
```

Buat database kosong, lalu sesuaikan `.env`:

```sql
CREATE DATABASE eventku CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eventku
DB_USERNAME=root
DB_PASSWORD=
```

```bash
php artisan migrate --seed
php artisan storage:link      # agar poster hasil upload tampil
npm run build                 # atau `npm run dev` saat pengembangan
php artisan serve             # http://127.0.0.1:8000
```

> **Windows + OneDrive**: jika muncul error *"bootstrap/cache must be present and writable"*, jalankan `attrib -R bootstrap\cache`.


## Cara menguji manual

| Fitur | Langkah |
|-------|---------|
| Daftar event | Login sebagai user → `/events` → buka event → **Daftar Sekarang** → lihat kode di halaman detail & `/my-registrations` |
| Pendaftaran ganda | Daftar ke event yang sama lagi → muncul pesan "sudah terdaftar" |
| Kuota penuh | Buka *Intimate Jazz Night* (kuota 25, sudah penuh dari seeder) → tombol nonaktif |
| Event ditutup | Buka *Malam Puisi & Musikalisasi* → pendaftaran ditutup |
| Hak akses | Login sebagai user → buka `/admin` → 403 |
| CRUD event | Login admin → `/admin/events` → Event Baru / ubah / hapus (ada konfirmasi) |
| Upload poster | Form event → pilih file >2 MB atau non-gambar → ditolak |
| Kategori | `/admin/categories` → tambah/ubah; kategori yang dipakai event tidak bisa dihapus |
| Status pendaftar | `/admin/registrations` → ubah status lewat dropdown |

