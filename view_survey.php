<?php
require_once 'config.php';

if (!isset($_GET['id']) || !isset($_GET['code'])) {
    header("Location: index.php");
    exit();
}

$survey_id = $_GET['id'];
$link_code = $_GET['code'];

try {
    // بررسی اعتبار لینک
    $stmt = $pdo->prepare("SELECT * FROM survey_links WHERE survey_id = ? AND link_code = ? AND is_used = 0");
    $stmt->execute([$survey_id, $link_code]);
    $link = $stmt->fetch();
    
    if (!$link) {
        header("Location: thank_you.php?id=" . $survey_id);
        exit();
    }
    
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
} catch(PDOException $e) {
    error_log("خطا در دریافت اطلاعات نظرسنجی: " . $e->getMessage());
    header("Location: thank_you.php?id=" . $survey_id);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // ثبت پاسخ‌ها
        $stmt = $pdo->prepare("INSERT INTO answers (survey_id, question_id, answer_text, link_code) VALUES (?, ?, ?, ?)");
        
        foreach ($_POST['answers'] as $question_id => $answer) {
            if (is_array($answer)) {
                // برای سوالات چند گزینه‌ای
                $answer_text = implode(', ', $answer);
            } else {
                // برای سوالات تک گزینه‌ای و متنی
                $answer_text = $answer;
            }
            
            $stmt->execute([$survey_id, $question_id, $answer_text, $link_code]);
        }
        
        // به‌روزرسانی وضعیت لینک
        $stmt = $pdo->prepare("UPDATE survey_links SET is_used = 1 WHERE id = ?");
        $stmt->execute([$link['id']]);
        
        // هدایت به صفحه تشکر
        header("Location: thank_you.php?id=" . $survey_id);
        exit();
        
    } catch(PDOException $e) {
        error_log("خطا در ثبت پاسخ‌ها: " . $e->getMessage());
        header("Location: thank_you.php?id=" . $survey_id);
        exit();
    }
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($survey['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
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
        .btn-submit {
            font-family: 'Yekan', Tahoma, Arial;
            font-weight: 700;
            font-size: 1.2rem;
            padding: 12px 30px;
            border-radius: 8px;
            background: linear-gradient(45deg, #4CAF50, #388E3C);
            border: none;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(76, 175, 80, 0.3);
            letter-spacing: -0.5px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.4);
            background: linear-gradient(45deg, #388E3C, #2E7D32);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        .btn-submit i {
            margin-left: 8px;
            font-size: 1.2rem;
        }
        .btn-submit:disabled {
            background: #ccc;
            box-shadow: none;
            transform: none;
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
        .question-item {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .question-text {
            font-family: 'Yekan', Tahoma, Arial;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.3rem;
            line-height: 2;
            position: relative;
            padding-right: 15px;
            letter-spacing: -0.5px;
        }
        .question-text::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, #2196f3, #1976d2);
            border-radius: 2px;
        }
        .question-description {
            font-family: 'Yekan', Tahoma, Arial;
            color: #666;
            font-size: 1rem;
            margin-bottom: 15px;
            padding-right: 15px;
            border-right: 3px solid #e9ecef;
            line-height: 2;
            letter-spacing: -0.3px;
        }
        .options-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .option-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .option-item:hover {
            background-color: #e9ecef;
            transform: translateX(-5px);
        }
        .option-item.selected {
            background-color: #e3f2fd;
            border-color: #2196f3;
        }
        .option-item.selected::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: #2196f3;
        }
        .option-item input[type="radio"],
        .option-item input[type="checkbox"] {
            margin-left: 10px;
            cursor: pointer;
        }
        .option-item label {
            margin: 0;
            cursor: pointer;
            flex-grow: 1;
        }
        select.form-select {
            padding: 12px 15px;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        select.form-select:focus {
            border-color: #2196f3;
            box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
        }
        textarea.form-control {
            padding: 12px 15px;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            background-color: #f8f9fa;
            transition: all 0.2s ease;
        }
        textarea.form-control:focus {
            border-color: #2196f3;
            box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
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
                        <a class="nav-link" href="reports.php">گزارشات</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title"><?php echo htmlspecialchars($survey['title']); ?></h2>
                <p class="card-text"><?php echo htmlspecialchars($survey['description']); ?></p>
                
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>راهنمای انتخاب گزینه‌ها:</strong>
                    <ul class="mb-0 mt-2">
                        <li>برای سوالات تک انتخابی، روی گزینه مورد نظر کلیک کنید تا انتخاب شود.</li>
                        <li>برای سوالات چند انتخابی، می‌توانید روی چند گزینه کلیک کنید تا انتخاب شوند.</li>
                        <li>گزینه‌های انتخاب شده با رنگ آبی و نوار آبی در سمت راست مشخص می‌شوند.</li>
                        <li>برای لغو انتخاب، دوباره روی گزینه کلیک کنید.</li>
                        <li>همکار گرامی : درراستای رفع مشکلات و نواقص ،خواهشمند است درصورت انتخاب ضعیف ، خیلی ضعیف در هرمورد علل ودلایل را درکادر پایین هرقسمت ذکر فرمایید.</li>
                    </ul>
                </div>
                
                <form method="POST" id="surveyForm">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="question-item mb-4">
                            <h4 class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></h4>
                            <?php if ($question['has_description'] && !empty($question['description'])): ?>
                                <p class="question-description"><?php echo htmlspecialchars($question['description']); ?></p>
                            <?php endif; ?>
                            <?php
                            switch ($question['question_type']) {
                                case 'text':
                                    ?>
                                    <textarea class="form-control" name="answers[<?php echo $question['id']; ?>]" rows="3" required></textarea>
                                    <?php
                                    break;
                                case 'radio':
                                    $options = json_decode($question['options'], true);
                                    ?>
                                    <div class="options-container">
                                        <?php foreach ($options as $option): ?>
                                            <div class="option-item" onclick="selectRadio(this, '<?php echo $question['id']; ?>', '<?php echo htmlspecialchars($option); ?>')">
                                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" 
                                                       value="<?php echo htmlspecialchars($option); ?>" required>
                                                <label><?php echo htmlspecialchars($option); ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php
                                    break;
                                case 'checkbox':
                                    $options = json_decode($question['options'], true);
                                    ?>
                                    <div class="options-container">
                                        <?php foreach ($options as $option): ?>
                                            <div class="option-item" onclick="toggleCheckbox(this, '<?php echo $question['id']; ?>', '<?php echo htmlspecialchars($option); ?>')">
                                                <input type="checkbox" name="answers[<?php echo $question['id']; ?>][]" 
                                                       value="<?php echo htmlspecialchars($option); ?>">
                                                <label><?php echo htmlspecialchars($option); ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php
                                    break;
                            }
                            ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="d-flex justify-content-center mt-4">
                        <button type="submit" class="btn btn-submit" id="submitBtn">
                            <i class="bi bi-check-circle"></i>
                            ثبت
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // تابع انتخاب گزینه رادیویی
    function selectRadio(element, questionId, value) {
        // حذف کلاس selected از همه گزینه‌های این سوال
        const questionContainer = element.closest('.question-item');
        questionContainer.querySelectorAll('.option-item').forEach(item => {
            item.classList.remove('selected');
        });
        
        // اضافه کردن کلاس selected به گزینه انتخاب شده
        element.classList.add('selected');
        
        // انتخاب radio button
        const radio = element.querySelector('input[type="radio"]');
        radio.checked = true;
        
        // لاگ برای اشکال‌زدایی
        console.log('Radio selected:', questionId, value);
    }
    
    // تابع انتخاب/لغو انتخاب گزینه چک‌باکس
    function toggleCheckbox(element, questionId, value) {
        const checkbox = element.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        
        if (checkbox.checked) {
            element.classList.add('selected');
        } else {
            element.classList.remove('selected');
        }
        
        // لاگ برای اشکال‌زدایی
        console.log('Checkbox toggled:', questionId, value, checkbox.checked);
    }
    
    // اضافه کردن event listener برای فرم
    document.getElementById('surveyForm').addEventListener('submit', function(e) {
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = 'در حال ارسال...';
    });
    </script>
</body>
</html> 