<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطای سرور - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: <?php echo FONT_FAMILY; ?>;
            background-color: #f8f9fa;
        }
        .server-error-container {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .server-error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .server-error-code {
            font-size: 2rem;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .server-error-message {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }
        .btn-home {
            background: linear-gradient(45deg, <?php echo PRIMARY_COLOR; ?>, <?php echo SECONDARY_COLOR; ?>);
            border: none;
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="server-error-container">
            <i class="bi bi-exclamation-circle server-error-icon"></i>
            <div class="server-error-code">500</div>
            <div class="server-error-message">متأسفانه خطایی در سرور رخ داده است. لطفاً بعداً تلاش کنید.</div>
            <a href="<?php echo SITE_URL; ?>" class="btn btn-home">
                <i class="bi bi-house-door"></i> بازگشت به صفحه اصلی
            </a>
        </div>
    </div>
</body>
</html> 