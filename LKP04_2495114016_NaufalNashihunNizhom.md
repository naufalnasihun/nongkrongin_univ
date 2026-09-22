# LEMBAR KERJA PRAKTIKUM (LKP) 04
## Database Design & Relational Model — Sistem Pemesanan Kantin

---

### IDENTITAS MAHASISWA

| Field | Keterangan |
|-------|------------|
| **NIM** | 2495114016 |
| **Nama Lengkap** | Naufal Nashihun Nizhom |
| **Kelas** | Karyawan / V |
| **Mata Kuliah** | Pemrograman Web Lanjut |
| **Tanggal Pengerjaan** | 22 September 2026 |

---

## BAGIAN I — TAUTAN REPOSITORI

> **Tautan Repositori GitHub/GitLab:**
> *(Catatan: Setelah push dan setup remote, isi URL repositori di sini. Pastikan repositori bersifat **public** atau dosen/asisten sudah diundang sebagai collaborator.)*

**Struktur repositori yang di-push:**
```
univ/
├── app/Models/              # 9 model Eloquent (User, Admin, Kasir, Customer, Menu,
│                            #   Keranjang, KeranjangItem, Pesanan, PesananItem)
├── database/
│   ├── migrations/          # 12 file migration (users, cache, jobs + 9 tabel domain)
│   ├── factories/           # 3 factory (UserFactory, CustomerFactory, MenuFactory)
│   └── seeders/             # 3 seeder (DatabaseSeeder, UserSeeder, MenuSeeder)
├── screenshots/             # ERD + screenshot verifikasi DBMS
│   ├── erd_proyek.png
│   ├── db_tables_list.png
│   ├── db_data_master.png
│   └── db_data_transaksi.png
├── .env.example
└── LKP04_2495114016_NaufalNashihunNizhom.md  # Lembar kerja ini
```

---

## BAGIAN II — PERANCANGAN SISTEM

### 2.1 Deskripsi Sistem

Sistem yang dikembangkan adalah **Sistem Pemesanan Kantin (Kantin Online)** yang menangani tiga peran pengguna (role-based) dengan alur transaksi pemesanan makanan/minuman secara digital.

**Tiga peran (actor) dalam sistem:**
1.  **Admin** — mengelola data master (menu, user, laporan)
2.  **Kasir** — memproses pesanan, memvalidasi pembayaran, mengubah status pesanan
3.  **Customer (Mahasiswa)** — melihat menu, menambah item ke keranjang, membuat pesanan

### 2.2 Entity Relationship Diagram (ERD)

> *(Screenshot ERD terlampir pada folder `screenshots/erd_proyek.png`)*

**Ringkasan Entitas dan Relasi:**

| Entitas | Atribut Utama | Relasi |
|---------|---------------|--------|
| **users** | id, name, email, password | 1:1 dengan admin / kasir / customer |
| **admins** | id_admin, nama, user_id | BelongsTo → users |
| **kasirs** | id_kasir, nama, user_id | BelongsTo → users; HasMany → pesanans |
| **customers** | nim, nama, kelas, user_id | BelongsTo → users; HasOne → keranjangs; HasMany → pesanans |
| **menus** | nama_menu, foto, harga, deskripsi, ketersediaan | Independent entity; dipakai di keranjang_items & pesanan_items |
| **keranjangs** | customer_id | BelongsTo → customers; HasMany → keranjang_items |
| **keranjang_items** | keranjang_id, menu_id, jumlah, subtotal | BelongsTo → keranjangs & menus |
| **pesanans** | nomor_pesanan, tanggal, total_harga, status, customer_id, kasir_id | BelongsTo → customers & kasirs; HasMany → pesanan_items |
| **pesanan_items** | pesanan_id, menu_id, jumlah, harga, subtotal | BelongsTo → pesanans & menus |

**Kardinalitas Utama:**
- `users` 1 — 1 `admins/kasirs/customers` (satu user = satu peran spesifik)
- `customers` 1 — 1 `keranjangs` (setiap customer punya 1 keranjang aktif)
- `customers` 1 — N `pesanans` (satu customer bisa banyak pesanan)
- `kasirs` 0..1 — N `pesanans` (kasir bisa memproses banyak pesanan)
- `keranjangs` 1 — N `keranjang_items`
- `pesanans` 1 — N `pesanan_items`
- `menus` 1 — N `keranjang_items` / `pesanan_items`

