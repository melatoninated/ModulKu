# ModulKu Backend

Backend REST API untuk aplikasi **ModulKu**, yaitu aplikasi bank modul pembelajaran digital bagi siswa Sekolah Menengah Pertama (SMP).

Backend dikembangkan menggunakan Laravel, PHP, dan MySQL. API digunakan oleh aplikasi Android untuk mengakses materi pembelajaran, kategori, mata pelajaran, kuis, serta riwayat pengerjaan kuis.

## 1. Teknologi yang Digunakan

- **Framework:** Laravel 13
- **Bahasa pemrograman:** PHP 8.5
- **Database:** MySQL 8
- **API Authentication:** Laravel Sanctum
- **API Testing:** Postman
- **Version Control:** Git dan GitHub

## 2. Fitur Backend

### Autentikasi dan Pengguna
- Registrasi akun siswa.
- Login menggunakan email dan password.
- Logout menggunakan token autentikasi.
- Mendapatkan informasi pengguna yang sedang login.
- Autentikasi API menggunakan Bearer Token.
- Pembagian hak akses berdasarkan role `admin` dan `siswa`.

### Manajemen Kategori
- Melihat daftar dan detail kategori.
- Menambahkan, mengubah, dan menghapus kategori (admin).

### Manajemen Mata Pelajaran
- Melihat daftar dan detail mata pelajaran.
- Menambahkan, mengubah, dan menghapus mata pelajaran (admin).

### Manajemen Materi
- Melihat daftar dan detail materi pembelajaran.
- Menambahkan, mengubah, dan menghapus materi (admin).
- Mendukung materi berupa teks, dokumen PDF, dan video.
- Mengunggah dan menyimpan file PDF serta video.

### Manajemen Kuis
- Melihat daftar dan detail kuis.
- Menambahkan, mengubah, dan menghapus kuis (admin).
- Mengelola pertanyaan dan pilihan jawaban.
- Mendukung jenis soal pilihan ganda dan benar/salah.

### Pengerjaan Kuis
- Siswa dapat memulai pengerjaan kuis.
- Siswa dapat mengirimkan jawaban.
- Backend memeriksa jawaban secara otomatis.
- Backend menghitung nilai berdasarkan jawaban yang benar.
- Siswa dapat melihat hasil pengerjaan.
- Siswa dapat mengerjakan kembali kuis.
- Riwayat setiap pengerjaan kuis disimpan secara terpisah.

## 3. Hak Akses Pengguna

| Fitur | Admin | Siswa |
|---|:---:|:---:|
| Registrasi akun | - | Ya |
| Login dan logout | Ya | Ya |
| Melihat kategori | Ya | Ya |
| Mengelola kategori | Ya | - |
| Melihat mata pelajaran | Ya | Ya |
| Mengelola mata pelajaran | Ya | - |
| Melihat materi | Ya | Ya |
| Mengelola materi | Ya | - |
| Melihat kuis | Ya | Ya |
| Mengelola kuis dan soal | Ya | - |
| Mengerjakan kuis | - | Ya |
| Melihat riwayat pengerjaan | Ya | Riwayat sendiri |

## 4. Struktur Database

Database ModulKu menggunakan sembilan tabel utama yang mengikuti rancangan ERD.

| No. | Tabel | Fungsi |
|---|---|---|
| 1 | `users` | Menyimpan data pengguna dan role |
| 2 | `categories` | Menyimpan kategori materi |
| 3 | `subjects` | Menyimpan mata pelajaran |
| 4 | `materials` | Menyimpan data materi pembelajaran |
| 5 | `quizzes` | Menyimpan data kuis |
| 6 | `questions` | Menyimpan pertanyaan kuis |
| 7 | `question_options` | Menyimpan pilihan jawaban dan kunci jawaban |
| 8 | `quiz_attempts` | Menyimpan setiap riwayat pengerjaan kuis |
| 9 | `attempt_answers` | Menyimpan jawaban siswa pada setiap pengerjaan |

Laravel juga menggunakan tabel pendukung, seperti `personal_access_tokens` untuk autentikasi Sanctum dan tabel bawaan Laravel lainnya.

## 5. Persyaratan Instalasi

Sebelum menjalankan backend, pastikan perangkat sudah memiliki:

- PHP 8.3 atau lebih baru.
- Composer.
- MySQL 8 atau versi kompatibel.
- Git.
- Laragon atau lingkungan pengembangan PHP dan MySQL lainnya.
- Postman (opsional, untuk pengujian API).

Pastikan ekstensi PHP yang dibutuhkan Laravel tersedia, termasuk `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml`, dan `ctype`.

## 6. Panduan Instalasi untuk Tim Frontend

Bagian ini ditujukan untuk anggota tim frontend yang ingin menjalankan backend ModulKu pada laptop masing-masing setelah melakukan clone repository.

### Langkah 1 — Clone Repository

Buka terminal atau PowerShell, kemudian jalankan:

```bash
git clone URL_REPOSITORY_GITHUB
```

Masuk ke direktori proyek:

```bash
cd ModulKu-Backend
```

