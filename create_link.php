<?php
require_once 'config.php';
require_once 'auth.php';

// بررسی دسترسی ادمین
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $survey_id = $_POST['survey_id'];
    $user_name = $_POST['user_name'];
    $link_code = $_POST['link_code'];
    
    try {
        // بررسی تکراری نبودن کد لینک
        $stmt = $pdo->prepare("SELECT id FROM survey_links WHERE link_code = ?");
        $stmt->execute([$link_code]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "این کد لینک قبلاً استفاده شده است.";
            header("Location: manage_links.php");
            exit();
        }
        
        // ایجاد لینک جدید
        $stmt = $pdo->prepare("INSERT INTO survey_links (survey_id, link_code, user_name) VALUES (?, ?, ?)");
        $stmt->execute([$survey_id, $link_code, $user_name]);
        
        $_SESSION['success'] = "لینک با موفقیت ایجاد شد.";
        header("Location: manage_links.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "خطا در ایجاد لینک: " . $e->getMessage();
        header("Location: manage_links.php");
        exit();
    }
} else {
    header("Location: manage_links.php");
    exit();
} 