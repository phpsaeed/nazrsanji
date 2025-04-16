<?php
require_once 'config.php';
require_once 'auth.php';

// بررسی دسترسی ادمین
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($current_password === ADMIN_PASSWORD) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                // تغییر رمز عبور در فایل auth.php
                $auth_file = file_get_contents('auth.php');
                $new_auth_file = preg_replace(
                    "/define\('ADMIN_PASSWORD', '.*?'\);/",
                    "define('ADMIN_PASSWORD', '" . addslashes($new_password) . "');",
                    $auth_file
                );
                
                if (file_put_contents('auth.php', $new_auth_file)) {
                    $success = 'رمز عبور با موفقیت تغییر کرد.';
                } else {
                    $error = 'خطا در ذخیره رمز عبور جدید.';
                }
            } else {
                $error = 'رمز عبور جدید باید حداقل 6 کاراکتر باشد.';
            }
        } else {
            $error = 'رمز عبور جدید و تکرار آن مطابقت ندارند.';
        }
    } else {
        $error = 'رمز عبور فعلی اشتباه است.';
    }
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تغییر رمز عبور - سیستم نظرسنجی</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.css" rel="stylesheet" type="text/css">
    <style>
        body {
            font-family: 'Vazir', Tahoma, Arial;
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 10px;
            border: none;
        }
        .card-title {
            font-weight: 600;
            color: #2c3e50;
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
                <ul class="navbar-nav">
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
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title mb-4">تغییر رمز عبور</h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">رمز عبور فعلی</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">رمز عبور جدید</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">تکرار رمز عبور جدید</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">تغییر رمز عبور</button>
                                <a href="dashboard.php" class="btn btn-secondary">بازگشت به داشبورد</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 