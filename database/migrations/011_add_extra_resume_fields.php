<?php
/**
 * Migration 011 — Add extra resume fields to students table
 */
return function (Database $db): void {
    try {
        $fields = [
            'soft_skills' => 'TEXT NULL',
            'hobbies_interests' => 'TEXT NULL',
            'extracurriculars' => 'TEXT NULL',
            'responsibilities' => 'TEXT NULL',
            'references_info' => 'TEXT NULL'
        ];

        foreach ($fields as $col => $type) {
            $cols = $db->fetchAll("SHOW COLUMNS FROM `students` LIKE ?", [$col]);
            if (empty($cols)) {
                $db->query("ALTER TABLE `students` ADD COLUMN `{$col}` {$type}");
            }
        }
    } catch (Exception $e) {
        error_log('Migration 011 Error: ' . $e->getMessage());
    }
};
