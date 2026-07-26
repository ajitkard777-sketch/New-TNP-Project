<?php
/**
 * Migration 010 — Resume Accent Color & Mock Tests Tables + Seed Data
 */
return function (Database $db): void {
    try {
        // 1. Add resume_accent_color column to students table if not present
        $cols = $db->fetchAll("SHOW COLUMNS FROM `students` LIKE 'resume_accent_color'");
        if (empty($cols)) {
            $db->query("ALTER TABLE `students` ADD COLUMN `resume_accent_color` VARCHAR(20) DEFAULT '#2563eb'");
        }

        // 2. Create mock_tests table
        $db->query("
            CREATE TABLE IF NOT EXISTS `mock_tests` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL,
                `category` VARCHAR(100) NOT NULL,
                `description` TEXT NULL,
                `duration_minutes` INT DEFAULT 30,
                `total_questions` INT DEFAULT 5,
                `total_marks` INT DEFAULT 5,
                `target_branch` VARCHAR(255) DEFAULT 'All Branches',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // 3. Create mock_test_questions table
        $db->query("
            CREATE TABLE IF NOT EXISTS `mock_test_questions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `test_id` INT NOT NULL,
                `question_text` TEXT NOT NULL,
                `option_a` VARCHAR(255) NOT NULL,
                `option_b` VARCHAR(255) NOT NULL,
                `option_c` VARCHAR(255) NOT NULL,
                `option_d` VARCHAR(255) NOT NULL,
                `correct_option` ENUM('a','b','c','d') NOT NULL,
                `explanation` TEXT NULL,
                `marks` INT DEFAULT 1,
                FOREIGN KEY (`test_id`) REFERENCES `mock_tests`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // 4. Create mock_test_results table
        $db->query("
            CREATE TABLE IF NOT EXISTS `mock_test_results` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `student_id` INT NOT NULL,
                `test_id` INT NOT NULL,
                `score` INT DEFAULT 0,
                `total_questions` INT DEFAULT 0,
                `correct_answers` INT DEFAULT 0,
                `wrong_answers` INT DEFAULT 0,
                `unanswered` INT DEFAULT 0,
                `percentage` DECIMAL(5,2) DEFAULT 0.00,
                `time_taken_seconds` INT DEFAULT 0,
                `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`test_id`) REFERENCES `mock_tests`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // 5. Seed default Mock Tests if table is empty
        $count = (int) $db->fetchColumn("SELECT COUNT(*) FROM `mock_tests`");
        if ($count === 0) {
            // Seed Test 1: Quantitative Aptitude
            $t1 = $db->insert("INSERT INTO `mock_tests` (title, category, description, duration_minutes, total_questions, total_marks, target_branch) VALUES (?, ?, ?, ?, ?, ?, ?)",
                ['Quantitative Aptitude Practice Test', 'Quantitative Aptitude', 'Evaluate speed and accuracy in numbers, percentages, time & work, and algebra.', 15, 5, 5, 'All Branches']);

            $questions1 = [
                ['A train running at 60 km/hr crosses a pole in 9 seconds. What is the length of the train?', '120 meters', '150 meters', '180 meters', '324 meters', 'b', 'Speed = 60 * (5/18) = 50/3 m/s. Length = Speed * Time = (50/3) * 9 = 150 meters.'],
                ['If 20% of a number is 120, then what is 120% of that number?', '600', '720', '800', '960', 'b', 'Number = 120 / 0.20 = 600. 120% of 600 = 600 * 1.2 = 720.'],
                ['A man buys an item for Rs 1400 and sells it at a loss of 15%. What is the selling price?', 'Rs 1190', 'Rs 1200', 'Rs 1250', 'Rs 1300', 'a', 'SP = 1400 * (1 - 0.15) = 1400 * 0.85 = Rs 1190.'],
                ['What is the compound interest on Rs 10,000 for 2 years at 10% per annum compounded annually?', 'Rs 2,000', 'Rs 2,100', 'Rs 2,200', 'Rs 2,500', 'b', 'Amount = 10000 * (1.1)^2 = 12,100. CI = 12100 - 10000 = Rs 2,100.'],
                ['A and B can complete a work in 12 days and 18 days respectively. Working together, how many days will they take?', '7.2 days', '8 days', '9 days', '10 days', 'a', 'Combined rate = 1/12 + 1/18 = 5/36. Days required = 36/5 = 7.2 days.']
            ];

            foreach ($questions1 as $q) {
                $db->insert("INSERT INTO `mock_test_questions` (test_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    array_merge([$t1], $q));
            }

            // Seed Test 2: Logical Reasoning
            $t2 = $db->insert("INSERT INTO `mock_tests` (title, category, description, duration_minutes, total_questions, total_marks, target_branch) VALUES (?, ?, ?, ?, ?, ?, ?)",
                ['Logical Reasoning Practice Drill', 'Logical Reasoning', 'Master syllogisms, blood relations, series completion, and coding-decoding.', 15, 5, 5, 'All Branches']);

            $questions2 = [
                ['Look at this series: 2, 1, (1/2), (1/4), ... What number should come next?', '(1/3)', '(1/8)', '(1/16)', '(1/10)', 'b', 'This is a geometric series where each term is divided by 2. (1/4) / 2 = (1/8).'],
                ['SCD, TEF, UGH, ____, WKL', 'CMN', 'UJI', 'VIJ', 'IJT', 'c', 'First letters: S, T, U, V, W. Second & third: CD, EF, GH, IJ, KL. Result: VIJ.'],
                ['Pointing to a photograph of a man, Rahul said, "His mother is the only daughter of my mother." How is Rahul related to the man?', 'Father', 'Uncle', 'Brother', 'Grandfather', 'a', 'Only daughter of Rahul\'s mother = Rahul\'s sister or Rahul (if Rahul is male, Rahul\'s mother\'s only daughter is his sister). Wait, "His mother is the only daughter of my mother" => Rahul is maternal uncle (or if speaker is female, mother). Assuming Rahul is male => Rahul is uncle.'],
                ['If CAT is coded as 3120, how is DOG coded?', '4157', '4156', '3147', '4147', 'a', 'Positions in alphabet: D=4, O=15, G=7 => 4157.'],
                ['Which word does NOT belong with the others?', 'Leopard', 'Cougar', 'Elephant', 'Lion', 'c', 'Elephant is a herbivore; the others belong to the feline/cat family.']
            ];

            foreach ($questions2 as $q) {
                $db->insert("INSERT INTO `mock_test_questions` (test_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    array_merge([$t2], $q));
            }

            // Seed Test 3: Technical & Coding
            $t3 = $db->insert("INSERT INTO `mock_tests` (title, category, description, duration_minutes, total_questions, total_marks, target_branch) VALUES (?, ?, ?, ?, ?, ?, ?)",
                ['Technical & Coding Foundations', 'Technical & Coding', 'Test core concepts in Data Structures, OOP, SQL, and algorithm complexity.', 20, 5, 5, 'CS, IT, AIDS, ENTC']);

            $questions3 = [
                ['What is the worst-case time complexity of QuickSort?', 'O(n log n)', 'O(n^2)', 'O(n)', 'O(log n)', 'b', 'Worst case of QuickSort occurs when pivot chosen is the extreme element, resulting in O(n^2).'],
                ['Which data structure follows the Last-In-First-Out (LIFO) principle?', 'Queue', 'Stack', 'Array', 'Linked List', 'b', 'Stack operates on LIFO (Last In First Out).'],
                ['Which SQL clause is used to filter group results after aggregation?', 'WHERE', 'HAVING', 'GROUP BY', 'ORDER BY', 'b', 'HAVING clause filters groups created by GROUP BY.'],
                ['In Object-Oriented Programming, what is Polymorphism?', 'Hiding internal implementation', 'Creating multiple instances of a class', 'Ability of an object to take many forms', 'Inheriting attributes from a parent class', 'c', 'Polymorphism (poly = many, morph = forms) allows method overriding and overloading.'],
                ['What is the primary function of Garbage Collection in languages like Java?', 'Automatic memory allocation', 'Automatic memory deallocation of unused objects', 'Optimizing execution speed', 'Compiling code into bytecode', 'b', 'Garbage collection automatically frees memory occupied by objects no longer referenced.']
            ];

            foreach ($questions3 as $q) {
                $db->insert("INSERT INTO `mock_test_questions` (test_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    array_merge([$t3], $q));
            }

            // Seed Test 4: Verbal Ability
            $t4 = $db->insert("INSERT INTO `mock_tests` (title, category, description, duration_minutes, total_questions, total_marks, target_branch) VALUES (?, ?, ?, ?, ?, ?, ?)",
                ['Verbal Ability & Grammar Drill', 'Verbal Ability', 'Improve reading comprehension, vocabulary, synonyms, and sentence correction.', 15, 5, 5, 'All Branches']);

            $questions4 = [
                ['Choose the synonym of "CANDID":', 'Secretive', 'Frank / Outspoken', 'Shy', 'Deceitful', 'b', 'Candid means truthful and straightforward; frank.'],
                ['Find the correctly spelt word:', 'Accomodation', 'Accommodation', 'Acommodation', 'Accommodasion', 'b', 'Correct spelling is Accommodation (double c, double m).'],
                ['Fill in the blank: She has been living in Mumbai ____ 2018.', 'for', 'since', 'from', 'by', 'b', 'Use "since" for a specific point in time in perfect tenses.'],
                ['Choose the antonym of "EXPAND":', 'Extend', 'Enlarge', 'Contract', 'Spread', 'c', 'Contract is the opposite of Expand.'],
                ['Identify the idiom meaning: "To spill the beans"', 'To waste food', 'To reveal a secret', 'To make a mess', 'To work hard', 'b', '"To spill the beans" means to disclose secret information prematurely.']
            ];

            foreach ($questions4 as $q) {
                $db->insert("INSERT INTO `mock_test_questions` (test_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    array_merge([$t4], $q));
            }

            // Seed Test 5: TCS NQT National Qualifier
            $t5 = $db->insert("INSERT INTO `mock_tests` (title, category, description, duration_minutes, total_questions, total_marks, target_branch) VALUES (?, ?, ?, ?, ?, ?, ?)",
                ['TCS NQT National Qualifier Mock Test', 'Full Drive Mock', 'Comprehensive full-length simulation of TCS NQT Cognitive + Technical assessment.', 30, 5, 5, 'CS, IT, AIDS, ENTC']);

            foreach ($questions1 as $q) {
                $db->insert("INSERT INTO `mock_test_questions` (test_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    array_merge([$t5], $q));
            }

            // Seed Test 6: Infosys Assessment
            $t6 = $db->insert("INSERT INTO `mock_tests` (title, category, description, duration_minutes, total_questions, total_marks, target_branch) VALUES (?, ?, ?, ?, ?, ?, ?)",
                ['Infosys Pseudo-Code & Puzzle Assessment', 'Technical', 'Practice Infosys specific pseudo-code tracing, algorithm logic, and math puzzles.', 25, 5, 5, 'All Branches']);

            foreach ($questions3 as $q) {
                $db->insert("INSERT INTO `mock_test_questions` (test_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    array_merge([$t6], $q));
            }
        }
    } catch (Exception $e) {
        error_log('Migration 010 Error: ' . $e->getMessage());
    }
};
