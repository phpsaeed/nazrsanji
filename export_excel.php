<?php
require_once 'config.php';
require_once 'auth.php';
require 'vendor/autoload.php';

// بررسی دسترسی ادمین
requireAdmin();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$survey_id = $_GET['id'];

try {
    // دریافت اطلاعات نظرسنجی
    $stmt = $pdo->prepare("SELECT * FROM surveys WHERE id = ?");
    $stmt->execute([$survey_id]);
    $survey = $stmt->fetch();

    if (!$survey) {
        header("Location: index.php");
        exit();
    }

    // دریافت سوالات
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE survey_id = ?");
    $stmt->execute([$survey_id]);
    $questions = $stmt->fetchAll();

    // دریافت تعداد کل پاسخ‌دهندگان منحصر به فرد
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT link_code) as total_respondents FROM answers WHERE survey_id = ?");
    $stmt->execute([$survey_id]);
    $total_respondents = $stmt->fetch()['total_respondents'];

    // ایجاد فایل Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // تنظیم عنوان نظرسنجی
    $sheet->setCellValue('A1', $survey['title']);
    $sheet->mergeCells('A1:D1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // تنظیم تعداد پاسخ‌دهندگان
    $sheet->setCellValue('A2', 'تعداد کل پاسخ‌دهندگان: ' . $total_respondents);
    $sheet->mergeCells('A2:D2');
    $sheet->getStyle('A2')->getFont()->setBold(true);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // شروع از سطر 4
    $row = 4;

    foreach ($questions as $question) {
        // عنوان سوال
        $sheet->setCellValue('A' . $row, $question['question_text']);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $row++;

        // توضیحات سوال
        if ($question['has_description'] && !empty($question['description'])) {
            $sheet->setCellValue('A' . $row, $question['description']);
            $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $row++;
        }

        switch ($question['question_type']) {
            case 'text':
                // دریافت پاسخ‌های متنی
                $stmt = $pdo->prepare("SELECT answer_text FROM answers WHERE survey_id = ? AND question_id = ?");
                $stmt->execute([$survey_id, $question['id']]);
                $text_answers = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $sheet->setCellValue('A' . $row, 'پاسخ‌ها:');
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $row++;

                foreach ($text_answers as $answer) {
                    $sheet->setCellValue('A' . $row, $answer);
                    $sheet->mergeCells('A' . $row . ':D' . $row);
                    $row++;
                }
                break;

            case 'radio':
            case 'checkbox':
                $options = json_decode($question['options'], true);
                $option_counts = [];

                foreach ($options as $option) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM answers WHERE survey_id = ? AND question_id = ? AND answer_text LIKE ?");
                    $stmt->execute([$survey_id, $question['id'], '%' . $option . '%']);
                    $count = $stmt->fetchColumn();
                    $option_counts[$option] = $count;
                }

                asort($option_counts);

                $sheet->setCellValue('A' . $row, 'گزینه');
                $sheet->setCellValue('B' . $row, 'تعداد');
                $sheet->setCellValue('C' . $row, 'درصد');
                $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
                $row++;

                foreach ($option_counts as $option => $count) {
                    $percentage = $total_respondents > 0 ? round(($count / $total_respondents) * 100) : 0;
                    $sheet->setCellValue('A' . $row, $option);
                    $sheet->setCellValue('B' . $row, $count);
                    $sheet->setCellValue('C' . $row, $percentage . '%');
                    $row++;
                }
                break;
        }

        // اضافه کردن یک سطر خالی بین سوالات
        $row++;
    }

    // تنظیم عرض ستون‌ها
    $sheet->getColumnDimension('A')->setWidth(40);
    $sheet->getColumnDimension('B')->setWidth(15);
    $sheet->getColumnDimension('C')->setWidth(15);

    // تنظیم جهت راست به چپ
    $sheet->setRightToLeft(true);

    // تنظیمات فایل
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="survey_results.xlsx"');
    header('Cache-Control: max-age=0');

    // ذخیره فایل
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch(PDOException $e) {
    error_log("خطا در ایجاد فایل Excel: " . $e->getMessage());
    die("خطا در ایجاد فایل Excel: " . $e->getMessage());
}
?> 