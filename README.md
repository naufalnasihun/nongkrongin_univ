<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

LEMBAR KERJA PRAKTIKUM (LKP)
Pertemuan 4 — Basis Data, Migrasi, Eloquent & Seeder
Tugas Mandiri: Implementasi Database Sesuai Proyek Kelompok
Pemrograman Web Lanjut · Universitas Hasyim Asy'ari Tebuireng Jombang · Program Studi Teknik Informatika · Semester V

Mata Kuliah	Pemrograman Web Lanjut (3 SKS — Semester V)
Dosen Pengampu	Edwin Hari Agus Prastyo, S.Kom., M.Kom.
Sifat Tugas	Mandiri (Individu) — Berdasarkan Studi Kasus Proyek Kelompok
Teknologi	Laravel 11/12 · PHP 8.2+ · Composer · Laragon (MySQL/MariaDB) · Eloquent ORM
CPMK	CPMK-1, CPMK-2 · Sub-CPMK: S2, S4
Prasyarat	Selesai Modul 03 (Arsitektur Laravel, MVC, dan Perancangan Diagram UML)
Luaran Utama	Diagram ERD + File Migrasi + Model Eloquent Berelasi + Factory & Seeder + Data Dummy
Penyerahan Kode	Source code ditulis pada proyek Laravel lalu di-commit ke repositori Git — tidak disalin ke lembar kerja ini

Copyright © Edwin Hari Agus Prastyo, S.Kom., M.Kom.
I. IDENTITAS MAHASISWA & PROYEK
Setiap mahasiswa wajib mengisi data diri dan identitas proyek kelompok yang telah disepakati:
Komponen	Isian Mahasiswa
Nama Lengkap	Naufal Nashihun Nizhom
NIM	2495114016
Kelas / Semester	Karyawan / V (Lima)
Nama Kelompok	Kelompok 2
Judul Proyek Web	Nongkrongin univ
Kasus Proyek Kelompok	
Sistem Pre-Order Makanan dan Minuman Kantin Kampus

URL Repositori Git	https://github.com/naufalnasihun/nongkrongin_univ 

Branch Pengerjaan	______________________

II. CAPAIAN & TUJUAN PRAKTIKUM
Setelah menyelesaikan lembar kerja praktikum mandiri ini, mahasiswa mampu:
1. Mengonfigurasi koneksi basis data MySQL/MariaDB melalui Laragon pada berkas .env.
2. Merancang Entity Relationship Diagram (ERD) yang selaras dengan rancangan Class Diagram dari Modul 03.
3. Membuat dan menyusun urutan file migrasi tabel beserta konstrain integritas data.
4. Mendefinisikan Model Eloquent dan mengimplementasikan relasi antar entitas.
5. Membangun data dummy realistis menggunakan kombinasi Factory (Faker) dan Database Seeder.
6. Menjalankan perintah migrasi dan memverifikasi konsistensi struktur data pada DBMS.
Keterkaitan CPMK:
• CPMK-1 (Sub-CPMK S2): Merancang model data dan keterhubungan entitas aplikasi web.
• CPMK-2 (Sub-CPMK S4): Mengintegrasikan basis data, relasi skema, migrasi, dan populasi data awal.

III. RINGKASAN TEORI PENDUKUNG
1. Konvensi Standar Laravel Eloquent
• Nama Tabel: Bentuk jamak snake_case (contoh: users, products, medical_records).
• Nama Model: Bentuk tunggal PascalCase (contoh: User, Product, MedicalRecord).
• Foreign Key: Nama model tunggal + _id (contoh: user_id, category_id, doctor_id).
• Mass Assignment: Kolom yang dapat diisi wajib didaftarkan pada properti $fillable.
2. Jenis Relasi Eloquent
Jenis Relasi	Method Model Induk	Method Model Anak	Penempatan FK
One-to-Many	hasMany(Child::class)	belongsTo(Parent::class)	FK di tabel anak
One-to-One	hasOne(Child::class)	belongsTo(Parent::class)	FK di tabel anak
Many-to-Many	belongsToMany(Other::class)	belongsToMany(Other::class)	Tabel pivot terpisah

