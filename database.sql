-- ایجاد پایگاه داده
CREATE DATABASE IF NOT EXISTS khorasa4_Nazarsanje CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE khorasa4_Nazarsanje;

-- جدول نظرسنجی‌ها
CREATE TABLE IF NOT EXISTS surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tracking_id VARCHAR(20) UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول سوالات
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    question_text TEXT NOT NULL,
    has_description BOOLEAN DEFAULT FALSE,
    description TEXT NULL,
    question_type ENUM('text', 'radio', 'checkbox', 'select') NOT NULL,
    options JSON,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول لینک‌های نظرسنجی
CREATE TABLE IF NOT EXISTS survey_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    link_code VARCHAR(50) NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    UNIQUE KEY unique_link_code (link_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول پاسخ‌ها
CREATE TABLE IF NOT EXISTS answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_text TEXT NOT NULL,
    link_code VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (link_code) REFERENCES survey_links(link_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- حذف فیلدهای قدیمی اگر وجود دارند
ALTER TABLE questions DROP COLUMN IF EXISTS has_description;
ALTER TABLE questions DROP COLUMN IF EXISTS description;

-- اضافه کردن فیلدهای جدید
ALTER TABLE questions ADD COLUMN has_description BOOLEAN DEFAULT FALSE AFTER question_text;
ALTER TABLE questions ADD COLUMN description TEXT NULL AFTER has_description; 