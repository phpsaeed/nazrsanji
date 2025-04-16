<?php
require_once 'config.php';
require_once 'auth.php';

// بررسی دسترسی ادمین
requireAdmin();

// دریافت آمار کلی
try {
    $stats = [
        'total_surveys' => $pdo->query("SELECT COUNT(*) FROM surveys")->fetchColumn(),
        'total_questions' => $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn(),
        'total_answers' => $pdo->query("SELECT COUNT(*) FROM answers")->fetchColumn(),
        'total_respondents' => $pdo->query("SELECT COUNT(DISTINCT survey_id) FROM answers")->fetchColumn()
    ];
} catch(PDOException $e) {
    echo "خطا در دریافت آمار: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد مدیریتی - سیستم نظرسنجی</title>
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
        .table {
            font-size: 0.95rem;
        }
        .table th {
            font-weight: 600;
            color: #2c3e50;
        }
        .stats-card {
            background: linear-gradient(45deg, #4e73df, #224abe);
            color: white;
        }
        .stats-card .card-title {
            color: white;
        }
        .stats-card .card-text {
            color: rgba(255,255,255,0.9);
        }
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
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
                        <a class="nav-link active" href="dashboard.php">داشبورد</a>
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
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">
                            <i class="bi bi-key"></i> تغییر رمز عبور
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_logout.php">
                            <i class="bi bi-box-arrow-right"></i> خروج
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">داشبورد مدیریتی</h2>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-clipboard-data stat-icon"></i>
                        <h5>کل نظرسنجی‌ها</h5>
                        <h3><?php echo $stats['total_surveys']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-question-circle stat-icon"></i>
                        <h5>کل سوالات</h5>
                        <h3><?php echo $stats['total_questions']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle stat-icon"></i>
                        <h5>کل پاسخ‌ها</h5>
                        <h3><?php echo $stats['total_answers']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-people stat-icon"></i>
                        <h5>کل پاسخ‌دهندگان</h5>
                        <h3><?php echo $stats['total_respondents']; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">نظرسنجی‌های اخیر</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>عنوان</th>
                                        <th>تاریخ</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $stmt = $pdo->query("SELECT * FROM surveys ORDER BY created_at DESC LIMIT 5");
                                        while ($survey = $stmt->fetch()) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($survey['title']) . "</td>";
                                            echo "<td>" . date('Y-m-d', strtotime($survey['created_at'])) . "</td>";
                                            echo "<td>
                                                    <a href='edit_survey.php?id=" . $survey['id'] . "' class='btn btn-sm btn-primary'>ویرایش</a>
                                                    <a href='view_survey.php?id=" . $survey['id'] . "' class='btn btn-sm btn-info'>مشاهده</a>
                                                    <a href=manage_surveys.php?delete=" . $survey['id'] . " class='btn btn-sm btn-danger' onclick='return confirm(\"آیا از حذف این نظرسنجی مطمئن هستید؟\")'>حذف</a>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } catch(PDOException $e) {
                                        echo "خطا در دریافت نظرسنجی‌ها: " . $e->getMessage();
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">گزارشات آماری</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // نمودار آماری
        const ctx = document.getElementById('statsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['نظرسنجی‌ها', 'سوالات', 'پاسخ‌ها', 'پاسخ‌دهندگان'],
                datasets: [{
                    label: 'آمار کلی',
                    data: [
                        <?php echo $stats['total_surveys']; ?>,
                        <?php echo $stats['total_questions']; ?>,
                        <?php echo $stats['total_answers']; ?>,
                        <?php echo $stats['total_respondents']; ?>
                    ],
                    backgroundColor: [
                        '#0d6efd',
                        '#198754',
                        '#0dcaf0',
                        '#ffc107'
                    ]
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html> 