Ganti `URL_REPOSITORY_GITHUB` dengan URL repository GitHub ModulKu yang sebenarnya.

### Langkah 2 — Instalasi Dependency

Jalankan:

```bash
composer install
```

### Langkah 3 — Membuat File Environment

Salin `.env.example` menjadi `.env`.

Untuk Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Untuk Linux atau macOS:

```bash
cp .env.example .env
```

### Langkah 4 — Konfigurasi Database

Buat database baru bernama `modulku` melalui phpMyAdmin atau MySQL.

Selanjutnya, buka file `.env` dan sesuaikan konfigurasi database dengan MySQL pada laptop masing-masing.

Contoh:

```env
APP_NAME=ModulKu
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=modulku
DB_USERNAME=root
DB_PASSWORD=
```

**Catatan:**
- `DB_PORT` harus disesuaikan dengan port MySQL yang digunakan.
- `DB_USERNAME` dan `DB_PASSWORD` harus disesuaikan dengan konfigurasi MySQL masing-masing.
- Jangan menggunakan database proyek lain.
- Contoh port di atas adalah 3306. Instalasi Laragon tertentu dapat menggunakan port berbeda.

### Langkah 5 — Generate Application Key

Jalankan:

```bash
php artisan key:generate
```

### Langkah 6 — Menjalankan Migration

Jalankan:

```bash
php artisan migrate
```

Perintah ini membuat seluruh tabel database berdasarkan migration yang tersedia di repository.

### Langkah 7 — Membuat Akun Admin

Jalankan seeder admin:

```bash
php artisan db:seed --class=AdminSeeder
```

Akun admin awal:

| Informasi | Nilai |
|---|---|
| Email | `admin@modulku.com` |
| Password | `Admin12345!` |
| Role | `admin` |

**Penting:** Kredensial tersebut hanya untuk pengembangan awal. Ganti password sebelum penggunaan produksi dan jangan membagikan kredensial admin kepada pihak yang tidak berwenang.

Siswa dapat membuat akun melalui endpoint registrasi. Role siswa akan diberikan secara otomatis oleh backend.

### Langkah 8 — Membuat Storage Link

Jalankan:

```bash
php artisan storage:link
```

Perintah ini menghubungkan penyimpanan file publik Laravel agar dokumen PDF dan video yang diunggah dapat diakses melalui URL.

### Langkah 9 — Menjalankan Backend

Jalankan server Laravel dengan:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Backend dapat diakses secara lokal melalui:

```text
http://127.0.0.1:8000
```

Endpoint API menggunakan prefix `/api`.

Contoh:

```text
http://127.0.0.1:8000/api/materials
```

Biarkan terminal server tetap berjalan selama aplikasi Android menggunakan API.

## 7. Integrasi dengan Android

Backend ModulKu menyediakan REST API yang dapat diakses oleh aplikasi Android menggunakan Retrofit atau HTTP client lainnya.

### Base URL API

Base URL menyesuaikan lingkungan tempat backend dijalankan.

| Lingkungan | Base URL |
|---|---|
| Android Emulator | `http://10.0.2.2:8000/api/` |
| Perangkat Android fisik | `http://IP_LAPTOP_BACKEND:8000/api/` |
| Pengujian lokal pada laptop | `http://127.0.0.1:8000/api/` |

**Catatan integrasi:**

- `127.0.0.1` pada Android menunjuk ke perangkat Android itu sendiri, bukan laptop yang menjalankan backend.
- Jika menggunakan emulator Android Studio, gunakan `10.0.2.2` untuk mengakses server lokal pada laptop yang sama.
- Jika menggunakan perangkat Android fisik, pastikan perangkat dan laptop backend berada pada jaringan Wi-Fi yang sama.
- Untuk perangkat fisik, ganti `IP_LAPTOP_BACKEND` dengan alamat IPv4 laptop yang menjalankan backend. Alamat tersebut dapat diperiksa menggunakan perintah `ipconfig` di Windows.
- Pastikan firewall Windows mengizinkan koneksi ke port 8000.
- Untuk penggunaan di luar jaringan lokal atau produksi, gunakan server dengan HTTPS.

### Autentikasi API

API yang membutuhkan autentikasi menggunakan Laravel Sanctum dengan Bearer Token.

Alur autentikasi:

1. Aplikasi Android mengirimkan email dan password ke endpoint login.
2. Backend memvalidasi kredensial pengguna.
3. Backend mengirimkan token autentikasi jika login berhasil.
4. Android menyimpan token dengan aman.
5. Setiap request ke endpoint yang dilindungi harus menyertakan token.

Contoh header:

```http
Accept: application/json
Authorization: Bearer TOKEN_PENGGUNA
```

Jangan mengirimkan token melalui URL atau membagikannya kepada pihak lain.

### Pengiriman File Materi

Untuk menambahkan atau memperbarui materi yang memiliki lampiran, gunakan `multipart/form-data`.

Field file yang digunakan:

| Field | Keterangan |
|---|---|
| `pdf_file` | File dokumen PDF |
| `video_file` | File video |
| `content` | Konten materi berupa teks |

