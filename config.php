<?php

// تنظیمات دیتابیس
$db_host = 'localhost';
$db_username = "root";
$db_pass = '';
$db_name = 'khorasa4_Nazarsanje'; // اصلاح شد

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_username, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("خطا در اتصال به دیتابیس: " . $e->getMessage());
    die("خطا در اتصال به دیتابیس");
}

// تنظیمات سایت
define('SITE_URL', 'http://localhost/n1/');
define('SITE_NAME', 'سیستم نظرسنجی');

// تنظیمات فونت و رنگ‌ها
define('FONT_FAMILY', 'Vazir, Tahoma, Arial');
define('PRIMARY_COLOR', '#2196f3');
define('SECONDARY_COLOR', '#1976d2');
define('SUCCESS_COLOR', '#4CAF50');
define('DANGER_COLOR', '#dc3545');
define('WARNING_COLOR', '#ffc107');
define('INFO_COLOR', '#17a2b8');

// پیام‌های سیستم
define('SUCCESS_MESSAGE', 'عملیات با موفقیت انجام شد.');
define('ERROR_MESSAGE', 'خطا در انجام عملیات. لطفاً دوباره تلاش کنید.');
define('CONFIRM_MESSAGE', 'آیا از انجام این عملیات اطمینان دارید؟');

?>
