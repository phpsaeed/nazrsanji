<?php
require_once 'config.php';
require_once 'auth.php';

// بررسی دسترسی ادمین
requireAdmin();

try {
    // دریافت لیست نظرسنجی‌ها
    $stmt = $pdo->query("SELECT id, title FROM surveys ORDER BY created_at DESC");
    $surveys = $stmt->fetchAll();

    // دریافت لیست لینک‌ها
    $stmt = $pdo->query("
        SELECT sl.*, s.title as survey_title 
        FROM survey_links sl 
        JOIN surveys s ON sl.survey_id = s.id 
        ORDER BY sl.created_at DESC
    ");
    $links = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("خطا در دریافت لیست لینک‌ها: " . $e->getMessage());
    die("خطا در دریافت لیست لینک‌ها");
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت لینک‌های نظرسنجی</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.css" rel="stylesheet" type="text/css">
    <style>
        @font-face {
            font-family: 'Yekan';
            src: url('fonts/Yekan.woff2') format('woff2'),
                 url('fonts/Yekan.woff') format('woff');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'Yekan', Tahoma, Arial;
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .link-code {
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .used {
            color: #dc3545;
        }
        .unused {
            color: #28a745;
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
        .link-item {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-right: 4px solid #2196f3;
        }
        .link-code {
            font-weight: 600;
            color: #1976d2;
            margin-bottom: 5px;
        }
        .link-user {
            color: #666;
        }
        .link-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
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
                        <a class="nav-link active" href="manage_links.php">مدیریت لینک‌ها</a>
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
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">ایجاد لینک جدید</h5>
                        <form action="create_link.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">نظرسنجی</label>
                                <select name="survey_id" class="form-select" required>
                                    <?php foreach ($surveys as $survey): ?>
                                        <option value="<?php echo $survey['id']; ?>">
                                            <?php echo htmlspecialchars($survey['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">نام کاربر</label>
                                <input type="text" name="user_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">کد لینک</label>
                                <input type="text" name="link_code" class="form-control" required>
                                <div class="form-text">مثال: 1001</div>
                            </div>
                            <button type="submit" class="btn btn-primary">ایجاد لینک</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">لیست لینک‌ها</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>نظرسنجی</th>
                                        <th>نام کاربر</th>
                                        <th>کد لینک</th>
                                        <th>وضعیت</th>
                                        <th>تاریخ ایجاد</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($links as $link): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($link['survey_title']); ?></td>
                                            <td><?php echo htmlspecialchars($link['user_name']); ?></td>
                                            <td>
                                                <span class="link-code">
                                                    <?php echo htmlspecialchars($link['link_code']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($link['is_used']): ?>
                                                    <span class="used">استفاده شده</span>
                                                <?php else: ?>
                                                    <span class="unused">استفاده نشده</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('Y/m/d H:i', strtotime($link['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="survey_link.php?id=<?php echo $link['survey_id']; ?>&code=<?php echo $link['link_code']; ?>" 
                                                       class="btn btn-sm btn-info" target="_blank">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-primary" 
                                                            onclick="copyLink('<?php echo SITE_URL . 'survey_link.php?id=' . $link['survey_id'] . '&code=' . $link['link_code']; ?>')">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                    <a href="delete_link.php?id=<?php echo $link['id']; ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('آیا از حذف این لینک اطمینان دارید؟')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
        function copyLink(link) {
            navigator.clipboard.writeText(link).then(() => {
                // نمایش پیام موفقیت
                const toast = new bootstrap.Toast(document.querySelector('.toast'));
                toast.show();
            });
        }
    </script>
</body>
</html> 