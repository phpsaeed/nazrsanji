<?php
require_once 'config.php';
require_once 'auth.php';

// بررسی دسترسی ادمین
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // ذخیره نظرسنجی
        $stmt = $pdo->prepare("INSERT INTO surveys (title, description) VALUES (?, ?)");
        $stmt->execute([$_POST['title'], $_POST['description']]);
        $survey_id = $pdo->lastInsertId();
        
        // ذخیره سوالات
        foreach ($_POST['questions'] as $question) {
            $options = isset($question['options']) ? json_encode($question['options']) : null;
            $has_description = isset($question['has_description']) ? 1 : 0;
            $description = isset($question['description']) ? $question['description'] : null;
            
            $stmt = $pdo->prepare("INSERT INTO questions (survey_id, question_text, description, has_description, question_type, options) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$survey_id, $question['text'], $description, $has_description, $question['type'], $options]);
        }
        
        header("Location: manage_surveys.php");
        exit();
    } catch(PDOException $e) {
        echo "خطا در ایجاد نظرسنجی: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ایجاد نظرسنجی جدید</title>
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
        .question-item {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .form-label {
            font-weight: 500;
            color: #2c3e50;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #2196f3;
            box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
        }
        .description-container {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-right: 3px solid #2196f3;
        }
        .options-container {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        .input-group {
            margin-bottom: 10px;
        }
        .btn-outline-danger {
            border-radius: 8px;
        }
        .btn-outline-primary {
            border-radius: 8px;
        }
        .question-number {
            font-size: 1.1rem;
            font-weight: 600;
        }
        .badge {
            padding: 8px 12px;
            font-size: 1rem;
            border-radius: 8px;
        }
        .question-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
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
                <h2 class="card-title mb-4">ایجاد نظرسنجی جدید</h2>
                
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label">عنوان نظرسنجی</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">توضیحات نظرسنجی</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div id="questionsContainer">
                        <?php for ($i = 0; $i < 1; $i++): ?>
                            <div class="question-item mb-4">
                                <div class="question-header d-flex align-items-center mb-3">
                                    <div class="question-number me-3">
                                        <span class="badge bg-primary">سوال <?php echo $i + 1; ?></span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="questions[<?php echo $i; ?>][has_description]" id="hasDescription<?php echo $i; ?>" onchange="toggleDescription(this, <?php echo $i; ?>)">
                                        <label class="form-check-label" for="hasDescription<?php echo $i; ?>">
                                            افزودن توضیحات برای این سوال
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="description-container mb-3" id="descriptionContainer<?php echo $i; ?>" style="display: none;">
                                    <label class="form-label">توضیحات سوال</label>
                                    <textarea class="form-control" name="questions[<?php echo $i; ?>][description]" rows="2"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">متن سوال</label>
                                    <input type="text" class="form-control" name="questions[<?php echo $i; ?>][text]" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">نوع سوال</label>
                                    <select class="form-select" name="questions[<?php echo $i; ?>][type]" onchange="toggleOptions(this, <?php echo $i; ?>)">
                                        <option value="text">پاسخ متنی</option>
                                        <option value="radio">چند گزینه‌ای</option>
                                        <option value="checkbox">چند انتخابی</option>
                                        <option value="select">لیست کشویی</option>
                                    </select>
                                </div>
                                
                                <div class="options-container" id="optionsContainer<?php echo $i; ?>" style="display: none;">
                                    <label class="form-label">گزینه‌ها</label>
                                    <div class="options-list">
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="questions[<?php echo $i; ?>][options][]" placeholder="گزینه 1">
                                            <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">حذف</button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOption(<?php echo $i; ?>)">افزودن گزینه</button>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="addQuestion()">
                            <i class="bi bi-plus-circle"></i> افزودن سوال
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> ایجاد نظرسنجی
                        </button>
                        <a href="manage_surveys.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> انصراف
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleOptions(select, index) {
            const optionsContainer = document.getElementById('optionsContainer' + index);
            const hasDescriptionCheckbox = document.getElementById('hasDescription' + index);
            
            if (select.value === 'text') {
                optionsContainer.style.display = 'none';
                hasDescriptionCheckbox.checked = false;
                hasDescriptionCheckbox.disabled = true;
            } else {
                optionsContainer.style.display = 'block';
                hasDescriptionCheckbox.disabled = false;
            }
        }
        
        function toggleDescription(checkbox, index) {
            const container = document.getElementById('descriptionContainer' + index);
            container.style.display = checkbox.checked ? 'block' : 'none';
        }
        
        function addOption(index) {
            const optionsList = document.querySelector('#optionsContainer' + index + ' .options-list');
            const optionCount = optionsList.children.length + 1;
            
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" class="form-control" name="questions[${index}][options][]" placeholder="گزینه ${optionCount}">
                <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">حذف</button>
            `;
            
            optionsList.appendChild(div);
        }
        
        function removeOption(button) {
            button.parentElement.remove();
        }
        
        function addQuestion() {
            const container = document.getElementById('questionsContainer');
            const questionCount = container.children.length;
            
            const div = document.createElement('div');
            div.className = 'question-item mb-4';
            div.innerHTML = `
                <div class="question-header d-flex align-items-center mb-3">
                    <div class="question-number me-3">
                        <span class="badge bg-primary">سوال ${questionCount + 1}</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="questions[${questionCount}][has_description]" id="hasDescription${questionCount}" onchange="toggleDescription(this, ${questionCount})">
                        <label class="form-check-label" for="hasDescription${questionCount}">
                            افزودن توضیحات برای این سوال
                        </label>
                    </div>
                </div>
                
                <div class="description-container mb-3" id="descriptionContainer${questionCount}" style="display: none;">
                    <label class="form-label">توضیحات سوال</label>
                    <textarea class="form-control" name="questions[${questionCount}][description]" rows="2"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">متن سوال</label>
                    <input type="text" class="form-control" name="questions[${questionCount}][text]" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">نوع سوال</label>
                    <select class="form-select" name="questions[${questionCount}][type]" onchange="toggleOptions(this, ${questionCount})">
                        <option value="text">پاسخ متنی</option>
                        <option value="radio">چند گزینه‌ای</option>
                        <option value="checkbox">چند انتخابی</option>
                        <option value="select">لیست کشویی</option>
                    </select>
                </div>
                
                <div class="options-container" id="optionsContainer${questionCount}" style="display: none;">
                    <label class="form-label">گزینه‌ها</label>
                    <div class="options-list">
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="questions[${questionCount}][options][]" placeholder="گزینه 1">
                            <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">حذف</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOption(${questionCount})">افزودن گزینه</button>
                </div>
            `;
            
            container.appendChild(div);
        }
    </script>
</body>
</html> 