---

## BAGIAN III — IMPLEMENTASI DATABASE (MIGRATIONS)

### 3.1 Daftar File Migration

| No | Nama File Migration | Tujuan |
|----|--------------------|--------|
| 1 | `0001_01_01_000000_create_users_table.php` | Tabel `users`, `password_reset_tokens`, `sessions` |
| 2 | `0001_01_01_000001_create_cache_table.php` | Tabel `cache`, `cache_locks` (framework) |
| 3 | `0001_01_01_000002_create_jobs_table.php` | Tabel `jobs`, `job_batches`, `failed_jobs` (queue) |
| 4 | `2026_09_22_040930_create_customers_table.php` | Tabel `customers` |
| 5 | `2026_09_22_040940_create_kasirs_table.php` | Tabel `kasirs` |
| 6 | `2026_09_22_040945_create_admins_table.php` | Tabel `admins` |
| 7 | `2026_09_22_040953_create_menus_table.php` | Tabel `menus` |
| 8 | `2026_09_22_041018_create_keranjangs_table.php` | Tabel `keranjangs` |
| 9 | `2026_09_22_041026_create_keranjang_items_table.php` | Tabel `keranjang_items` |
| 10 | `2026_09_22_041033_create_pesanans_table.php` | Tabel `pesanans` |
| 11 | `2026_09_22_041039_create_pesanan_items_table.php` | Tabel `pesanan_items` |

### 3.2 Strategi Foreign Key & Constraint

- **CascadeOnDelete:** Digunakan pada `user_id` (admin/kasir/customer) dan relasi item-master; jika data induk dihapus, data turunan ikut terhapus.
- **NullOnDelete:** Digunakan pada `kasir_id` di `pesanans`; jika akun kasir dihapus, riwayat pesanan tetap ada dengan `kasir_id = NULL`.
- **Unique Constraint:** `user_id` di admin/kasir/customer (1:1), `nim` di customers, `id_admin`, `id_kasir`, `nomor_pesanan`, `email` di users.
- **Precision Decimal:** `harga`, `subtotal`, `total_harga` menggunakan `decimal(12,2)`.

---

## BAGIAN IV — IMPLEMENTASI MODEL ELOQUENT

### 4.1 Daftar Model dan Relasi

| Model | Trait | Fillable | Relasi |
|-------|-------|----------|--------|
| **User** | HasFactory, Notifiable | name, email, password | hasOne → Admin, Kasir, Customer |
| **Admin** | — | user_id, id_admin, nama | belongsTo → User |
| **Kasir** | — | user_id, id_kasir, nama | belongsTo → User; hasMany → Pesanan |
| **Customer** | HasFactory | user_id, nim, nama, kelas | belongsTo → User; hasOne → Keranjang; hasMany → Pesanan |
| **Menu** | HasFactory | nama_menu, foto, harga, deskripsi, ketersediaan | Independent (ditarik via pivot) |
| **Keranjang** | HasFactory | customer_id | belongsTo → Customer; hasMany → KeranjangItem |
| **KeranjangItem** | HasFactory | keranjang_id, menu_id, jumlah, subtotal | belongsTo → Keranjang, Menu |
| **Pesanan** | HasFactory | customer_id, kasir_id, nomor_pesanan, tanggal, total_harga, status | belongsTo → Customer, Kasir; hasMany → PesananItem |
| **PesananItem** | HasFactory | pesanan_id, menu_id, jumlah, harga, subtotal | belongsTo → Pesanan, Menu |

### 4.2 Casts Terpakai

- `User.password` → `hashed`
- `Pesanan.tanggal` → `datetime`
- `Pesanan.total_harga` → `decimal:2`
- `KeranjangItem.subtotal` → `decimal:2`
- `PesananItem.harga` & `.subtotal` → `decimal:2`

---

## BAGIAN V — FACTORY DAN SEEDER

### 5.1 Factory

| Factory | Output / Logika |
|---------|----------------|
| **UserFactory** | `name`, `email` unique, `password` di-hash (`'password'`), `email_verified_at = now()` |
| **CustomerFactory** | Relasi `user_id → User::factory()`, `nim` numerify `2495114###`, kelas acak (TI-A/B/C, Karyawan) |
| **MenuFactory** | `nama_menu` 2 kata, `harga` 5.000–30.000, `ketersediaan = true` |

