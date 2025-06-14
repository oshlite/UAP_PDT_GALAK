# GALAK: Game Katalog & Admin Log

Sistem web katalog game modern berbasis **PHP** & **MySQL** yang menampilkan, mengelola, dan memantau data game serta kategori secara dinamis. Proyek ini menonjolkan integrasi **stored procedure**, **function**, **trigger**, dan **transaction** di database, serta fitur backup otomatis untuk menjaga keamanan data.

---

## 📌 Detail Konsep & Tujuan
GALAK adalah aplikasi katalog game dengan fitur admin log, dibangun untuk mendemonstrasikan implementasi:
- **Stored Procedure** (otomasi proses penting)
- **Stored Function** (pengambilan data terpusat)
- **Trigger** (logging & validasi otomatis)
- **Transaction** (atomicity multi-tabel)
- **Backup Otomatis** (keamanan data)

Semua logika bisnis utama dipusatkan di database, sehingga konsistensi, reliabilitas, dan integritas data terjaga, sesuai prinsip Pemrosesan Data Terdistribusi.

---

## ⚠️ Disclaimer
Penerapan procedure, function, trigger, dan transaction pada GALAK disesuaikan dengan kebutuhan sistem katalog game. Implementasi pada sistem lain dapat berbeda sesuai arsitektur dan kebutuhan.

---

## 🧠 Stored Procedure
Stored procedure bertindak sebagai SOP database untuk operasi penting:
- `AddNewGame(p_title, p_genre, p_thumbnail, p_description)`
- `UpdateGameDescription(p_game_id, p_new_description)`
- `GetGameCountByGenre(genre_param, OUT game_count)`

**Implementasi di sistem:**
- `admin_game_form.php` (tambah game: panggil `AddNewGame`, edit deskripsi: panggil `UpdateGameDescription`)
- `hitung_game.php`, `katalog.php` (statistik genre: panggil `GetGameCountByGenre`)

**Contoh kode:**
```php
// Tambah game baru
$stmt = $conn->prepare("CALL AddNewGame(?, ?, ?, ?)");
$stmt->bind_param('ssss', $title, $genre, $thumbnail, $description);
$stmt->execute();
```

---

## 🚨 Trigger
Trigger digunakan untuk logging otomatis dan validasi data penting:
- `after_game_insert` & `after_game_delete` (log admin saat tambah/hapus game)
- `insert_kategori` & `update_kategori` (log admin saat tambah/edit kategori)

**Implementasi di sistem:**
- `admin_game_form.php`, `admin_game_list.php` (otomatis aktif saat insert/delete game)
- `admin_kategori_form.php` (otomatis aktif saat insert/update kategori)
- Semua log dapat dilihat di `admin_log_view.php`

**Contoh kode trigger:**
```sql
CREATE TRIGGER after_game_insert AFTER INSERT ON games FOR EACH ROW BEGIN
  INSERT INTO admin_log (admin_id, activity)
  VALUES (@admin_id, CONCAT('Menambahkan game baru: ', NEW.title));
END
```

---

## 🔄 Transaction
Transaksi memastikan operasi multi-tabel berjalan atomik. Jika salah satu langkah gagal, seluruh proses dibatalkan (rollback).

**Implementasi di sistem:**
- `admin_game_form.php` (insert kategori baru + game dalam satu transaksi)
- `admin_kategori_form.php` (insert/update kategori)

**Contoh kode:**
```php
$conn->begin_transaction();
// Insert kategori jika baru
// Insert game
$conn->commit();
// Jika gagal: $conn->rollback();
```

---

## 📺 Stored Function
Stored function digunakan untuk mengambil data spesifik secara efisien dan konsisten:
- `GetGameCountByKategoriId(kategori_id)` (jumlah game per kategori)
- `GetKategoriNameByGameId(game_id)` (nama kategori dari id game)
- `IsUserAdmin(user_id)` (cek role admin)

**Implementasi di sistem:**
- `admin_kategori_list.php`, `hitung_game.php`, `katalog.php` (jumlah game per kategori)
- `game_detail.php` (tampilkan nama kategori)
- `login.php`, `admin_dashboard.php` (validasi role admin setelah login)

**Contoh kode:**
```php
$stmt = $conn->prepare('SELECT IsUserAdmin(?) AS is_admin');
$stmt->bind_param('i', $user_id);
$stmt->execute();
```

---

