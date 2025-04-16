<?php
require_once 'config.php';
require_once 'auth.php';

// خروج ادمین
logoutAdmin();

// هدایت به صفحه ورود
header("Location: admin_login.php");
exit(); 