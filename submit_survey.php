<?php
session_start();
require_once 'config.php';

// فعال کردن نمایش خطاها
error_reporting(E_ALL);
ini_set('display_errors', 1);

// اطمینان از عدم خروجی قبل از هدرها
ob_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $survey_id = $_POST['survey_id'];
    $link_code = $_POST['link_code'];
    $answers = $_POST['answers'] ?? [];

    try {
        // شروع تراکنش
        $pdo->beginTransaction();

        // بررسی اعتبار لینک
        $stmt = $pdo->prepare("SELECT * FROM survey_links WHERE survey_id = ? AND link_code = ? AND is_used = 0");
        $stmt->execute([$survey_id, $link_code]);
        $link = $stmt->fetch();

        if (!$link) {
            throw new Exception("این لینک نامعتبر است یا قبلاً استفاده شده است.");
        }

        // علامت‌گذاری لینک به عنوان استفاده شده
        $stmt = $pdo->prepare("UPDATE survey_links SET is_used = 1 WHERE survey_id = ? AND link_code = ?");
        $stmt->execute([$survey_id, $link_code]);

        // ذخیره پاسخ‌ها
        foreach ($answers as $question_id => $answer) {
            if (is_array($answer)) {
                // برای سوالات چند انتخابی
                $answer_text = json_encode($answer);
            } else {
                // برای سوالات تک انتخابی و متنی
                $answer_text = $answer;
            }

            $stmt = $pdo->prepare("INSERT INTO answers (survey_id, question_id, answer_text, link_code) VALUES (?, ?, ?, ?)");
            $stmt->execute([$survey_id, $question_id, $answer_text, $link_code]);
        }

        // ثبت تراکنش
        $pdo->commit();

        // انتقال به صفحه تشکر
        header("Location: thank_you.php");
        exit();

    } catch (Exception $e) {
        // در صورت خطا، برگرداندن تراکنش
        $pdo->rollBack();
        error_log("خطا در ثبت پاسخ‌ها: " . $e->getMessage());
        die("خطا در ثبت پاسخ‌ها: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit();
} 