<?php
// config.php - File konfigurasi
define('DB_HOST', 'localhost');
define('DB_NAME', 'innobi_worship_leader');
define('DB_USER', 'innobi_wlapp'); // ⚠️ Ganti dengan username database Anda
define('DB_PASS', 'Project789'); // ⚠️ Ganti dengan password database Anda
define('JWT_SECRET', 'It-Project1'); // ⚠️ Ganti dengan kunci rahasia yang kuat
define('TOKEN_EXPIRY', 86400); // Masa berlaku token: 24 jam dalam detik

// Error reporting untuk debugging. Matikan di produksi.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>