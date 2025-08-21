<?php
// config.php - File konfigurasi
define('DB_HOST', 'localhost');
define('DB_NAME', 'innobi_worship_leader');
define('DB_USER', 'innobi_wlapp');
define('DB_PASS', 'Project789'); 
define('JWT_SECRET', 'It-Project1'); 
define('TOKEN_EXPIRY', 86400); // Masa berlaku token: 24 jam dalam detik

// Error reporting untuk debugging. Matikan di produksi.
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
?>