3. Urutan Eksekusi Migrasi
Tabel induk (parent/master) wajib dibuat lebih dahulu sebelum tabel anak (child/detail). Jika urutan terbalik, konstrain foreignId()->constrained() akan gagal dengan error foreign key constraint fail.
IV. ALAT, BAHAN & LINGKUNGAN KERJA
Pastikan seluruh komponen berikut tersedia dan aktif sebelum memulai:
Kebutuhan	Software / Komponen	Status Kesiapan
Web Server & Database	Laragon + MySQL/MariaDB	[   ] Aktif
Runtime & Package Manager	PHP >= 8.2 & Composer 2.x	[   ] Terpasang
IDE / Code Editor	Visual Studio Code / PHPStorm	[   ] Siap
Database Client	phpMyAdmin / HeidiSQL / DBeaver	[   ] Siap
Diagramming Tool	Draw.io / dbdiagram.io / PlantUML	[   ] Siap
Proyek Laravel	Proyek hasil pengerjaan LKP Modul 03	[   ] Siap berjalan

V. LEMBAR KERJA MAHASISWA MANDIRI
Ketentuan Pengerjaan Lembar Kerja:
• Lembar kerja ini bukan tempat melampirkan source code. Seluruh kode (migrasi, model, factory, seeder, konfigurasi .env) ditulis pada proyek Laravel kelompok Anda dan di-commit ke repositori Git.
• Isi lembar kerja sebatas rancangan, tabel isian, dan refleksi. Untuk setiap berkas kode cukup dicatat path/nama berkas beserta status commit-nya.
• Tautan repositori dan branch pengerjaan dicatat pada Bagian I agar dapat diperiksa dosen/asisten pengampu.
• Kolom Commit pada tabel isian ditandai [X] setelah berkas di-commit dan di-push ke repositori; biarkan kosong bila belum.

TAHAP 1: PERANCANGAN ENTITAS & RELASI (ERD)

1.1 Identifikasi Entitas / Model Proyek
Tuliskan seluruh entitas yang akan dibangun pada proyek kelompok Anda, sesuai Class Diagram dari Modul 03:
No	Nama Entitas (Model)	Nama Tabel (Database)	Fungsi & Peran dalam Sistem
1	User	users	Menyimpan data dasar pengguna dan autentikasi sistem
2	Customer	customers	Menyimpan data khusus Customer/Mahasiswa seperti NIM dan kelas
3	Kasir	kasirs	Menyimpan data khusus kasir yang memproses pesanan
4	Admin	admins	Menyimpan data khusus admin yang mengelola sistem
5	Menu	menus	Menyimpan data makanan/minuman yang tersedia
6	Keranjang	keranjangs	Menyimpan keranjang milik Customer
7	KeranjangItem	keranjang_items	Menyimpan menu dan jumlah item dalam keranjang
8	Pesanan	pesanans	Menyimpan data pre-order, nomor pesanan, total dan status
9	PesananItem	pesanan_items	Menyimpan detail menu yang terdapat dalam pesanan
			

1.2 Matriks Pemetaan Relasi Antar Entitas
Petakan hubungan antar entitas beserta kardinalitas dan foreign key yang digunakan:
No	Entitas Asal	Bentuk Relasi	Entitas Target	Kolom Kunci Tamu (Foreign Key)
1	User	01:01	Customer	customers.user_id
2	User	01:01	Kasir	kasirs.user_id
3	User	01:01	Admin	admins.user_id
4	Customer	01:01	Keranjang	keranjangs.customer_id
5	Keranjang	1 : N	KeranjangItem	keranjang_items.keranjang_id
6	Menu	1 : N	KeranjangItem	keranjang_items.menu_id
7	Customer	1 : N	Pesanan	pesanans.customer_id
8	Kasir	1 : N	Pesanan	pesanans.kasir_id
9	Pesanan	1 : N	PesananItem	pesanan_items.pesanan_id
10	Menu	1 : N	PesananItem	pesanan_items.menu_id

 

1.3 Diagram Hubungan Entitas (ERD)
Rancang ERD menggunakan draw.io / dbdiagram.io / PlantUML, lalu lampirkan hasilnya di sini:
STRUKTUR / DIAGRAM — TEMPAT PENEMPELAN GAMBAR
 
Path berkas gambar ERD : screenshots/erd_proyek.png
Tools yang digunakan   : draw io

TAHAP 2: KONFIGURASI DATABASE & ENVIRONMENT

2.1 Pembuatan Database pada DBMS
1. Buka Laragon → Database → buka phpMyAdmin
2. Buat basis data baru:
• Nama Database : nongkrongin_univ
• Collation     : utf8mb4_unicode_ci
2.2 Konfigurasi Berkas .env
Terapkan baris konfigurasi database berikut pada berkas .env proyek (berkas .env bersifat lokal dan tidak di-commit; cukup sertakan .env.example pada repositori):
KONFIGURASI BERKAS .ENV
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=____________________________________
DB_USERNAME=root
DB_PASSWORD=

2.3 Uji Koneksi Database
Jalankan perintah berikut di terminal proyek:
PERINTAH TERMINAL (ARTISAN)
php artisan migrate:status

