<?php
require_once 'config.php';

// بررسی وجود پارامترهای لازم
if (!isset($_GET['id']) || !isset($_GET['code'])) {
    header("Location: index.php");
    exit();
}

$survey_id = $_GET['id'];
$link_code = $_GET['code'];

try {
    // بررسی اعتبار لینک
    $stmt = $pdo->prepare("SELECT * FROM survey_links WHERE survey_id = ? AND link_code = ? AND is_used = 0");
    $stmt->execute([$survey_id, $link_code]);
    $link = $stmt->fetch();
    
    if (!$link) {
        // اگر لینک نامعتبر باشد، به صفحه تشکر هدایت می‌شود
        header("Location: thank_you.php?id=" . $survey_id);
        exit();
    }
    
    // هدایت مستقیم به صفحه نظرسنجی
    header("Location: view_survey.php?id=" . $survey_id . "&code=" . $link_code);
    exit();
} catch(PDOException $e) {
    error_log("خطا در بررسی لینک نظرسنجی: " . $e->getMessage());
    header("Location: thank_you.php?id=" . $survey_id);
    exit();
} 