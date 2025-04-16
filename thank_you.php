<?php
require_once 'config.php';

// دریافت اطلاعات نظرسنجی
if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT title FROM surveys WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $survey = $stmt->fetch();
    } catch(PDOException $e) {
        error_log("خطا در دریافت اطلاعات نظرسنجی: " . $e->getMessage());
        $survey = null;
    }
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت موفق نظرسنجی</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.css" rel="stylesheet" type="text/css">
    <style>
        body {
            font-family: 'Vazir', Tahoma, Arial;
            background-color: #f8f9fa;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 15px;
            border: none;
            max-width: 500px;
            width: 90%;
            text-align: center;
            padding: 2rem;
            background-color: white;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        .card-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        .card-text {
            color: #666;
            margin-bottom: 2rem;
        }
        .btn {
            font-weight: 500;
            padding: 10px 25px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(45deg, #2196f3, #1976d2);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #1976d2, #1565c0);
            transform: translateY(-2px);
        }
        .survey-title {
            color: #2196f3;
            font-weight: 600;
            margin: 1rem 0;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="card">
        <i class="bi bi-check-circle-fill success-icon"></i>
        <h2 class="card-title">با تشکر از شما</h2>
        <?php if ($survey): ?>
            <div class="survey-title"><?php echo htmlspecialchars($survey['title']); ?></div>
        <?php endif; ?>
        <p class="card-text">
            پاسخ‌های شما با موفقیت ثبت شد. از مشارکت شما در این نظرسنجی سپاسگزاریم.
        </p>
        <button class="btn btn-secondary" disabled>
            <i class="bi bi-box-arrow-up-right"></i> بازگشت به سایت
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 