## 🗂️ Struktur Fitur & Highlight Implementasi
| File | Fitur | Implementasi Ketentuan No.1 |
|------|-------|----------------------------|
| `admin_dashboard.php` | Ringkasan total game & kategori, validasi admin | Function: `IsUserAdmin` |
| `admin_game_form.php` | Tambah/edit game | Procedure: `AddNewGame`, `UpdateGameDescription`, Transaction, Trigger |
| `admin_game_list.php` | Daftar game, filter kategori, statistik genre | Procedure, Function, Trigger |
| `admin_kategori_list.php` | Daftar kategori, jumlah game per kategori | Function, Trigger |
| `admin_kategori_form.php` | Tambah/edit kategori | Trigger, Transaction |
| `admin_log_view.php` | Tabel log admin | Trigger |
| `game_detail.php` | Detail game, badge kategori | Function: `GetKategoriNameByGameId` |
| `katalog.php` | Katalog publik, filter kategori, statistik | Function, Procedure |
| `login.php` | Login user/admin, cek role | Function: `IsUserAdmin` |
| `hitung_game.php` | Fungsi PHP wrapper ke procedure/function | Procedure, Function |

---

## 💻 Contoh Kode Implementasi Ketentuan No.1 di Sistem GALAK (Lengkap dengan Penjelasan)

### 1. Stored Procedure
#### Pemanggilan AddNewGame (admin_game_form.php)
```php
// Menambah game baru ke database secara konsisten dan aman menggunakan procedure di MySQL.
$sql = "CALL AddNewGame(?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssss', $title, $genre, $thumbnail_to_save, $description);
$stmt->execute();
// Dengan procedure, logika insert terpusat di database, sehingga data lebih terjaga dan efisien.
```
#### Pemanggilan UpdateGameDescription (admin_game_form.php)
```php
// Mengupdate deskripsi game secara langsung di database via procedure.
$sql = "CALL UpdateGameDescription(?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $posted_id, $description);
$stmt->execute();
// Memastikan perubahan hanya terjadi jika data valid, dan mudah di-maintain.
```
#### Statistik Genre (hitung_game.php, katalog.php)
```php
// Menghitung jumlah game per genre dengan procedure agar hasil selalu akurat dan konsisten.
$sql_call = "CALL GetGameCountByGenre(?, @game_count)";
$stmt = $conn->prepare($sql_call);
$stmt->bind_param("s", $genre);
$stmt->execute();
$stmt->close();
$result = $conn->query("SELECT @game_count as count");
// Cocok untuk statistik real-time tanpa query manual berulang.
```

### 2. Stored Function
#### Cek Role Admin (login.php, admin_dashboard.php)
```php
// Mengecek apakah user adalah admin menggunakan function di MySQL.
$role_stmt = $conn->prepare('SELECT IsUserAdmin(?) AS is_admin');
$role_stmt->bind_param('i', $user['id']);
$role_stmt->execute();
$role_result = $role_stmt->get_result();
$is_admin = $role_result->fetch_assoc()['is_admin'];
// Function ini menjaga keamanan akses fitur admin, hanya user dengan role admin yang bisa masuk.
```
#### Jumlah Game per Kategori (admin_kategori_list.php, hitung_game.php, katalog.php)
```php
// Mengambil jumlah game dalam satu kategori dengan function di database.
$sql = "SELECT GetGameCountByKategoriId(?) AS jumlah";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $kategori_id);
$stmt->execute();
$result = $stmt->get_result();
$jumlah = $result->fetch_assoc()['jumlah'];
// Memudahkan pembuatan statistik kategori tanpa query rumit di PHP.
```
#### Nama Kategori dari Game (game_detail.php)
```php
// Mendapatkan nama kategori dari id game secara langsung dari database.
$stmt_kat = $conn->prepare("SELECT GetKategoriNameByGameId(?) AS kategori");
$stmt_kat->bind_param('i', $game_id);
$stmt_kat->execute();
$result_kat = $stmt_kat->get_result();
$kategori_func = $result_kat->fetch_assoc()['kategori'];
// Membuat tampilan detail game lebih informatif dan dinamis.
```