Tuliskan output terminal atau catatan hasil pengujian:
AREA JAWABAN — SILAKAN TULIS DI SINI
C:\laragon\www\univ
λ php artisan migrate

   INFO  Preparing database.

  Creating migration table ........................................................................................................... 328.70ms DONE

   INFO  Running migrations.

  0001_01_01_000000_create_users_table ............................................................................................... 295.31ms DONE
  0001_01_01_000001_create_cache_table ................................................................................................ 76.53ms DONE
  0001_01_01_000002_create_jobs_table ................................................................................................ 165.03ms DONE

Status koneksi: Terhubung Berhasil
TAHAP 3: IMPLEMENTASI SKEMA MIGRASI

3.1 Berkas Migrasi di Repositori
Perintah make:migration dijalankan pada terminal proyek, lalu berkas hasilnya di-commit ke repositori. Catat berkas migrasi yang Anda hasilkan, terurut dari tabel master ke tabel anak/transaksi:
No	Berkas Migrasi (database/migrations/)	Tabel yang Dibuat	Commit
1	0001_01_01_000000_create_users_table.php	users	comit
2	2026_09_22_040930_create_customers_table.php	customers	comit
3	2026_09_22_040940_create_kasirs_table.php	kasirs	Ter-comit
4	2026_09_22_040945_create_admins_table.php	admins	Ter-comit
5	2026_09_22_040953_create_menus_table.php	menus	Ter-comit
6	2026_09_22_041018_create_keranjangs_table.php	keranjangs	Ter-comit
7	2026_09_22_041026_create_keranjang_items_table.php	keranjang_items	Ter-comit
8	2026_09_22_041033_create_pesanans_table.php	pesanans	Ter-comit
9	2026_09_22_041039_create_pesanan_items_table.php	pesanan_items	Ter-comit

3.2 Skema Tabel Master Inti (Tabel 1)
• Nama Tabel: users
• Berkas Migrasi: database/migrations/0001_01_01_000000_create_users_table.php
• Status Commit: Ter-commit
Implementasikan method up() pada berkas migrasi tersebut di repositori, lalu jelaskan rancangan kolomnya di sini:
Penjelasan kolom utama tabel ini:
Nama Kolom	Tipe Data	Nullable	Keterangan / Aturan
id	BIGINT	Tidak	Primary Key
name	VARCHAR(255)	Tidak	Nama pengguna
email	VARCHAR(255)	Tidak	Email pengguna
email_verified_at	TIMESTAMP	Ya	Waktu verifikasi email
password	VARCHAR(255)	Tidak	Password pengguna
remember_token	VARCHAR(100)	Ya	Token autentikasi
created_at	TIMESTAMP	Ya	Waktu data dibuat
updated_at	TIMESTAMP	Ya	Waktu data diperbarui

3.3 Skema Tabel Transaksi / Detail dengan Foreign Key (Tabel 2)
• Nama Tabel: customers
• Berkas Migrasi: database/migrations/2026_09_22_040930_create_customers_table.php
• Merujuk ke Tabel:  users dan -
• Status Commit: Ter-commit
Definisikan foreign key pada berkas migrasi di repositori menggunakan foreignId()->constrained() (tambahkan cascadeOnDelete() bila data anak harus ikut terhapus). Petakan kolom dan relasinya di sini:
Nama Kolom	Tipe Data	Foreign Key / Constraint	Keterangan
id	BIGINT UNSIGNED	PRIMARY KEY	ID unik customer
user_id	BIGINT UNSIGNED	FOREIGN KEY → users.id, UNIQUE, CASCADE ON DELETE	Relasi customer dengan akun user
nim	VARCHAR(255)	UNIQUE	Nomor Induk Mahasiswa
nama	VARCHAR(255)	—	Nama customer/mahasiswa
kelas	VARCHAR(255)	—	Kelas mahasiswa
created_at	TIMESTAMP	—	Waktu data dibuat
updated_at	TIMESTAMP	—	Waktu data diperbarui

