<?php
session_start();

// تنظیمات ادمین
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'kom133@'); // در محیط واقعی از رمز عبور قوی‌تر استفاده کنید

// بررسی وضعیت ورود ادمین
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// ورود ادمین
function loginAdmin($username, $password) {
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    return false;
}

// خروج ادمین
function logoutAdmin() {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
}

// بررسی دسترسی ادمین
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header("Location: admin_login.php");
        exit();
    }
} 