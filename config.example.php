<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'your_db_name');

// Site Configuration
define('SITE_URL', 'http://localhost');
define('SITE_NAME', 'NeonVox');
define('JWT_SECRET', 'change-this-to-a-random-secret-key');

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_UPLOAD_SIZE', 100 * 1024 * 1024); // 100MB