3.4 Skema Tabel Tambahan / Pivot (Tabel 3 dan Seterusnya)
(Salin tabel isian ini untuk setiap tabel tambahan sesuai ERD proyek; kodenya tetap berada di repositori)
• Nama Tabel: Kasir
• Berkas Migrasi: database/migrations/2026_09_22_040940_create_kasirs_table.php
• Status Commit: Ter-commit
Nama Kolom	Tipe Data	Constraint / Relasi	Keterangan
id	BIGINT UNSIGNED	PRIMARY KEY	ID unik kasir
user_id	BIGINT UNSIGNED	FOREIGN KEY → users.id, UNIQUE, CASCADE ON DELETE	Relasi kasir dengan akun user
nama	VARCHAR(255)	—	Nama kasir
created_at	TIMESTAMP	—	Waktu data dibuat
updated_at	TIMESTAMP	—	Waktu data diperbarui
3.5 Skema Tabel Tambahan / Pivot (Tabel 3 dan Seterusnya)
(Salin tabel isian ini untuk setiap tabel tambahan sesuai ERD proyek; kodenya tetap berada di repositori)
• Nama Tabel: admins
• Berkas Migrasi: database/migrations/2026_09_22_040945_create_admins_table.php
• Status Commit: Ter-commit
Nama Kolom	Tipe Data	Constraint / Relasi	Keterangan
id	BIGINT UNSIGNED	PRIMARY KEY	ID unik admin
user_id	BIGINT UNSIGNED	FOREIGN KEY → users.id, UNIQUE, CASCADE ON DELETE	Relasi admin dengan akun user
nama	VARCHAR(255)	—	Nama admin
created_at	TIMESTAMP	—	Waktu data dibuat
updated_at	TIMESTAMP	—	Waktu data diperbarui
3.6 Skema Tabel Tambahan / Pivot (Tabel 3 dan Seterusnya)
(Salin tabel isian ini untuk setiap tabel tambahan sesuai ERD proyek; kodenya tetap berada di repositori)
• Nama Tabel: menus
• Berkas Migrasi: database/migrations/2026_09_22_040953_create_menus_table.php
• Status Commit: Ter-commit
Nama Kolom	Tipe Data	Constraint / Relasi	Keterangan
id	BIGINT UNSIGNED	PRIMARY KEY	ID unik menu
nama_menu	VARCHAR(255)	—	Nama makanan/minuman
foto	VARCHAR(255)	NULLABLE	Foto menu
harga	DECIMAL(12,2)	—	Harga menu
deskripsi	TEXT	NULLABLE	Deskripsi menu
ketersediaan	BOOLEAN	DEFAULT TRUE	Menunjukkan menu tersedia atau tidak
created_at	TIMESTAMP	—	Waktu data dibuat
updated_at	TIMESTAMP	—	Waktu data diperbarui

 
3.7 Skema Tabel Tambahan / Pivot (Tabel 3 dan Seterusnya)
(Salin tabel isian ini untuk setiap tabel tambahan sesuai ERD proyek; kodenya tetap berada di repositori)
• Nama Tabel: keranjangs
• Berkas Migrasi: database/migrations/2026_09_22_041018_create_keranjangs_table.php
• Status Commit: Ter-commit
Nama Kolom	Tipe Data	Constraint / Relasi	Keterangan
id	BIGINT UNSIGNED	PRIMARY KEY	ID unik keranjang
customer_id	BIGINT UNSIGNED	FOREIGN KEY → customers.id, UNIQUE, CASCADE ON DELETE	Relasi keranjang dengan customer
created_at	TIMESTAMP	—	Waktu data dibuat
updated_at	TIMESTAMP	—	Waktu data diperbarui
3.8 Skema Tabel Tambahan / Pivot (Tabel 3 dan Seterusnya)
(Salin tabel isian ini untuk setiap tabel tambahan sesuai ERD proyek; kodenya tetap berada di repositori)
• Nama Tabel: keranjang_items
• Berkas Migrasi: database/migrations/2026_09_22_041026_create_keranjang_items_table.php
• Status Commit: Ter-commit
Nama Kolom	Tipe Data	Constraint / Relasi	Keterangan
id	BIGINT UNSIGNED	PRIMARY KEY	ID unik item keranjang
keranjang_id	BIGINT UNSIGNED	FOREIGN KEY → keranjangs.id, CASCADE ON DELETE	Relasi item dengan keranjang
menu_id	BIGINT UNSIGNED	FOREIGN KEY → menus.id, CASCADE ON DELETE	Relasi item dengan menu
jumlah	INTEGER	—	Jumlah menu yang dimasukkan ke keranjang
subtotal	DECIMAL(12,2)	—	Total harga item berdasarkan jumlah
created_at	TIMESTAMP	—	Waktu data dibuat
updated_at	TIMESTAMP	—	Waktu data diperbarui
3.9 Skema Tabel Tambahan / Pivot (Tabel 3 dan Seterusnya)
(Salin tabel isian ini untuk setiap tabel tambahan sesuai ERD proyek; kodenya tetap berada di repositori)
• Nama Tabel: pesanans
• Berkas Migrasi: database/migrations/2026_09_22_041033_create_pesanans_table.php
• Status Commit: Ter-commit
Nama Kolom	Tipe Data	Constraint / Relasi	Keterangan
id	BIGINT UNSIGNED	PRIMARY KEY	ID unik pesanan
customer_id	BIGINT UNSIGNED	FOREIGN KEY → customers.id, CASCADE ON DELETE	Customer yang membuat pesanan
kasir_id	BIGINT UNSIGNED	FOREIGN KEY → kasirs.id, NULLABLE, NULL ON DELETE	Kasir yang menangani pesanan
nomor_pesanan	VARCHAR(255)	UNIQUE	Nomor unik setiap pesanan
tanggal	DATETIME	—	Tanggal dan waktu pesanan
total_harga	DECIMAL(12,2)	—	Total harga pesanan
status	VARCHAR(255)	DEFAULT Menunggu	Status proses pesanan
created_at	TIMESTAMP	—	Waktu data dibuat
updated_at	TIMESTAMP	—	Waktu data diperbarui
id	BIGINT UNSIGNED	PRIMARY KEY	ID unik pesanan
4.0 Skema Tabel Tambahan / Pivot (Tabel 3 dan Seterusnya)
(Salin tabel isian ini untuk setiap tabel tambahan sesuai ERD proyek; kodenya tetap berada di repositori)
• Nama Tabel: pesanan_items
• Berkas Migrasi: database/migrations/2026_09_22_041039_create_pesanan_items_table.php
• Status Commit: Ter-commit
Nama Kolom	Tipe Data	Constraint / Relasi	Keterangan
id	BIGINT UNSIGNED	PRIMARY KEY	ID unik detail pesanan
pesanan_id	BIGINT UNSIGNED	FOREIGN KEY → pesanans.id, CASCADE ON DELETE	Relasi detail dengan pesanan
menu_id	BIGINT UNSIGNED	FOREIGN KEY → menus.id, CASCADE ON DELETE	Menu yang dipesan
jumlah	INTEGER	—	Jumlah menu yang dipesan
harga	DECIMAL(12,2)	—	Harga menu saat dipesan
subtotal	DECIMAL(12,2)	—	Total harga berdasarkan jumlah
created_at	TIMESTAMP	—	Waktu data dibuat
updated_at	TIMESTAMP	—	Waktu data diperbarui

