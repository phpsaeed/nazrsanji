<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'tcpdf/tcpdf.php'; // کتابخانه TCPDF
require_once 'PhpSpreadsheet/vendor/autoload.php'; // کتابخانه PhpSpreadsheet

// بررسی دسترسی ادمین
requireAdmin();

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    header("Location: index.php");
    exit();
}

$survey_id = $_GET['id'];
$export_type = $_GET['type'];

try {
    // دریافت اطلاعات نظرسنجی
    $stmt = $pdo->prepare("SELECT * FROM surveys WHERE id = ?");
    $stmt->execute([$survey_id]);
    $survey = $stmt->fetch();
    
    if (!$survey) {
        header("Location: index.php");
        exit();
    }
    
    // دریافت سوالات و پاسخ‌ها
    $stmt = $pdo->prepare("
        SELECT q.*, COUNT(a.id) as answer_count,
        GROUP_CONCAT(a.answer_text) as answers
        FROM questions q
        LEFT JOIN answers a ON q.id = a.question_id
        WHERE q.survey_id = ?
        GROUP BY q.id
    ");
    $stmt->execute([$survey_id]);
    $questions = $stmt->fetchAll();
    
    // محاسبه تعداد کل پاسخ‌دهندگان
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT survey_id) as total_respondents FROM answers WHERE survey_id = ?");
    $stmt->execute([$survey_id]);
    $total_respondents = $stmt->fetch()['total_respondents'];
    
    if ($export_type === 'pdf') {
        // ایجاد PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // تنظیمات PDF
        $pdf->SetCreator('سیستم نظرسنجی');
        $pdf->SetAuthor('سیستم نظرسنجی');
        $pdf->SetTitle('نتایج نظرسنجی: ' . $survey['title']);
        $pdf->SetRTL(true);
        $pdf->SetFont('dejavusans', '', 12);
        
        // اضافه کردن صفحه
        $pdf->AddPage();
        
        // عنوان نظرسنجی
        $pdf->SetFont('dejavusans', 'B', 16);
        $pdf->Cell(0, 10, $survey['title'], 0, 1, 'C');
        $pdf->Ln(5);
        
        // توضیحات نظرسنجی
        if (!empty($survey['description'])) {
            $pdf->SetFont('dejavusans', '', 12);
            $pdf->MultiCell(0, 10, $survey['description'], 0, 'C');
            $pdf->Ln(5);
        }
        
        // تعداد پاسخ‌دهندگان
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'تعداد کل پاسخ‌دهندگان: ' . $total_respondents, 0, 1, 'C');
        $pdf->Ln(10);
        
        // نتایج سوالات
        foreach ($questions as $index => $question) {
            $pdf->SetFont('dejavusans', 'B', 14);
            $pdf->Cell(0, 10, 'سوال ' . ($index + 1) . ': ' . $question['question_text'], 0, 1, 'R');
            $pdf->Ln(5);
            
            if ($question['has_description'] && !empty($question['description'])) {
                $pdf->SetFont('dejavusans', 'I', 12);
                $pdf->MultiCell(0, 10, $question['description'], 0, 'R');
                $pdf->Ln(5);
            }
            
            if ($question['question_type'] === 'text') {
                $pdf->SetFont('dejavusans', '', 12);
                $answers = explode(',', $question['answers']);
                foreach ($answers as $answer) {
                    $pdf->MultiCell(0, 10, '• ' . $answer, 0, 'R');
                }
            } else {
                $options = json_decode($question['options'], true);
                $answers = explode(',', $question['answers']);
                $answer_counts = array_count_values($answers);
                
                foreach ($options as $option) {
                    $count = isset($answer_counts[$option]) ? $answer_counts[$option] : 0;
                    $percentage = $total_respondents > 0 ? round(($count / $total_respondents) * 100) : 0;
                    
                    $pdf->SetFont('dejavusans', '', 12);
                    $pdf->Cell(0, 10, $option . ': ' . $count . ' پاسخ (' . $percentage . '%)', 0, 1, 'R');
                }
            }
            
            $pdf->Ln(10);
        }
        
        // خروجی PDF
        $pdf->Output('survey_results.pdf', 'D');
        
    } elseif ($export_type === 'excel') {
        // ایجاد فایل Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // تنظیم جهت RTL
        $sheet->setRightToLeft(true);
        
        // عنوان نظرسنجی
        $sheet->setCellValue('A1', $survey['title']);
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // توضیحات نظرسنجی
        if (!empty($survey['description'])) {
            $sheet->setCellValue('A2', $survey['description']);
            $sheet->mergeCells('A2:B2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }
        
        // تعداد پاسخ‌دهندگان
        $sheet->setCellValue('A3', 'تعداد کل پاسخ‌دهندگان: ' . $total_respondents);
        $sheet->mergeCells('A3:B3');
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // نتایج سوالات
        $row = 5;
        foreach ($questions as $index => $question) {
            // عنوان سوال
            $sheet->setCellValue('A' . $row, 'سوال ' . ($index + 1) . ': ' . $question['question_text']);
            $sheet->mergeCells('A' . $row . ':B' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
            $row++;
            
            // توضیحات سوال
            if ($question['has_description'] && !empty($question['description'])) {
                $sheet->setCellValue('A' . $row, $question['description']);
                $sheet->mergeCells('A' . $row . ':B' . $row);
                $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
                $row++;
            }
            
            if ($question['question_type'] === 'text') {
                $answers = explode(',', $question['answers']);
                foreach ($answers as $answer) {
                    $sheet->setCellValue('A' . $row, '• ' . $answer);
                    $sheet->mergeCells('A' . $row . ':B' . $row);
                    $row++;
                }
            } else {
                $options = json_decode($question['options'], true);
                $answers = explode(',', $question['answers']);
                $answer_counts = array_count_values($answers);
                
                foreach ($options as $option) {
                    $count = isset($answer_counts[$option]) ? $answer_counts[$option] : 0;
                    $percentage = $total_respondents > 0 ? round(($count / $total_respondents) * 100) : 0;
                    
                    $sheet->setCellValue('A' . $row, $option . ': ' . $count . ' پاسخ (' . $percentage . '%)');
                    $sheet->mergeCells('A' . $row . ':B' . $row);
                    $row++;
                }
            }
            
            $row += 2;
        }
        
        // تنظیم عرض ستون‌ها
        $sheet->getColumnDimension('A')->setWidth(50);
        $sheet->getColumnDimension('B')->setWidth(50);
        
        // ایجاد فایل Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="survey_results.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
} catch(PDOException $e) {
    echo "خطا در دریافت نتایج نظرسنجی: " . $e->getMessage();
}
?> 