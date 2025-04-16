<?php
require_once 'config.php';
require_once 'auth.php';

// بررسی دسترسی ادمین
requireAdmin();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$survey_id = $_GET['id'];

// اضافه کردن فیلتر رتبه‌بندی
$rating_filter = isset($_GET['rating']) ? $_GET['rating'] : '';

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
    
    // دریافت پاسخ‌ها با فیلتر رتبه‌بندی
    $query = "
        SELECT a.*, sl.user_name 
        FROM answers a 
        LEFT JOIN survey_links sl ON a.link_code = sl.link_code 
        WHERE a.survey_id = ?
    ";
    
    if ($rating_filter) {
        $query .= " AND a.answer_text = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$survey_id, $rating_filter]);
    } else {
        $stmt = $pdo->prepare($query);
        $stmt->execute([$survey_id]);
    }
    
    $answers = $stmt->fetchAll();
    
    // گروه‌بندی پاسخ‌ها بر اساس لینک
    $grouped_answers = [];
    foreach ($answers as $answer) {
        $grouped_answers[$answer['link_code']][] = $answer;
    }
    
    // محاسبه آمار برای هر سوال
    $question_stats = [];
    foreach ($questions as $question) {
        $question_answers = array_filter($answers, function($answer) use ($question) {
            return $answer['question_id'] == $question['id'];
        });
        
        $answer_counts = [];
        foreach ($question_answers as $answer) {
            $answer_text = $answer['answer_text'];
            if (!isset($answer_counts[$answer_text])) {
                $answer_counts[$answer_text] = 0;
            }
            $answer_counts[$answer_text]++;
        }
        
        // محاسبه درصدها
        $total_answers = count($question_answers);
        $percentages = [];
        foreach ($answer_counts as $answer => $count) {
            $percentage = $total_answers > 0 ? round(($count / $total_answers) * 100, 1) : 0;
            $percentages[$answer] = [
                'count' => $count,
                'percentage' => $percentage
            ];
        }
        
        // مرتب‌سازی درصدها به صورت صعودی
        uasort($percentages, function($a, $b) {
            return $a['percentage'] - $b['percentage'];
        });
        
        $question_stats[$question['id']] = [
            'total_answers' => $total_answers,
            'percentages' => $percentages
        ];
    }
    
} catch(PDOException $e) {
    error_log("خطا در دریافت نتایج نظرسنجی: " . $e->getMessage());
    die("خطا در دریافت نتایج نظرسنجی");
}