Materi dapat memiliki konten teks, PDF, video, atau kombinasi ketiganya sesuai kebutuhan.

## 8. Daftar Endpoint API

Seluruh endpoint API menggunakan prefix:

```text
/api
```

### Authentication

| Method | Endpoint | Keterangan |
|---|---|---|
| POST | `/register` | Registrasi siswa |
| POST | `/login` | Login pengguna |
| GET | `/user` | Mendapatkan data pengguna login |
| POST | `/logout` | Logout pengguna |

### Categories

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/categories` | Daftar kategori |
| POST | `/categories` | Tambah kategori (admin) |
| GET | `/categories/{id}` | Detail kategori |
| PUT/PATCH | `/categories/{id}` | Ubah kategori (admin) |
| DELETE | `/categories/{id}` | Hapus kategori (admin) |

### Subjects

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/subjects` | Daftar mata pelajaran |
| POST | `/subjects` | Tambah mata pelajaran (admin) |
| GET | `/subjects/{id}` | Detail mata pelajaran |
| PUT/PATCH | `/subjects/{id}` | Ubah mata pelajaran (admin) |
| DELETE | `/subjects/{id}` | Hapus mata pelajaran (admin) |

### Materials

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/materials` | Daftar materi |
| POST | `/materials` | Tambah materi (admin) |
| GET | `/materials/{id}` | Detail materi |
| PUT/PATCH | `/materials/{id}` | Ubah materi (admin) |
| DELETE | `/materials/{id}` | Hapus materi (admin) |

### Quizzes

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/quizzes` | Daftar kuis |
| POST | `/quizzes` | Tambah kuis (admin) |
| GET | `/quizzes/{id}` | Detail kuis |
| PUT/PATCH | `/quizzes/{id}` | Ubah kuis (admin) |
| DELETE | `/quizzes/{id}` | Hapus kuis (admin) |

### Questions

| Method | Endpoint | Keterangan |
|---|---|---|
| POST | `/quizzes/{quiz}/questions` | Menambahkan pertanyaan (admin) |
| PUT/PATCH | `/questions/{question}` | Mengubah pertanyaan (admin) |
| DELETE | `/questions/{question}` | Menghapus pertanyaan (admin) |

### Quiz Attempts

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/attempts` | Melihat riwayat pengerjaan |
| POST | `/quizzes/{quiz}/attempts` | Memulai pengerjaan kuis |
| POST | `/attempts/{attempt}/submit` | Mengirimkan jawaban kuis |
| GET | `/attempts/{attempt}` | Melihat detail hasil pengerjaan |

Endpoint yang memerlukan autentikasi harus menyertakan Bearer Token. Hak akses setiap endpoint mengikuti role pengguna.

## 9. Keamanan Kunci Jawaban

Kolom `is_correct` hanya digunakan oleh backend untuk memeriksa dan menilai jawaban siswa.

Kolom ini **tidak boleh disertakan dalam respons API yang diterima siswa** agar kunci jawaban tidak bocor.

Ketentuan keamanan:

- Backend bertanggung jawab memeriksa jawaban berdasarkan kolom `is_correct` yang tersimpan di database.
- Respons API untuk siswa tidak boleh menampilkan nilai `is_correct` pada pilihan jawaban.
- Siswa hanya menerima informasi yang diperlukan untuk mengerjakan kuis.
- Hasil pengerjaan dapat menampilkan skor dan jumlah jawaban benar tanpa membocorkan kunci jawaban.
- Pengelolaan kunci jawaban hanya dilakukan melalui fitur admin yang memiliki otorisasi.

## 10. Pengujian API

Pengujian API dapat dilakukan menggunakan Postman.

Untuk request yang membutuhkan autentikasi, gunakan Bearer Token yang diperoleh dari endpoint login.

Header yang disarankan:

```http
Accept: application/json
Content-Type: application/json
```

Untuk endpoint yang mengunggah file, gunakan `multipart/form-data`.

## 11. Struktur Direktori Penting

```text
ModulKu-Backend/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   └── Models/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   └── storage/
├── routes/
│   └── api.php
├── storage/
│   └── app/
│       └── public/
├── .env.example
├── .gitignore
├── artisan
├── composer.json
└── README.md
```

## 12. Catatan untuk Pengembangan Tim

- Backend dan frontend dikembangkan pada lingkungan kerja masing-masing sesuai pembagian tugas tim.
- Anggota frontend dapat melakukan clone repository backend untuk menjalankan API secara lokal.
- Setiap anggota tim harus menggunakan konfigurasi database lokal masing-masing.
- Perubahan kode backend perlu di-push ke GitHub agar anggota frontend dapat mengambil versi terbaru.
- Jangan melakukan commit terhadap file `.env`, token autentikasi, password database, atau informasi rahasia lainnya.
- Gunakan migration untuk perubahan struktur database agar perubahan dapat diterapkan oleh seluruh anggota tim.
- Pastikan perubahan API didokumentasikan agar integrasi Android tetap konsisten.

---

**ModulKu Backend**  
REST API untuk aplikasi bank modul pembelajaran digital SMP.