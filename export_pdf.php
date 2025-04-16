<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'vendor/autoload.php';

// بررسی دسترسی ادمین
requireAdmin();

use Dompdf\Dompdf;
use Dompdf\Options;

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
    
    // تنظیمات PDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Vazir');

    $dompdf = new Dompdf($options);

    // ساخت HTML برای PDF
    $html = '
    <!DOCTYPE html>
    <html dir="rtl" lang="fa">
    <head>
        <meta charset="UTF-8">
        <style>
            @font-face {
                font-family: Vazir;
                src: url("fonts/Vazir.ttf") format("truetype");
            }
            body {
                font-family: Vazir, sans-serif;
                padding: 20px;
                line-height: 1.6;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .question {
                margin-bottom: 20px;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
            }
            .option {
                margin: 5px 0;
                padding: 5px;
            }
            .progress-bar {
                background-color: #2196f3;
                height: 20px;
                border-radius: 10px;
                margin: 5px 0;
            }
            .percentage {
                text-align: left;
                font-size: 12px;
                color: #666;
            }
            .text-answer {
                background-color: #f8f9fa;
                padding: 10px;
                margin: 5px 0;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>نتایج نظرسنجی: ' . htmlspecialchars($survey['title']) . '</h1>
            <p>تعداد کل پاسخ‌دهندگان: ' . $total_respondents . ' نفر</p>
        </div>';

    foreach ($questions as $question) {
        $html .= '<div class="question">';
        $html .= '<h3>' . htmlspecialchars($question['question_text']) . '</h3>';
        
        if ($question['has_description'] && !empty($question['description'])) {
            $html .= '<p>' . htmlspecialchars($question['description']) . '</p>';
        }

        switch ($question['question_type']) {
            case 'text':
                $stmt = $pdo->prepare("SELECT answer_text FROM answers WHERE survey_id = ? AND question_id = ?");
                $stmt->execute([$survey_id, $question['id']]);
                $text_answers = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($text_answers as $answer) {
                    $html .= '<div class="text-answer">' . nl2br(htmlspecialchars($answer)) . '</div>';
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
                
                foreach ($option_counts as $option => $count) {
                    $percentage = $total_respondents > 0 ? round(($count / $total_respondents) * 100) : 0;
                    $html .= '<div class="option">';
                    $html .= '<div>' . htmlspecialchars($option) . ' - ' . $count . ' نفر (' . $percentage . '%)</div>';
                    $html .= '<div class="progress-bar" style="width: ' . $percentage . '%;"></div>';
                    $html .= '</div>';
                }
                break;
        }
        
        $html .= '</div>';
    }

    $html .= '</body></html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // خروجی PDF
    $dompdf->stream("survey_results.pdf", array("Attachment" => false));

} catch(PDOException $e) {
    error_log("خطا در ایجاد PDF: " . $e->getMessage());
    die("خطا در ایجاد PDF: " . $e->getMessage());
}
?> 