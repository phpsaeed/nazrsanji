<?php
require_once 'config.php';
require_once 'auth.php';

// بررسی دسترسی ادمین
requireAdmin();

if (!isset($_GET['id'])) {
    header("Location: manage_links.php");
    exit();
}

$link_id = $_GET['id'];

try {
    // بررسی استفاده نشدن لینک
    $stmt = $pdo->prepare("SELECT is_used FROM survey_links WHERE id = ?");
    $stmt->execute([$link_id]);
    $link = $stmt->fetch();

    if (!$link) {
        $_SESSION['error'] = "لینک مورد نظر یافت نشد.";
        header("Location: manage_links.php");
        exit();
    }

    if ($link['is_used']) {
        $_SESSION['error'] = "این لینک قبلاً استفاده شده و قابل حذف نیست.";
        header("Location: manage_links.php");
        exit();
    }

    // حذف لینک
    $stmt = $pdo->prepare("DELETE FROM survey_links WHERE id = ?");
    $stmt->execute([$link_id]);

    $_SESSION['success'] = "لینک با موفقیت حذف شد.";
    header("Location: manage_links.php");
    exit();

} catch(PDOException $e) {
    error_log("خطا در حذف لینک: " . $e->getMessage());
    $_SESSION['error'] = "خطا در حذف لینک: " . $e->getMessage();
    header("Location: manage_links.php");
    exit();
} 