TAHAP 4: IMPLEMENTASI MODEL ELOQUENT & RELASI

4.1 Berkas Model di Repositori
Jalankan make:model pada terminal proyek, lalu catat model yang dihasilkan beserta tabel yang diwakilinya:
No	Model (app/Models/)	Tabel	Relasi Utama	Commit
1	User.php	users	hasOne Customer, Kasir, Admin	Ter-commit
2	Customer.php	customers	belongsTo User, hasOne Keranjang, hasMany Pesanan	Ter-commit
3	Kasir.php	kasirs	belongsTo User, hasMany Pesanan	Ter-commit
4	Admin.php	admins	belongsTo User	Ter-commit
5	Menu.php	menus	hasMany KeranjangItem, PesananItem	Ter-commit
6	Keranjang.php	keranjangs	belongsTo Customer, hasMany KeranjangItem	Ter-commit
7	KeranjangItem.php	keranjang_items	belongsTo Keranjang, Menu	Ter-commit
8	Pesanan.php	pesanans	belongsTo Customer, Kasir, hasMany PesananItem	Ter-commit
9	PesananItem.php	pesanan_items	belongsTo Pesanan, Menu	Ter-commit

4.2 Model Induk (HasMany / HasOne)
• Berkas: app/Models/User.php
• Status Commit: Ter-commit
Implementasikan $fillable, $casts, dan method relasi pada model tersebut di repositori, lalu rangkum isinya di sini:
Properti dan relasi yang didefinisikan pada model ini:
Method / Properti	Jenis	Target / Isi	Keterangan
User	name, email, password	email_verified_at, password	hasOne Customer, hasOne Kasir, hasOne Admin