### 3. Trigger
#### Trigger Log Admin Otomatis (database_galak.sql)
```sql
-- Trigger ini otomatis mencatat aktivitas admin setiap kali ada game ditambah/hapus atau kategori diubah.
CREATE TRIGGER after_game_insert AFTER INSERT ON games FOR EACH ROW BEGIN
  INSERT INTO admin_log (admin_id, activity)
  VALUES (@admin_id, CONCAT('Menambahkan game baru: ', NEW.title));
END;

CREATE TRIGGER after_game_delete AFTER DELETE ON games FOR EACH ROW BEGIN
  INSERT INTO admin_log (admin_id, activity)
  VALUES (@admin_id, CONCAT('Menghapus game: ', OLD.title));
END;

CREATE TRIGGER insert_kategori AFTER INSERT ON kategori FOR EACH ROW BEGIN
  INSERT INTO admin_log (admin_id, activity)
  VALUES (@admin_id, CONCAT('Menambahkan kategori: ', NEW.name));
END;
-- Dengan trigger, semua perubahan penting otomatis tercatat tanpa perlu kode tambahan di aplikasi.
```

### 4. Transaction
#### Insert Kategori + Game (admin_game_form.php)
```php
// Transaksi ini memastikan jika salah satu proses gagal (insert kategori/game), maka semua perubahan dibatalkan.
$conn->begin_transaction();
try {
    // Insert kategori jika belum ada
    $kategori_stmt = $conn->prepare("SELECT id FROM kategori WHERE name = ? LIMIT 1");
    $kategori_stmt->bind_param('s', $genre);
    $kategori_stmt->execute();
    $kategori_result = $kategori_stmt->get_result();
    if ($kategori_result->num_rows === 0) {
        $desc_default = 'Kategori otomatis dari form game';
        $insert_kat = $conn->prepare("INSERT INTO kategori (name, description) VALUES (?, ?)");
        $insert_kat->bind_param('ss', $genre, $desc_default);
        $insert_kat->execute();
    }
    // Insert game
    $sql = "CALL AddNewGame(?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $title, $genre, $thumbnail_to_save, $description);
    $stmt->execute();
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
}
// Transaction menjaga data tetap konsisten, tidak ada data setengah jalan jika terjadi error.
```

### 5. Backup Database + Task Scheduler
#### Script Backup Otomatis (Windows)
```bat
@echo off
set DATE=%date:~10,4%-%date:~4,2%-%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
mysqldump -u root database_galak > D:\AppData\Laragon\www\GALAK\storage\backups\galak_backup_%DATE%.sql
:: Script ini akan membuat file backup database otomatis dengan nama sesuai tanggal dan jam.
:: Jalankan script ini dengan Task Scheduler Windows agar backup berjalan rutin tanpa perlu manual.
```
- Dengan backup otomatis, data tetap aman walau terjadi error, kehilangan data, atau kerusakan server.

---

## 🛡️ Backup Otomatis
Backup database dilakukan secara berkala menggunakan `mysqldump` dan task scheduler (Windows Task Scheduler atau cron). File backup disimpan di folder `storage/backups` dengan format timestamp.

**Contoh script backup:**
```bat
@echo off
set DATE=%date:~10,4%-%date:~4,2%-%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
mysqldump -u root database_galak > D:\AppData\Laragon\www\GALAK\storage\backups\galak_backup_%DATE%.sql
```

---

## 🧩 Relevansi dengan Pemrosesan Data Terdistribusi
- **Konsistensi:** Semua operasi penting (insert, update, delete) dijalankan via procedure/function/trigger di database.
- **Reliabilitas:** Trigger dan transaction memastikan data tetap valid meski ada error.
- **Integritas:** Logika bisnis utama tersentral di database, sehingga tetap konsisten walau diakses dari banyak sumber (web, API, dsb).

---

## 💡 Cara Menjalankan
1. Import `database_galak.sql` ke MySQL.
2. Pastikan folder `uploads/` dan `storage/backups/` ada dan writable.
3. Jalankan aplikasi di localhost (disarankan Laragon/XAMPP).
4. Login sebagai admin/user (lihat data awal di tabel `users`).
5. Untuk backup otomatis, atur task scheduler sesuai script di atas.

---

## 👨‍💻 Tim & Kontribusi
- Proyek UAP Pemrosesan Data Terdistribusi
- Kelompok 17 Kelas CD

---

## 📚 Referensi
- [Dokumentasi MySQL: Stored Procedure & Function](https://dev.mysql.com/doc/refman/8.0/en/stored-programs-defining.html)
- [PHP MySQLi Manual](https://www.php.net/manual/en/book.mysqli.php)
- [Tailwind CSS](https://tailwindcss.com/)

---

**GALAK** – (Game Asyik Login Akses Katalog) Game Katalog & Admin Log, contoh aplikasi modern dengan integrasi penuh fitur database terdistribusi.
