# Setup Instructions - Worship Leader Assistant

## Prerequisites
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)

## Langkah-langkah Setup

### 1. Database Setup
```bash
# Import struktur database
mysql -u root -p < innobi_worship_leader.sql

# Import data awal
mysql -u root -p < seed_data.sql
```

### 2. Konfigurasi Database
Edit file `config.php` dan sesuaikan dengan konfigurasi database Anda:
```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'innobi_worship_leader');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('JWT_SECRET', 'your_jwt_secret_key');
?>
```

### 3. Generate Password Hash
Jalankan script untuk menghasilkan password hash yang benar:
```bash
php generate_password.php
```

Copy output SQL dan jalankan di database untuk update password admin dan user.

### 4. File Permissions
Pastikan folder memiliki permission yang benar:
```bash
chmod 755 .
chmod 644 *.php *.html *.sql
```

### 5. Web Server Configuration
Pastikan web server dapat mengakses file PHP dan HTML.

## Testing

### 1. Akses Aplikasi
- Buka browser dan akses `http://localhost/your-folder/`
- Akan muncul halaman login

### 2. Login Credentials
- **Admin**: `admin` / `admin123`
- **User**: `user` / `user123`

### 3. Fitur yang Tersedia
- **User**: Melihat lagu, membuat komposisi, menyimpan komposisi
- **Admin**: Semua fitur user + manajemen user, lagu, dan melihat statistik

## Troubleshooting

### Error Database Connection
- Periksa konfigurasi di `config.php`
- Pastikan database sudah dibuat dan dapat diakses
- Periksa username dan password database

### Error 404 pada API
- Pastikan web server mendukung URL rewriting
- Periksa file `.htaccess` (jika menggunakan Apache)
- Pastikan `api.php` dapat diakses

### Error Authentication
- Jalankan script `generate_password.php` untuk update password
- Periksa JWT_SECRET di `config.php`
- Clear browser cache dan localStorage

### Error CORS
- Pastikan header CORS sudah dikonfigurasi di `api.php`
- Periksa apakah frontend dan backend berjalan di domain yang sama

## Development

### Struktur File
```
├── api.php              # API endpoint utama
├── config.php           # Konfigurasi database
├── Database.php         # Kelas koneksi database
├── Auth.php             # Kelas autentikasi
├── index.html           # Frontend user
├── admin.html           # Frontend admin
├── innobi_worship_leader.sql  # Struktur database
├── seed_data.sql        # Data awal
├── generate_password.php # Script generate password
└── README.md            # Dokumentasi
```

### API Endpoints
Semua endpoint API tersedia di `api.php` dengan format:
- `POST /auth/login` - Login
- `GET /songs` - Ambil lagu
- `POST /compositions` - Buat komposisi
- Dan lainnya (lihat README.md)

### Database Schema
- `users` - Data pengguna
- `songs` - Data lagu
- `compositions` - Data komposisi
- `composition_songs` - Relasi komposisi-lagu
- `activity_logs` - Log aktivitas
- `sessions` - Manajemen session

## Security Notes
- Ganti JWT_SECRET dengan key yang unik
- Ganti password default admin dan user
- Aktifkan HTTPS di produksi
- Batasi akses file sensitif
- Backup database secara berkala
