<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$survey_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM surveys WHERE id = ?");
    $stmt->execute([$survey_id]);
    $survey = $stmt->fetch();
    
    if (!$survey) {
        header("Location: index.php");
        exit();
    }
    
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE survey_id = ?");
    $stmt->execute([$survey_id]);
    $questions = $stmt->fetchAll();
} catch(PDOException $e) {
    die("خطا در دریافت اطلاعات نظرسنجی: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($survey['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title"><?php echo htmlspecialchars($survey['title']); ?></h2>
                <?php if (!empty($survey['description'])): ?>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($survey['description'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <form method="POST" action="submit_survey.php" id="surveyForm">
            <input type="hidden" name="survey_id" value="<?php echo $survey_id; ?>">
            
            <?php foreach ($questions as $index => $question): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">سوال <?php echo $index + 1; ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
                        
                        <?php if ($question['has_description'] && !empty($question['description'])): ?>
                            <p class="text-muted"><?php echo htmlspecialchars($question['description']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($question['question_type'] === 'text'): ?>
                            <textarea class="form-control" name="answers[<?php echo $question['id']; ?>]" rows="3" required></textarea>
                        <?php else: ?>
                            <?php $options = json_decode($question['options'], true); ?>
                            <?php foreach ($options as $option): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="<?php echo $question['question_type']; ?>" 
                                           name="answers[<?php echo $question['id']; ?>]<?php echo $question['question_type'] === 'checkbox' ? '[]' : ''; ?>" 
                                           value="<?php echo htmlspecialchars($option); ?>" required>
                                    <label class="form-check-label">
                                        <?php echo htmlspecialchars($option); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-primary" id="submitButton">ارسال پاسخ</button>
            </div>
        </form>
    </div>

    <script>
    document.getElementById('surveyForm').addEventListener('submit', function(e) {
        document.getElementById('submitButton').disabled = true;
        document.getElementById('submitButton').innerHTML = 'در حال ارسال...';
    });
    </script>
</body>
</html> 