4.3 Model Anak (BelongsTo)
1.Customer
• Berkas: app/Models/Customer.php
• Status Commit: Ter-commit
Implementasikan $fillable (termasuk foreign key) dan method belongsTo() pada model tersebut di repositori, lalu rangkum relasinya di sini:
Method Relasi	Target Model	Foreign Key	Keterangan
user() → belongsTo()	User	user_id	Customer dimiliki oleh satu User
2.Kasir
• Berkas: app/Models/Kasir.php
• Status Commit: Ter-commit
Implementasikan $fillable (termasuk foreign key) dan method belongsTo() pada model tersebut di repositori, lalu rangkum relasinya di sini:
Method Relasi	Target Model	Foreign Key	Keterangan
user() → belongsTo()	User	user_id	Kasir dimiliki oleh satu User
3.Admin
• Berkas: app/Models/Admin.php
• Status Commit: Ter-commit
Implementasikan $fillable (termasuk foreign key) dan method belongsTo() pada model tersebut di repositori, lalu rangkum relasinya di sini:
Method Relasi	Target Model	Foreign Key	Keterangan
user() → belongsTo()	User	user_id	Admin dimiliki oleh satu User

 
4.Keranjang
• Berkas: app/Models/Keranjang.php
• Status Commit: Ter-commit
Implementasikan $fillable (termasuk foreign key) dan method belongsTo() pada model tersebut di repositori, lalu rangkum relasinya di sini:
Method Relasi	Target Model	Foreign Key	Keterangan
customer() → belongsTo()	Customer	customer_id	Keranjang dimiliki oleh satu Customer
5.KeranjangItem
• Berkas: app/Models/KeranjangItem.php
• Status Commit: Ter-commit
Implementasikan $fillable (termasuk foreign key) dan method belongsTo() pada model tersebut di repositori, lalu rangkum relasinya di sini:
Method Relasi	Target Model	Foreign Key	Keterangan
keranjang() → belongsTo()	Keranjang	keranjang_id	Item berada dalam satu Keranjang
menu() → belongsTo()	Menu	menu_id	Item mengacu pada satu Menu
6.Pesanan
• Berkas: app/Models/Pesanan.php
• Status Commit: Ter-commit
Implementasikan $fillable (termasuk foreign key) dan method belongsTo() pada model tersebut di repositori, lalu rangkum relasinya di sini:
Method Relasi	Target Model	Foreign Key	Keterangan
customer() → belongsTo()	Customer	customer_id	Pesanan dibuat oleh satu Customer
kasir() → belongsTo()	Kasir	kasir_id	Pesanan diproses oleh Kasir

 
7.PesananItem
• Berkas: app/Models/PesananItem.php
• Status Commit: Ter-commit
Implementasikan $fillable (termasuk foreign key) dan method belongsTo() pada model tersebut di repositori, lalu rangkum relasinya di sini:
Method Relasi	Target Model	Foreign Key	Keterangan
pesanan() → belongsTo()	Pesanan	pesanan_id	Item merupakan bagian dari satu Pesanan
menu() → belongsTo()	Menu	menu_id	Item mengacu pada satu Menu

4.4 Ringkasan Relasi Seluruh Model
Setelah semua model selesai, lengkapi tabel ringkasan berikut:
Model	Method Relasi	Target Model	Jenis Relasi Eloquent
User	customer()	Customer	hasOne
User	kasir()	Kasir	hasOne
User	admin()	Admin	hasOne
Customer	user()	User	belongsTo
Customer	keranjang()	Keranjang	hasOne
Customer	pesanans()	Pesanan	hasMany
Kasir	user()	User	belongsTo
Kasir	pesanans()	Pesanan	hasMany
Admin	user()	User	belongsTo
Menu	keranjangItems()	KeranjangItem	hasMany
Menu	pesananItems()	PesananItem	hasMany
Keranjang	customer()	Customer	belongsTo
Keranjang	items()	KeranjangItem	hasMany
KeranjangItem	keranjang()	Keranjang	belongsTo
KeranjangItem	menu()	Menu	belongsTo
Pesanan	customer()	Customer	belongsTo
Pesanan	kasir()	Kasir	belongsTo
Pesanan	items()	PesananItem	hasMany
PesananItem	pesanan()	Pesanan	belongsTo
PesananItem	menu()	Menu	belongsTo

TAHAP 5: FACTORY & DATABASE SEEDER

5.1 Berkas Factory & Seeder di Repositori
Jalankan make:factory dan make:seeder pada terminal proyek, lalu catat berkas yang dihasilkan:
No	Berkas	Jenis	Model / Tabel Sasaran	Commit
1	database/factories/MenuFactory.php	Factory	Menu	Ter-comit
2	database/factories/CustomerFactory.php	Factory	Customer	Ter-comit
3	database/seeders/UserSeeder.php	Seeder	User	Ter-comit
4	database/seeders/MenuSeeder.php	Seeder	Menu	Ter-comit

