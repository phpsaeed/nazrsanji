<?php
require_once 'config.php';
require_once 'auth.php';

// بررسی دسترسی ادمین
requireAdmin();

if (!isset($_GET['id'])) {
    header("Location: manage_surveys.php");
    exit();
}

$survey_id = $_GET['id'];

try {
    // دریافت اطلاعات نظرسنجی
    $stmt = $pdo->prepare("SELECT * FROM surveys WHERE id = ?");
    $stmt->execute([$survey_id]);
    $survey = $stmt->fetch();
    
    if (!$survey) {
        header("Location: manage_surveys.php");
        exit();
    }
    
    // دریافت سوالات نظرسنجی
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE survey_id = ?");
    $stmt->execute([$survey_id]);
    $questions = $stmt->fetchAll();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // بروزرسانی اطلاعات نظرسنجی
        $stmt = $pdo->prepare("UPDATE surveys SET title = ?, description = ? WHERE id = ?");
        $stmt->execute([$_POST['title'], $_POST['description'], $survey_id]);
        
        // حذف سوالات قبلی
        $stmt = $pdo->prepare("DELETE FROM questions WHERE survey_id = ?");
        $stmt->execute([$survey_id]);
        
        // اضافه کردن سوالات جدید
        foreach ($_POST['questions'] as $question) {
            if (!empty($question['text'])) {
                $stmt = $pdo->prepare("INSERT INTO questions (survey_id, question_text, question_type, options) VALUES (?, ?, ?, ?)");
                $options = isset($question['options']) ? json_encode($question['options']) : null;
                $stmt->execute([$survey_id, $question['text'], $question['type'], $options]);
            }
        }
        
        header("Location: manage_surveys.php");
        exit();
    }
} catch(PDOException $e) {
    echo "خطا در ویرایش نظرسنجی: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش نظرسنجی</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazir', Tahoma, Arial;
            background-color: #f8f9fa;
        }
        .option-item {
            margin-bottom: 10px;
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
                        <a class="nav-link active" href="manage_surveys.php">مدیریت نظرسنجی‌ها</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">گزارشات</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">ویرایش نظرسنجی</h2>
        
        <form method="POST" class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">عنوان نظرسنجی</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($survey['title']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">توضیحات</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($survey['description']); ?></textarea>
                </div>
                
                <div id="questions-container">
                    <h3>سوالات</h3>
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="question-item mb-3">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="questions[<?php echo $index; ?>][text]" 
                                           value="<?php echo htmlspecialchars($question['question_text']); ?>" 
                                           placeholder="متن سوال" required>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select question-type" name="questions[<?php echo $index; ?>][type]" 
                                            onchange="toggleOptions(this, <?php echo $index; ?>)">
                                        <option value="text" <?php echo $question['question_type'] === 'text' ? 'selected' : ''; ?>>متن آزاد</option>
                                        <option value="radio" <?php echo $question['question_type'] === 'radio' ? 'selected' : ''; ?>>تک انتخابی</option>
                                        <option value="checkbox" <?php echo $question['question_type'] === 'checkbox' ? 'selected' : ''; ?>>چند انتخابی</option>
                                        <option value="select" <?php echo $question['question_type'] === 'select' ? 'selected' : ''; ?>>لیست کشویی</option>
                                    </select>
                                </div>
                            </div>
                            <div class="options-container mt-2" id="options-<?php echo $index; ?>">
                                <?php if (in_array($question['question_type'], ['radio', 'checkbox', 'select'])): 
                                    $options = json_decode($question['options'], true) ?? ['گزینه 1', 'گزینه 2', 'گزینه 3'];
                                    foreach ($options as $option): ?>
                                        <div class="option-item">
                                            <div class="input-group">
                                                <input type="text" class="form-control" 
                                                       name="questions[<?php echo $index; ?>][options][]" 
                                                       value="<?php echo htmlspecialchars($option); ?>" required>
                                                <button type="button" class="btn btn-danger" onclick="removeOption(this)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="addOption(<?php echo $index; ?>)">
                                        <i class="bi bi-plus-circle"></i> افزودن گزینه
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" class="btn btn-secondary mb-3" onclick="addQuestion()">افزودن سوال</button>
                <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                <a href="manage_surveys.php" class="btn btn-danger">انصراف</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let questionCount = <?php echo count($questions); ?>;
        
        function addQuestion() {
            const container = document.getElementById('questions-container');
            const questionDiv = document.createElement('div');
            questionDiv.className = 'question-item mb-3';
            questionDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="questions[${questionCount}][text]" placeholder="متن سوال" required>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select question-type" name="questions[${questionCount}][type]" onchange="toggleOptions(this, ${questionCount})">
                            <option value="text">متن آزاد</option>
                            <option value="radio">تک انتخابی</option>
                            <option value="checkbox">چند انتخابی</option>
                            <option value="select">لیست کشویی</option>
                        </select>
                    </div>
                </div>
                <div class="options-container mt-2" id="options-${questionCount}"></div>
            `;
            container.appendChild(questionDiv);
            questionCount++;
        }
        
        function toggleOptions(select, index) {
            const optionsContainer = document.getElementById(`options-${index}`);
            optionsContainer.innerHTML = '';
            
            if (select.value !== 'text') {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'option-item';
                optionDiv.innerHTML = `
                    <div class="input-group">
                        <input type="text" class="form-control" name="questions[${index}][options][]" value="گزینه 1" required>
                        <button type="button" class="btn btn-danger" onclick="removeOption(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
                optionsContainer.appendChild(optionDiv);
                
                const addButton = document.createElement('button');
                addButton.type = 'button';
                addButton.className = 'btn btn-secondary btn-sm';
                addButton.innerHTML = '<i class="bi bi-plus-circle"></i> افزودن گزینه';
                addButton.onclick = () => addOption(index);
                optionsContainer.appendChild(addButton);
            }
        }
        
        function addOption(questionIndex) {
            const optionsContainer = document.getElementById(`options-${questionIndex}`);
            const optionDiv = document.createElement('div');
            optionDiv.className = 'option-item';
            optionDiv.innerHTML = `
                <div class="input-group">
                    <input type="text" class="form-control" name="questions[${questionIndex}][options][]" value="گزینه جدید" required>
                    <button type="button" class="btn btn-danger" onclick="removeOption(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            optionsContainer.insertBefore(optionDiv, optionsContainer.lastElementChild);
        }
        
        function removeOption(button) {
            const optionItem = button.closest('.option-item');
            optionItem.remove();
        }
    </script>
</body>
</html> 