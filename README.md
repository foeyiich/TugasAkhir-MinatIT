# Tugas Akhir - PHP MVC Framework

Kerangka kerja (framework) Model-View-Controller (MVC) berbasis PHP murni yang dikembangkan untuk keperluan Tugas Akhir. Kerangka kerja ini dibangun dengan arsitektur yang berorientasi pada objek (OOP), menyediakan fondasi yang ringan dan terstruktur untuk membangun aplikasi web.

## Fitur Utama

- **Arsitektur MVC:** Pemisahan logika aplikasi, akses data, dan antarmuka pengguna yang jelas.
- **Custom ORM (Active Record):** Abstraksi basis data (PDO) yang aman dari *SQL Injection* dengan pemetaan skema otomatis.
- **Auto-Healing Environment:** Pembuatan dan validasi otomatis file konfigurasi `.env`.
- **Role-Based Access Control (RBAC):** Otentikasi dan otorisasi pengguna bawaan.
- **Manajemen Sesi & Kuki Aman:** Proteksi terhadap *Session Fixation*, perlindungan XSS (*HttpOnly*), dan CSRF (*SameSite*).
- **Integrated CLI Testing:** Kerangka pengujian mandiri untuk *unit test* tanpa dependensi eksternal.

## Persyaratan Sistem

Pastikan lingkungan peladen (server) Anda memenuhi persyaratan berikut sebelum menjalankan aplikasi:

- PHP >= 8.5.6
- Composer (untuk memuat otomatis standar PSR-4)
- Ekstensi PHP PDO (untuk SQLite atau MySQL)

## Instalasi

1. **Kloning repositori**
   Unduh atau kloning repositori ini ke dalam direktori server lokal Anda.

2. **Instalasi Dependensi**
   Jalankan Composer untuk menginisialisasi autoloader:
   ```bash
   composer install