5.2 Rancangan Factory
• Berkas: database/factories/MenuFactory.php
• Status Commit: Ter-commit
Implementasikan method definition() pada berkas factory di repositori (contoh helper: fake()->name(), fake()->unique()->safeEmail()). Rangkum pemetaan kolomnya di sini:
Alasan pemilihan helper fake() yang digunakan:
Kolom	Helper Faker	Alasan Pemilihan
nama_menu	fake()->words(2, true)	Menghasilkan nama menu secara acak
foto	'menu/default.jpg'	Menggunakan path foto default
harga	fake()->numberBetween(5000, 30000)	Menghasilkan harga menu dalam rentang yang sesuai
deskripsi	fake()->sentence()	Menghasilkan deskripsi singkat
ketersediaan	TRUE	Menentukan menu dalam kondisi tersedia
• Berkas: database/factories/CustomerFactory.php
• Status Commit: Ter-commit
Implementasikan method definition() pada berkas factory di repositori (contoh helper: fake()->name(), fake()->unique()->safeEmail()). Rangkum pemetaan kolomnya di sini:
Alasan pemilihan helper fake() yang digunakan:
Kolom	Helper Faker	Alasan Pemilihan
user_id	User::factory()	Membuat User yang menjadi induk Customer
nim	fake()->unique()->numerify('##########')	Menghasilkan NIM angka yang unik
kelas	fake()->randomElement(['TI-A','TI-B','TI-C'])	Menghasilkan kelas secara acak

5.3 Rancangan Seeder
• Berkas: database/seeders/UserSeeder.php
• Status Commit: Ter-commit
Seeder diisi pada repositori (data master statis lewat Model::create([...]), data acak lewat Model::factory(n)->create()). Catat sumber dan volume data dummy yang dihasilkan:
Model	Sumber Data	Jumlah Baris	Keterangan
User	Model::create([...]) — data statis	3	Membuat akun data master tetap (1 Admin, 1 Kasir, dan 1 Customer).
Customer	Model::factory(n)->create()	10	Menggenerasi 10 data dummy mahasiswa/customer secara otomatis beserta akun User-nya.
Menu	Model::factory(n)->create()	15	Menggenerasi 15 data dummy makanan dan minuman acak untuk katalog kantin.
5.4 Urutan Pemanggilan pada DatabaseSeeder.php
• Berkas: database/seeders/DatabaseSeeder.php
• Status Commit: Ter-commit
Catat urutan pemanggilan seeder pada berkas tersebut (urutan harus menghormati relasi induk → anak):
Urutan	Seeder Class	Tujuan Data
1	UserSeeder::class	Mengisi data pengguna utama (Admin, Kasir, Customer Naufal) serta 10 data Customer acak beserta relasi akun users-nya.
2	MenuSeeder::class	Mengisi data 15 item menu makanan/minuman ke dalam tabel menus.

TAHAP 6: EKSEKUSI, VERIFIKASI & PENGUJIAN BASIS DATA

6.1 Jalankan Fresh Migration + Seeding
Jalankan perintah berikut pada terminal proyek (yang disalin ke lembar kerja hanya ringkasan log, bukan kode program):
PERINTAH TERMINAL (ARTISAN)
php artisan migrate:fresh --seed

Tempelkan ringkasan output log terminal sebagai bukti eksekusi:
AREA JAWABAN — SILAKAN TULIS DI SINI
                                                                                                                                                       
C:\laragon\www\univ                                                                                                                                    
λ php artisan migrate:fresh --seed                                                                                                                                                                                                                                                                        
  Dropping all tables ................................................................................................................ 512.31ms DONE                                                                                                                                                   
   INFO  Preparing database.                                                                                                                                                                                                                                                                              
  Creating migration table ............................................................................................................ 60.05ms DONE                                                                                                                                                      
   INFO  Running migrations.                                                                                                                                                                                                                                                                       
  0001_01_01_000000_create_users_table ............................................................................................... 229.75ms DONE   
  0001_01_01_000001_create_cache_table ............................................................................................... 179.98ms DONE   
  0001_01_01_000002_create_jobs_table ................................................................................................ 245.19ms DONE   
  2026_09_22_040930_create_customers_table ........................................................................................... 228.08ms DONE   
  2026_09_22_040940_create_kasirs_table .............................................................................................. 220.42ms DONE   
  2026_09_22_040945_create_admins_table .............................................................................................. 190.09ms DONE   
  2026_09_22_040953_create_menus_table ................................................................................................ 34.97ms DONE   
  2026_09_22_041018_create_keranjangs_table .......................................................................................... 186.18ms DONE   
  2026_09_22_041026_create_keranjang_items_table ..................................................................................... 296.37ms DONE   
  2026_09_22_041033_create_pesanans_table ............................................................................................ 325.87ms DONE   
  2026_09_22_041039_create_pesanan_items_table ....................................................................................... 345.60ms DONE   
                                                                                                                                                                                                                                                                                                
   INFO  Seeding database.                                                                                                                             
                                                                                                                                                       
  Database\Seeders\UserSeeder .............................................................................................................. RUNNING   
  Database\Seeders\UserSeeder ........................................................................................................ 2,179 ms DONE   
                                                                                                                                                       
  Database\Seeders\MenuSeeder .............................................................................................................. RUNNING   
  Database\Seeders\MenuSeeder ........................................................................................................... 89 ms DONE   
                                                                                                                                                       


