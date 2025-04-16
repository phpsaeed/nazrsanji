<?php
require_once 'config.php';

try {
    // دریافت لیست نظرسنجی‌ها و لینک‌های آنها
    $stmt = $pdo->query("SELECT s.*, 
        (SELECT COUNT(*) FROM questions WHERE survey_id = s.id) as question_count,
        (SELECT COUNT(*) FROM answers WHERE survey_id = s.id) as answer_count,
        sl.link_code,
        sl.user_name
        FROM surveys s 
        LEFT JOIN survey_links sl ON s.id = sl.survey_id AND sl.is_used = 0
        ORDER BY s.created_at DESC");
    $surveys = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "خطا در دریافت نظرسنجی‌ها: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سیستم نظرسنجی</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.css" rel="stylesheet" type="text/css">
    <style>
        body {
            font-family: 'Vazir', Tahoma, Arial;
            background-color: #f8f9fa;
            line-height: 1.6;
        }
        .survey-card {
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 10px;
            border: none;
        }
        .survey-card:hover {
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
        .user-info {
            background-color: #e3f2fd;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .user-info i {
            color: #1976d2;
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
            <h2>نظرسنجی‌های فعال</h2>
            <a href="create_survey.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> ایجاد نظرسنجی جدید
            </a>
        </div>

        <div class="row">
            <?php foreach ($surveys as $survey): ?>
                <div class="col-md-6">
                    <div class="card survey-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($survey['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($survey['description']); ?></p>
                            <?php if ($survey['user_name']): ?>
                                <div class="user-info">
                                    <i class="bi bi-person"></i>
                                    <span><?php echo htmlspecialchars($survey['user_name']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="bi bi-question-circle"></i> تعداد سوالات: <?php echo $survey['question_count']; ?>
                                    </small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="bi bi-check-circle"></i> تعداد پاسخ‌ها: <?php echo $survey['answer_count']; ?>
                                    </small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <?php if ($survey['link_code']): ?>
                                    <a href="survey_link.php?id=<?php echo $survey['id']; ?>&code=<?php echo $survey['link_code']; ?>" class="btn btn-primary">
                                        <i class="bi bi-pencil-square"></i> شرکت در نظرسنجی
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                        <i class="bi bi-x-circle"></i> لینک نامعتبر
                                    </button>
                                <?php endif; ?>
                                <a href="results.php?id=<?php echo $survey['id']; ?>" class="btn btn-success">
                                    <i class="bi bi-graph-up"></i> مشاهده نتایج
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 