### 5.2 Seeder

#### UserSeeder (Data Statis + Factory)
1.  **Akun Admin Statis:**
    - User: `Admin Kantin` / `admin@gmail.com` / `password`
    - Admin: `ADM001` / `Admin Utama`
2.  **Akun Kasir Statis:**
    - User: `Kasir Kantin` / `kasir@gmail.com` / `password`
    - Kasir: `KSR001` / `Kasir Utama`
3.  **Akun Customer Statis:**
    - User: `Naufal Nashihun Nizhom` / `naufal@gmail.com` / `password`
    - Customer: `2495114016` / `Naufal Nashihun Nizhom` / `Karyawan / V`
4.  **10 Customer Acak** via `Customer::factory(10)`.

#### MenuSeeder
- `Menu::factory(15)` → 15 data menu dummy.

#### DatabaseSeeder
- Memanggil `[UserSeeder::class, MenuSeeder::class]`.

---

## BAGIAN VI — VERIFIKASI DATABASE

> *(Screenshot hasil verifikasi DBMS ada pada folder `screenshots/`:)*
> - `db_tables_list.png` — Daftar seluruh tabel di DB setelah migrate
> - `db_data_master.png` — Isi tabel master (users, admins, kasirs, customers, menus) setelah seeder
> - `db_data_transaksi.png` — Contoh data transaksi (keranjangs, keranjang_items, pesanans, pesanan_items)

### 6.1 Checklist Verifikasi

| Checklist | Status |
|-----------|--------|
| Semua 11 migration berhasil dijalankan (`php artisan migrate`) | ✅ |
| Foreign key terbentuk sesuai constraint (cascade/null) | ✅ |
| `php artisan db:seed` berjalan tanpa error | ✅ |
| 1 Admin + 1 Kasir + 11 Customer (1 statis + 10 factory) masuk | ✅ |
| 15 Menu dummy masuk via MenuFactory | ✅ |
| Akun statis bisa login dengan password `password` | ✅ |
| `nomor_pesanan` unique, `nim` unique, `email` unique enforced | ✅ |

### 6.2 Environment (.env.example)

- `DB_CONNECTION=sqlite` — default menggunakan SQLite untuk kemudahan deployment dan testing.
- Konfigurasi MySQL disediakan dalam bentuk komentar untuk switch ke RDBMS production.
- `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, `CACHE_STORE=database`.

---

## BAGIAN VII — KESIMPULAN DAN CATATAN

1.  **Normalisasi:** Skema database mencapai 3NF — setiap field non-key bergantung penuh pada primary key, tidak ada transitive dependency (harga disalin ke `pesanan_items.harga` sebagai snapshot saat transaksi, dengan pola snapshot harga yang umum di sistem penjualan).
2.  **Integritas Referensial:** Foreign key + cascade/null policy menjaga agar tidak ada orphan record.
3.  **Extensibility:** Framework auth Laravel (`users`) dipisah dari profil peran (admin/kasir/customer) dengan 1:1 relation, memudahkan penambahan peran baru tanpa mengubah struktur user core.
4.  **Seeder Idempotent:** *Catatan tambahan:* UserSeeder saat ini menggunakan `create()` langsung sehingga jika di-run dua kali akan menimbulkan constraint violation. Untuk production dapat dibungkus dengan `firstOrCreate()` atau dijalankan sekali saat `migrate:fresh --seed`.
5.  **Deployment:** Repositori dikirim beserta `.env.example`, `composer.json/lock`, dan instruksi pada Bagian I untuk clone & setup di mesin evaluator.

---

## BAGIAN VIII — REFERENSI

- Dokumentasi resmi Laravel 13: https://laravel.com/docs/13.x
- Database Migration: https://laravel.com/docs/13.x/migrations
- Eloquent Relationships: https://laravel.com/docs/13.x/eloquent-relationships
- Database Testing (Factory & Seeder): https://laravel.com/docs/13.x/database-testing

---

*Lembar kerja ini diisi secara mandiri oleh Naufal Nashihun Nizhom (2495114016) sebagai bukti pengerjaan LKP04 — Pemrograman Web Lanjut.*