6.2 Tabel Checklist Verifikasi di DBMS
No	Nama Tabel	Status Migrasi	Jumlah Baris Data	Tipe Data Sesuai ERD
1	users	[   ] Berhasil	13	[   ] Ya
2	customers	[   ] Berhasil	11	[   ] Ya
3	kasirs	[   ] Berhasil	1	[   ] Ya
4	admins	[   ] Berhasil	1	[   ] Ya
5	menus	[   ] Berhasil	15	[   ] Ya
6	keranjangs	[   ] Berhasil	0	[   ] Ya
7	pesanans	[   ] Berhasil	0	[   ] Ya

6.3 Bukti Screenshot Verifikasi
No	Konten Screenshot	Nama File
1	Daftar tabel di DBMS
 	screenshots/db_tables_list.png
2	Contoh data dummy tabel master
 	screenshots/db_data_master.png
3	Contoh data dummy tabel transaksi
 	screenshots/db_data_transaksi.png

TAHAP 7: REFLEKSI DAN EVALUASI MANDIRI

1. Mengapa urutan timestamp file migrasi krusial saat mendefinisikan foreign key?
AREA JAWABAN — SILAKAN TULIS DI SINI
Hal ini krusial saat menggunakan foreign key karena:
Tabel induk harus ada lebih dulu: RDBMS (seperti MySQL) menolak pembuatan foreign key jika tabel/kolom induk yang dirujuk belum terbentuk.
Mencegah error: Jika timestamp tabel anak lebih awal dari tabel induk, eksekusi migrasi akan gagal (error Cannot add foreign key constraint).


VI. RUBRIK PENILAIAN PRAKTIKUM MANDIRI
No	Komponen Penilaian	Bobot	Kriteria Capaian Maksimal
1	Rancangan ERD & Integritas Relasi	20%	ERD lengkap, kardinalitas jelas, sesuai skenario proyek kelompok
2	Struktur File Migrasi & Constraint	25%	Tipe data presisi, nullability tepat, foreign key cascade rapi
3	Implementasi Model Eloquent	25%	$fillable aman, method relasi dua arah terdefinisi benar
4	Kualitas Factory & Seeder	15%	Data dummy bervariasi, faker helper sesuai konteks realistis
5	Verifikasi & Dokumentasi LKP	15%	LKP terisi lengkap (tanpa salinan kode), log eksekusi bersih, screenshot valid
	TOTAL	100%	

Catatan penilaian: Komponen 2–4 (migrasi, model, factory & seeder) dinilai langsung dari source code pada repositori Git kelompok (lihat Bagian I), bukan dari lembar kerja ini.
VII. PEMETAAN KE SUB-CPMK
Tahap Kegiatan	Sub-CPMK yang Dicapai
Tahap 1 — Identifikasi entitas & ERD	S2
Tahap 2 — Konfigurasi database	S4
Tahap 3 — Migrasi & skema tabel	S4
Tahap 4 — Model Eloquent & relasi	S2, S4
Tahap 5 — Factory & Seeder	S4
Tahap 6 — Verifikasi basis data	S4
Tahap 7 — Refleksi mandiri	S2, S4

VIII. TATA CARA PENGUMPULAN
Deadline: Pertemuan 5 (awal perkuliahan)
Yang dikumpulkan:
1. Repositori GitHub/GitLab — push seluruh source code (migrasi, model, factory, seeder, .env.example) ke branch aktif proyek; pastikan tautan pada Bagian I dapat diakses dosen/asisten pengampu
2. File lembar kerja ini (format .md atau PDF) — terisi lengkap sesuai pengerjaan mandiri; source code tidak dilampirkan pada lembar kerja
3. Folder screenshots/ di repositori — berisi ERD dan screenshot hasil verifikasi DBMS
Format nama file lembar kerja:
FORMAT / CONTOH PENAMAAN
LKP04_[NIM]_[NamaLengkap].md

Contoh: LKP04_12345678_BudiSantoso.md

Copyright © Edwin Hari Agus Prastyo, S.Kom., M.Kom. — Universitas Hasyim Asy'ari Tebuireng Jombang
