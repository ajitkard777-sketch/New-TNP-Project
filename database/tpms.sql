-- =====================================================
-- TPMS - Training & Placement Management System
-- Complete Database Schema
-- Version: 1.0.0
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+05:30";

DROP DATABASE IF EXISTS `team1`;
CREATE DATABASE `team1` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `team1`;

-- =====================================================
-- USERS TABLE (Authentication)
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'student', 'company') NOT NULL DEFAULT 'student',
    `status` ENUM('active', 'inactive', 'pending', 'banned') NOT NULL DEFAULT 'active',
    `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `email_verification_token` VARCHAR(255) NULL,
    `password_reset_token` VARCHAR(255) NULL,
    `password_reset_expiry` DATETIME NULL,
    `otp` VARCHAR(10) NULL,
    `otp_expires_at` DATETIME NULL,
    `remember_token` VARCHAR(255) NULL,
    `last_login` DATETIME NULL,
    `login_attempts` INT NOT NULL DEFAULT 0,
    `locked_until` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STUDENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(15) NULL,
    `dob` DATE NULL,
    `gender` ENUM('male', 'female', 'other') NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `state` VARCHAR(100) NULL,
    `pincode` VARCHAR(10) NULL,
    `profile_photo` VARCHAR(255) NULL,
    `enrollment_no` VARCHAR(50) NULL,
    `branch` VARCHAR(100) NULL,
    `degree` VARCHAR(50) DEFAULT 'B.Tech',
    `admission_year` INT NULL,
    `passing_year` INT NULL,
    `cgpa` DECIMAL(4,2) NULL,
    `tenth_percentage` DECIMAL(5,2) NULL,
    `twelfth_percentage` DECIMAL(5,2) NULL,
    `diploma_percentage` DECIMAL(5,2) NULL,
    `backlogs` INT DEFAULT 0,
    `active_backlogs` INT DEFAULT 0,
    `skills` TEXT NULL,
    `bio` TEXT NULL,
    `resume_path` VARCHAR(255) NULL,
    `resume_original_name` VARCHAR(255) NULL,
    `linkedin` VARCHAR(255) NULL,
    `github` VARCHAR(255) NULL,
    `portfolio` VARCHAR(255) NULL,
    `is_placed` TINYINT(1) DEFAULT 0,
    `placed_company` VARCHAR(255) NULL,
    `placed_package` DECIMAL(10,2) NULL,
    `placed_date` DATE NULL,
    `profile_completion` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_branch` (`branch`),
    INDEX `idx_placed` (`is_placed`),
    INDEX `idx_cgpa` (`cgpa`),
    INDEX `idx_passing_year` (`passing_year`),
    INDEX `idx_enrollment` (`enrollment_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- COMPANIES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `companies` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `company_name` VARCHAR(255) NOT NULL,
    `industry` VARCHAR(100) NULL,
    `company_type` ENUM('product', 'service', 'startup', 'mnc', 'government') NULL,
    `employee_count` VARCHAR(50) NULL,
    `established_year` INT NULL,
    `website` VARCHAR(255) NULL,
    `logo` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `state` VARCHAR(100) NULL,
    `country` VARCHAR(100) DEFAULT 'India',
    `contact_person` VARCHAR(150) NULL,
    `contact_email` VARCHAR(255) NULL,
    `contact_phone` VARCHAR(15) NULL,
    `is_approved` TINYINT(1) DEFAULT 0,
    `registered_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_approved` (`is_approved`),
    INDEX `idx_industry` (`industry`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- JOBS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `company_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `job_type` ENUM('full-time', 'internship', 'part-time', 'contract') DEFAULT 'full-time',
    `work_mode` ENUM('onsite', 'remote', 'hybrid') DEFAULT 'onsite',
    `location` VARCHAR(150) NULL,
    `salary_min` DECIMAL(10,2) NULL DEFAULT 0,
    `salary_max` DECIMAL(10,2) NULL DEFAULT 0,
    `openings` INT DEFAULT 1,
    `skills_required` TEXT NULL,
    `experience_required` VARCHAR(100) NULL,
    `eligibility_cgpa` DECIMAL(4,2) DEFAULT 0,
    `eligibility_branches` TEXT NULL,
    `eligibility_backlogs` INT DEFAULT 0,
    `application_deadline` DATE NULL,
    `status` ENUM('pending', 'active', 'closed', 'expired') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    INDEX `idx_status` (`status`),
    INDEX `idx_type` (`job_type`),
    INDEX `idx_deadline` (`application_deadline`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- APPLICATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `applications` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `job_id` INT NOT NULL,
    `status` ENUM('applied', 'shortlisted', 'interview', 'selected', 'rejected', 'withdrawn') DEFAULT 'applied',
    `cover_letter` TEXT NULL,
    `resume_snapshot` VARCHAR(255) NULL,
    `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_application` (`student_id`, `job_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INTERVIEWS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `interviews` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `company_id` INT NOT NULL,
    `job_id` INT NOT NULL,
    `round` VARCHAR(100) DEFAULT 'Round 1',
    `interview_date` DATE NOT NULL,
    `interview_time` TIME NOT NULL,
    `mode` ENUM('online', 'offline') DEFAULT 'offline',
    `venue` VARCHAR(255) NULL,
    `meeting_link` VARCHAR(500) NULL,
    `instructions` TEXT NULL,
    `status` ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    `result` ENUM('pending', 'passed', 'failed') DEFAULT 'pending',
    `feedback` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
    INDEX `idx_date` (`interview_date`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PLACEMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `placements` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `company_id` INT NULL,
    `job_id` INT NULL,
    `package` DECIMAL(10,2) NULL,
    `placement_date` DATE NULL,
    `offer_letter` VARCHAR(255) NULL,
    `status` ENUM('confirmed', 'pending', 'revoked') DEFAULT 'confirmed',
    `remarks` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE SET NULL,
    INDEX `idx_date` (`placement_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TRAININGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `trainings` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `training_type` ENUM('technical', 'soft-skills', 'aptitude', 'workshop', 'seminar') DEFAULT 'technical',
    `mode` ENUM('online', 'offline', 'hybrid') DEFAULT 'offline',
    `venue` VARCHAR(255) NULL,
    `trainer_name` VARCHAR(150) NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `start_time` TIME NULL,
    `end_time` TIME NULL,
    `capacity` INT DEFAULT 50,
    `registered_count` INT DEFAULT 0,
    `faculty_id` INT NULL,
    `status` ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    `certificate_issued` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TRAINING REGISTRATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `training_registrations` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `training_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `status` ENUM('registered', 'attended', 'dropped', 'completed') DEFAULT 'registered',
    `attendance_count` INT DEFAULT 0,
    `certificate_issued` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`training_id`) REFERENCES `trainings`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_registration` (`training_id`, `student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BOOKMARKS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `bookmarks` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `job_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_bookmark` (`student_id`, `job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- NOTIFICATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info', 'success', 'warning', 'danger', 'announcement') DEFAULT 'info',
    `category` ENUM('system', 'job', 'interview', 'placement', 'training', 'announcement') DEFAULT 'system',
    `is_read` TINYINT(1) DEFAULT 0,
    `is_global` TINYINT(1) DEFAULT 0,
    `link` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_read` (`is_read`),
    INDEX `idx_global` (`is_global`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DOCUMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `documents` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `document_type` ENUM('certificate', 'marksheet', 'id_proof', 'offer_letter', 'other') DEFAULT 'other',
    `file_path` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `file_size` INT NULL,
    `mime_type` VARCHAR(100) NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STUDENT PROJECTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `student_projects` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `technologies` TEXT NULL,
    `project_url` VARCHAR(500) NULL,
    `github_url` VARCHAR(500) NULL,
    `start_date` DATE NULL,
    `end_date` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STUDENT CERTIFICATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `student_certifications` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `issuing_org` VARCHAR(255) NULL,
    `issue_date` DATE NULL,
    `expiry_date` DATE NULL,
    `credential_id` VARCHAR(100) NULL,
    `credential_url` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STUDENT LANGUAGES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `student_languages` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `language` VARCHAR(100) NOT NULL,
    `proficiency` ENUM('beginner', 'intermediate', 'advanced', 'native') DEFAULT 'intermediate',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STUDENT ACHIEVEMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `student_achievements` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `date` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FACULTY TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `faculty` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(15) NULL,
    `department` VARCHAR(100) NULL,
    `designation` VARCHAR(100) NULL,
    `specialization` TEXT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- UNIVERSITIES TABLE (Higher Studies)
-- =====================================================
CREATE TABLE IF NOT EXISTS `universities` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `city` VARCHAR(100) NULL,
    `country` VARCHAR(100) DEFAULT 'India',
    `ranking` INT NULL,
    `website` VARCHAR(500) NULL,
    `description` TEXT NULL,
    `admission_deadline` DATE NULL,
    `course_count` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- COURSES TABLE (University Programs)
-- =====================================================
CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `university_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `duration` VARCHAR(100) NULL,
    `degree_type` VARCHAR(100) NULL,
    `description` TEXT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE CASCADE,
    INDEX `idx_university` (`university_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ENTRANCE EXAMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `entrance_exams` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(500) NULL,
    `conducting_body` VARCHAR(255) NULL,
    `exam_date` DATE NULL,
    `registration_deadline` DATE NULL,
    `website` VARCHAR(500) NULL,
    `description` TEXT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SCHOLARSHIPS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `scholarships` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `provider` VARCHAR(255) NULL,
    `amount` DECIMAL(12,2) NULL,
    `currency` VARCHAR(10) DEFAULT 'INR',
    `type` ENUM('merit', 'need-based', 'research', 'sport', 'other') DEFAULT 'merit',
    `eligibility` TEXT NULL,
    `application_deadline` DATE NULL,
    `website` VARCHAR(500) NULL,
    `description` TEXT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- HIGHER STUDY APPLICATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `higher_study_applications` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `university_id` INT NULL,
    `course_id` INT NULL,
    `university_name` VARCHAR(255) NOT NULL,
    `country` VARCHAR(100) NULL,
    `course_name` VARCHAR(255) NULL,
    `exam_score` VARCHAR(50) NULL,
    `status` ENUM('interested', 'applied', 'accepted', 'rejected', 'enrolled') DEFAULT 'interested',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ACTIVITY LOGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `module` VARCHAR(100) NULL,
    `description` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PASSWORD RESETS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `used` TINYINT(1) NOT NULL DEFAULT 0,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DEFAULT ADMIN USER
-- Password: Admin@123
-- =====================================================
INSERT INTO `users` (`email`, `password`, `role`, `status`, `email_verified`) VALUES
('admin@tpms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 1);

-- =====================================================
-- SAMPLE DATA FOR TESTING
-- =====================================================

-- Sample Students
INSERT INTO `users` (`email`, `password`, `role`, `status`, `email_verified`) VALUES
('student1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', 1),
('student2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', 1),
('student3@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', 1);

INSERT INTO `students` (`user_id`, `first_name`, `last_name`, `phone`, `dob`, `gender`, `branch`, `degree`, `enrollment_no`, `admission_year`, `passing_year`, `cgpa`, `tenth_percentage`, `twelfth_percentage`, `skills`, `bio`, `profile_completion`, `city`, `state`) VALUES
(2, 'Rahul', 'Sharma', '9876543210', '2001-05-15', 'male', 'Computer Science', 'B.Tech', 'CS2021001', 2021, 2025, 8.50, 92.00, 88.50, 'Java, Python, React, MySQL, Data Structures', 'Passionate software developer with expertise in full-stack development.', 75, 'Indore', 'Madhya Pradesh'),
(3, 'Priya', 'Patel', '9876543211', '2001-08-22', 'female', 'Information Technology', 'B.Tech', 'IT2021002', 2021, 2025, 9.10, 95.00, 91.00, 'Python, Django, Machine Learning, TensorFlow', 'AI/ML enthusiast with strong analytical skills.', 80, 'Ahmedabad', 'Gujarat'),
(4, 'Amit', 'Kumar', '9876543212', '2002-01-10', 'male', 'Electronics', 'B.Tech', 'EC2021003', 2021, 2025, 7.80, 85.00, 82.00, 'Embedded Systems, IoT, C++, VHDL', 'Electronics engineer with interest in IoT systems.', 65, 'Delhi', 'Delhi');

-- Sample Companies
INSERT INTO `users` (`email`, `password`, `role`, `status`, `email_verified`) VALUES
('hr@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'company', 'active', 1),
('hr@innovatesoft.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'company', 'active', 1);

INSERT INTO `companies` (`user_id`, `company_name`, `industry`, `company_type`, `website`, `city`, `state`, `country`, `contact_person`, `contact_phone`, `contact_email`, `is_approved`, `employee_count`, `description`) VALUES
(5, 'TechCorp Solutions', 'Information Technology', 'product', 'https://techcorp.example.com', 'Bangalore', 'Karnataka', 'India', 'Rajesh Menon', '9988776655', 'rajesh@techcorp.com', 1, '1000-5000', 'Leading product-based company specializing in enterprise software solutions and cloud services.'),
(6, 'InnovateSoft', 'Information Technology', 'startup', 'https://innovatesoft.example.com', 'Pune', 'Maharashtra', 'India', 'Sneha Gupta', '9988776644', 'sneha@innovatesoft.com', 1, '50-200', 'Fast-growing startup building next-generation AI-powered business tools.');

-- Sample Jobs
INSERT INTO `jobs` (`company_id`, `title`, `description`, `job_type`, `work_mode`, `location`, `salary_min`, `salary_max`, `openings`, `skills_required`, `eligibility_cgpa`, `eligibility_branches`, `application_deadline`, `status`) VALUES
(1, 'Software Engineer', 'Design, develop, and maintain scalable web applications. Work with modern tech stack including React, Node.js, and cloud services. Participate in code reviews and agile ceremonies.', 'full-time', 'hybrid', 'Bangalore', 6.00, 12.00, 5, 'Java, Python, React, SQL, Git', 7.00, 'Computer Science, Information Technology', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'active'),
(1, 'Data Analyst Intern', 'Analyze business data to provide actionable insights. Work with SQL, Python, and BI tools. Create dashboards and reports for stakeholders.', 'internship', 'remote', 'Remote', 0.30, 0.50, 3, 'Python, SQL, Excel, Tableau', 6.50, '', DATE_ADD(CURDATE(), INTERVAL 20 DAY), 'active'),
(2, 'Full Stack Developer', 'Build end-to-end features for our SaaS platform. Strong knowledge of React, Node.js, and PostgreSQL required. Experience with microservices architecture is a plus.', 'full-time', 'onsite', 'Pune', 5.00, 10.00, 4, 'React, Node.js, PostgreSQL, Docker', 7.50, 'Computer Science, Information Technology', DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'active'),
(2, 'UI/UX Design Intern', 'Design intuitive user interfaces for web and mobile applications. Create wireframes, prototypes, and high-fidelity mockups using Figma.', 'internship', 'hybrid', 'Pune', 0.25, 0.40, 2, 'Figma, UI/UX, Prototyping, CSS', 6.00, '', DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'active');

-- Sample Applications
INSERT INTO `applications` (`student_id`, `job_id`, `status`, `applied_at`) VALUES
(1, 1, 'shortlisted', NOW() - INTERVAL 5 DAY),
(2, 1, 'applied', NOW() - INTERVAL 3 DAY),
(1, 3, 'applied', NOW() - INTERVAL 2 DAY),
(3, 2, 'applied', NOW() - INTERVAL 1 DAY);

-- Sample Training
INSERT INTO `trainings` (`title`, `description`, `training_type`, `mode`, `venue`, `trainer_name`, `start_date`, `end_date`, `capacity`, `status`, `registered_count`) VALUES
('Advanced Data Structures & Algorithms', 'Master essential DSA concepts for placement preparation. Covers arrays, trees, graphs, dynamic programming, and competitive coding strategies.', 'technical', 'offline', 'Seminar Hall A', 'Prof. Sharma', DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 21 DAY), 60, 'upcoming', 0),
('Communication & Soft Skills Workshop', 'Enhance your interpersonal communication, presentation skills, and group discussion techniques for campus placements.', 'soft-skills', 'offline', 'Training Room 2', 'Dr. Meera Singh', DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 18 DAY), 40, 'upcoming', 0),
('Aptitude & Logical Reasoning', 'Comprehensive aptitude training covering quantitative ability, verbal reasoning, and logical puzzles for placement exams.', 'aptitude', 'online', 'Google Meet', 'Mr. Vikram Rao', DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 100, 'ongoing', 12);

-- Sample Faculty
INSERT INTO `faculty` (`name`, `email`, `phone`, `department`, `designation`, `specialization`) VALUES
('Dr. Rakesh Kumar', 'rakesh@college.edu', '9112233445', 'Computer Science', 'Professor', 'Machine Learning, Data Science'),
('Prof. Anita Desai', 'anita@college.edu', '9112233446', 'Information Technology', 'Associate Professor', 'Web Technologies, Cloud Computing'),
('Dr. Suresh Reddy', 'suresh@college.edu', '9112233447', 'Electronics', 'Professor', 'Embedded Systems, VLSI Design');

-- Sample Universities
INSERT INTO `universities` (`name`, `city`, `country`, `ranking`, `website`, `description`, `admission_deadline`, `course_count`) VALUES
('IIT Delhi', 'New Delhi', 'India', 1, 'https://iitd.ac.in', 'Premier technical institute offering M.Tech, MS, and PhD programs in various engineering disciplines.', DATE_ADD(CURDATE(), INTERVAL 60 DAY), 25),
('MIT', 'Cambridge', 'USA', 1, 'https://mit.edu', 'World-renowned university for graduate programs in computer science, engineering, and technology.', DATE_ADD(CURDATE(), INTERVAL 90 DAY), 50),
('IISc Bangalore', 'Bangalore', 'India', 3, 'https://iisc.ac.in', 'India''s top research university offering ME, MTech, and PhD programs.', DATE_ADD(CURDATE(), INTERVAL 45 DAY), 30);

-- Sample Entrance Exams
INSERT INTO `entrance_exams` (`name`, `full_name`, `conducting_body`, `exam_date`, `registration_deadline`, `website`) VALUES
('GATE', 'Graduate Aptitude Test in Engineering', 'IIT', DATE_ADD(CURDATE(), INTERVAL 120 DAY), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'https://gate.iitb.ac.in'),
('GRE', 'Graduate Record Examination', 'ETS', NULL, NULL, 'https://www.ets.org/gre'),
('CAT', 'Common Admission Test', 'IIM', DATE_ADD(CURDATE(), INTERVAL 150 DAY), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'https://iimcat.ac.in');

-- Sample Scholarships
INSERT INTO `scholarships` (`name`, `provider`, `amount`, `currency`, `type`, `eligibility`, `application_deadline`, `website`) VALUES
('INSPIRE Fellowship', 'Department of Science & Technology', 80000.00, 'INR', 'merit', 'Top 1% in 12th board exam, pursuing BSc/Int. MSc', DATE_ADD(CURDATE(), INTERVAL 45 DAY), 'https://online-inspire.gov.in'),
('Google Scholarship for Women in Tech', 'Google', 2500.00, 'USD', 'merit', 'Female students pursuing CS/IT with CGPA > 8.0', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'https://buildyourfuture.withgoogle.com');

-- Sample Notifications
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `category`, `is_global`) VALUES
(NULL, 'Welcome to TPMS!', 'The Training & Placement Management System is now live. Students can browse and apply for jobs, track applications, and register for training programs.', 'announcement', 'system', 1),
(NULL, 'New Companies Registered', 'TechCorp Solutions and InnovateSoft have joined the placement drive. Check out their job openings!', 'info', 'job', 1),
(2, 'Application Shortlisted', 'Your application for Software Engineer at TechCorp Solutions has been shortlisted. Prepare for the next round!', 'success', 'job', 0);

-- Sample Placements
INSERT INTO `placements` (`student_id`, `company_id`, `job_id`, `package`, `placement_date`, `status`) VALUES
(1, 1, 1, 8.50, CURDATE() - INTERVAL 10 DAY, 'confirmed');

-- Update student as placed
UPDATE `students` SET `is_placed` = 1, `placed_company` = 'TechCorp Solutions', `placed_package` = 8.50, `placed_date` = CURDATE() - INTERVAL 10 DAY WHERE `id` = 1;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
