<?php
require_once 'config.php';

try {
    // شروع تراکنش
    $pdo->beginTransaction();

    // ایجاد نظرسنجی جدید
    $stmt = $pdo->prepare("INSERT INTO surveys (title, description) VALUES (?, ?)");
    $stmt->execute(['نظرسنجی رضایت مشتری', 'لطفاً به سوالات زیر پاسخ دهید']);
    $survey_id = $pdo->lastInsertId();

    // ایجاد سوالات
    $questions = [
        [
            'question_text' => 'میزان رضایت شما از خدمات ما چقدر است؟',
            'question_type' => 'radio',
            'options' => json_encode(['خیلی کم', 'کم', 'متوسط', 'زیاد', 'خیلی زیاد'])
        ],
        [
            'question_text' => 'کدام ویژگی‌های محصول ما را می‌پسندید؟',
            'question_type' => 'checkbox',
            'options' => json_encode(['کیفیت', 'قیمت', 'طراحی', 'کارایی', 'پشتیبانی'])
        ],
        [
            'question_text' => 'پیشنهادات شما برای بهبود خدمات چیست؟',
            'question_type' => 'text',
            'options' => null
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO questions (survey_id, question_text, question_type, options) VALUES (?, ?, ?, ?)");
    foreach ($questions as $question) {
        $stmt->execute([$survey_id, $question['question_text'], $question['question_type'], $question['options']]);
    }

    // ایجاد لینک نظرسنجی
    $stmt = $pdo->prepare("INSERT INTO survey_links (survey_id, link_code, user_name) VALUES (?, ?, ?)");
    $stmt->execute([$survey_id, '1001', 'کاربر نمونه']);

    // تایید تراکنش
    $pdo->commit();

    echo "نظرسنجی نمونه با موفقیت ایجاد شد!";
    echo "<br>شناسه نظرسنجی: " . $survey_id;
    echo "<br>کد لینک: 1001";
    echo "<br><a href='index.php'>بازگشت به صفحه اصلی</a>";

} catch(PDOException $e) {
    // در صورت خطا، تراکنش را برگردان
    $pdo->rollBack();
    echo "خطا در ایجاد نظرسنجی: " . $e->getMessage();
} 