// ساخت لینک نظرسنجی
$survey_url = SITE_URL . "view_survey.php?id=" . $survey_id;
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتایج نظرسنجی - <?php echo htmlspecialchars($survey['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.css" rel="stylesheet" type="text/css">
    <style>
        body {
            font-family: 'Vazir', Tahoma, Arial;
            background-color: #f8f9fa;
            line-height: 1.6;
        }
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .card-title {
            font-weight: 600;
            color: #2c3e50;
        }
        .card-text {
            color: #666;
        }
        .btn {
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 6px;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }
        .nav-link {
            font-weight: 500;
        }
        .text-muted {
            font-size: 0.9rem;
        }
        .bi {
            margin-left: 5px;
        }
        .answer-item {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-right: 4px solid #2196f3;
        }
        .answer-user {
            font-weight: 600;
            color: #1976d2;
            margin-bottom: 5px;
        }
        .answer-text {
            color: #666;
        }
        .stats-card {
            background: linear-gradient(45deg, #2196f3, #1976d2);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
        }
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .progress {
            height: 25px;
            background-color: #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .progress-bar {
            background: linear-gradient(45deg, #2196f3, #1976d2);
            transition: width 0.6s ease;
        }
        .percentage {
            font-weight: 600;
            color: #1976d2;
        }
        .answer-count {
            font-size: 0.9rem;
            color: #666;
        }
        .copy-link {
            background: #fff;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .copy-link input {
            border: none;
            background: none;
            flex-grow: 1;
            font-family: 'Vazir', Tahoma, Arial;
            font-size: 0.9rem;
            color: #666;
        }
        .copy-link input:focus {
            outline: none;
        }
        .copy-link .btn {
            background: linear-gradient(45deg, #2196f3, #1976d2);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        .copy-link .btn:hover {
            background: linear-gradient(45deg, #1976d2, #1565c0);
            transform: translateY(-2px);
        }
        .copy-link .btn i {
            margin-left: 5px;
        }
        .toast {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">سیستم نظرسنجی</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">داشبورد</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_surveys.php">مدیریت نظرسنجی‌ها</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_links.php">مدیریت لینک‌ها</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">گزارشات</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>نتایج نظرسنجی: <?php echo htmlspecialchars($survey['title']); ?></h2>
            <div>
                <a href="export_pdf.php?id=<?php echo $survey_id; ?>" class="btn btn-danger">
                    <i class="bi bi-file-pdf"></i> دریافت PDF
                </a>
                <a href="export_excel.php?id=<?php echo $survey_id; ?>" class="btn btn-success">
                    <i class="bi bi-file-excel"></i> دریافت Excel
                </a>
            </div>
        </div>

        <div class="copy-link">
            <input type="text" value="<?php echo $survey_url; ?>" id="surveyLink" readonly>
            <button class="btn" onclick="copyLink()">
                <i class="bi bi-clipboard"></i> کپی لینک
            </button>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo count($questions); ?></div>
                    <div class="stats-label">تعداد سوالات</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo count($grouped_answers); ?></div>
                    <div class="stats-label">تعداد پاسخ‌دهندگان</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo count($answers); ?></div>
                    <div class="stats-label">تعداد کل پاسخ‌ها</div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">فیلتر بر اساس رتبه‌بندی</h5>
                        <form method="GET" class="row g-3">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($survey_id); ?>">
                            <div class="col-md-4">
                                <select name="rating" class="form-select">
                                    <option value="">همه رتبه‌بندی‌ها</option>
                                    <option value="خیلی ضعیف" <?php echo $rating_filter === 'خیلی ضعیف' ? 'selected' : ''; ?>>خیلی ضعیف</option>
                                    <option value="ضعیف" <?php echo $rating_filter === 'ضعیف' ? 'selected' : ''; ?>>ضعیف</option>
                                    <option value="متوسط" <?php echo $rating_filter === 'متوسط' ? 'selected' : ''; ?>>متوسط</option>
                                    <option value="خوب" <?php echo $rating_filter === 'خوب' ? 'selected' : ''; ?>>خوب</option>
                                    <option value="عالی" <?php echo $rating_filter === 'عالی' ? 'selected' : ''; ?>>عالی</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">اعمال فیلتر</button>
                            </div>
                            <?php if ($rating_filter): ?>
                            <div class="col-md-2">
                                <a href="?id=<?php echo htmlspecialchars($survey_id); ?>" class="btn btn-secondary w-100">پاک کردن فیلتر</a>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php foreach ($questions as $question): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($question['question_text']); ?></h5>
                    <?php if ($question['has_description'] && $question['description']): ?>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($question['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="mt-3">
                        <?php
                        $stats = $question_stats[$question['id']];
                        $total_answers = $stats['total_answers'];
                        
                        if ($total_answers > 0):
                            foreach ($stats['percentages'] as $answer => $data):
                        ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="answer-text">
                                        <?php echo htmlspecialchars($answer); ?>
                                        <span class="answer-count">(<?php echo $data['count']; ?> پاسخ)</span>
                                    </span>
                                    <span class="percentage"><?php echo $data['percentage']; ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo $data['percentage']; ?>%" 
                                         aria-valuenow="<?php echo $data['percentage']; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <div class="text-center text-muted">
                                <i class="bi bi-info-circle"></i>
                                هنوز هیچ پاسخی برای این سوال ثبت نشده است.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="bi bi-check-circle text-success"></i>
            <strong class="me-auto">توجه</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            لینک نظرسنجی با موفقیت کپی شد.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyLink() {
            const linkInput = document.getElementById('surveyLink');
            linkInput.select();
            document.execCommand('copy');
            
            // نمایش پیام موفقیت
            const toast = new bootstrap.Toast(document.querySelector('.toast'));
            toast.show();
        }
    </script>
</body>
</html> 