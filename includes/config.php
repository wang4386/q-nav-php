<?php
// 基础配置
define('BASE_URL', 'http://localhost/q-nav-php');
define('SITE_NAME', 'My Website Favorites');

// 数据库配置
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'qnav');
define('DB_USER', 'root');
define('DB_PASS', '');

// 安全配置
define('SESSION_NAME', 'QNAV_SESSION');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 300); // 5分钟

// 上传配置
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// 是否开启调试模式
define('DEBUG', true);

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
