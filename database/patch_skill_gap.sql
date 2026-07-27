-- =====================================================
-- TPMS - AI Skill Gap Analysis & Recommended Courses Schema Patch
-- =====================================================

USE `team1`;

-- 1. Create skill_gap_analysis table
CREATE TABLE IF NOT EXISTS `skill_gap_analysis` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `job_id` INT NOT NULL,
    `matched_skills` TEXT NULL,
    `missing_skills` TEXT NULL,
    `skill_match_percentage` DECIMAL(5,2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_student_job_gap` (`student_id`, `job_id`),
    INDEX `idx_student_gap` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create recommended_courses table
CREATE TABLE IF NOT EXISTS `recommended_courses` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `skill_name` VARCHAR(100) NOT NULL,
    `course_name` VARCHAR(255) NOT NULL,
    `platform` VARCHAR(100) NOT NULL,
    `instructor` VARCHAR(150) NULL,
    `course_url` VARCHAR(500) NOT NULL,
    `difficulty` ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner',
    `duration` VARCHAR(100) NOT NULL,
    `rating` DECIMAL(3,2) DEFAULT 4.5,
    `is_free` TINYINT(1) DEFAULT 1,
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_skill` (`skill_name`),
    INDEX `idx_platform` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create student_learning_progress table
CREATE TABLE IF NOT EXISTS `student_learning_progress` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `status` ENUM('enrolled', 'completed') DEFAULT 'enrolled',
    `progress` INT DEFAULT 0,
    `completed_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `recommended_courses`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_student_course` (`student_id`, `course_id`),
    INDEX `idx_student_prog` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Seed High Quality Course Catalog
INSERT IGNORE INTO `recommended_courses` (`id`, `skill_name`, `course_name`, `platform`, `instructor`, `course_url`, `difficulty`, `duration`, `rating`, `is_free`, `description`) VALUES
(1, 'Spring Boot', 'Spring Boot 3 & Spring Framework 6 Masterclass', 'Udemy', 'In22Minutes', 'https://www.udemy.com/course/spring-boot-tutorial-for-beginners/', 'Intermediate', '30 Hours', 4.70, 0, 'Learn Spring Boot, REST APIs, Spring Security, Hibernate, and Microservices.'),
(2, 'Spring Boot', 'Spring Framework Specialization', 'Coursera', 'Eugen Paraschiv', 'https://www.coursera.org/specializations/spring-framework', 'Intermediate', '4 Weeks', 4.60, 1, 'Master Spring Boot fundamentals, dependency injection, and data persistence.'),
(3, 'Docker', 'Docker for Beginners - Hands On!', 'freeCodeCamp', 'KodeKloud', 'https://www.youtube.com/watch?v=fqMOX6JJhGo', 'Beginner', '3 Hours', 4.80, 1, 'Complete hands-on Docker containerization, Dockerfile, Compose, and networking guide.'),
(4, 'Docker', 'Docker & Kubernetes: The Practical Guide', 'Udemy', 'Maximilian Schwarzmüller', 'https://www.udemy.com/course/docker-kubernetes-the-practical-guide/', 'Intermediate', '23 Hours', 4.85, 0, 'Master Docker, Docker Compose, Multi-container apps, and Kubernetes deployment.'),
(5, 'Git', 'Git & GitHub Crash Course for Beginners', 'freeCodeCamp', 'Traversy Media', 'https://www.youtube.com/watch?v=SWYqp7iY_Tc', 'Beginner', '2 Hours', 4.90, 1, 'Learn version control, commits, branching, merging, pull requests, and GitHub workflows.'),
(6, 'Git', 'Version Control with Git', 'Coursera', 'Atlassian', 'https://www.coursera.org/learn/version-control-with-git', 'Beginner', '3 Weeks', 4.70, 1, 'Official Atlassian course on Git repository management, team collaboration, and branching strategies.'),
(7, 'AWS', 'AWS Certified Cloud Practitioner Training', 'AWS Skill Builder', 'AWS Experts', 'https://explore.skillbuilder.aws/', 'Beginner', '6 Hours', 4.80, 1, 'Official free AWS foundation course covering S3, EC2, IAM, Lambda, and Cloud fundamentals.'),
(8, 'AWS', 'Ultimate AWS Certified Solutions Architect Associate', 'Udemy', 'Stephane Maarek', 'https://www.udemy.com/course/aws-certified-solutions-architect-associate-saa-c03/', 'Intermediate', '27 Hours', 4.88, 0, 'In-depth AWS architecture design, High Availability, VPC, Serverless, and IAM security.'),
(9, 'React', 'React - The Complete Guide (incl. React Router & Redux)', 'Udemy', 'Maximilian Schwarzmüller', 'https://www.udemy.com/course/react-the-complete-guide-incl-redux/', 'Intermediate', '48 Hours', 4.80, 0, 'Build modern React applications with Hooks, Redux Toolkit, Context API, and Next.js.'),
(10, 'React', 'React Official Interactive Tutorial', 'freeCodeCamp', 'Scrimba / FCC', 'https://www.freecodecamp.org/learn/front-end-development-libraries/#react', 'Beginner', '12 Hours', 4.90, 1, 'Free interactive React certification course covering JSX, Components, State, Props, and Effects.'),
(11, 'Node.js', 'Node.js, Express & MongoDB Dev to Deployment', 'freeCodeCamp', 'Brad Traversy', 'https://www.youtube.com/watch?v=Oe421EPjeBE', 'Intermediate', '5 Hours', 4.85, 1, 'Build production RESTful APIs with Node.js, Express framework, JWT authentication, and MongoDB.'),
(12, 'Python', 'Python for Everybody Specialization', 'Coursera', 'Dr. Charles Severance', 'https://www.coursera.org/specializations/python', 'Beginner', '3 Months', 4.80, 1, 'University of Michigan famous course covering Python data structures, web scraping, and databases.'),
(13, 'MySQL', 'Database Management System (DBMS)', 'NPTEL', 'Prof. Partha Pratim Das', 'https://nptel.ac.in/courses/106105175', 'Intermediate', '8 Weeks', 4.60, 1, 'Official NPTEL IIT course covering Relational Algebra, SQL queries, Normalization, and Transactions.'),
(14, 'Java', 'Java Programming Masterclass for Software Developers', 'Udemy', 'Tim Buchalka', 'https://www.udemy.com/course/java-the-complete-java-developer-course/', 'Beginner', '80 Hours', 4.70, 0, 'Complete Java 17/21 OOP, Data Structures, Collections, Threads, and Lambdas guide.'),
(15, 'Kubernetes', 'Kubernetes Course for Beginners', 'freeCodeCamp', 'Nana Janashia', 'https://www.youtube.com/watch?v=X48VuDVv0do', 'Intermediate', '4 Hours', 4.90, 1, 'Learn Kubernetes Pods, Deployments, Services, Ingress, and Helm Charts hands-on.');
