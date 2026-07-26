<?php
/**
 * TPMS - Database Migration Runner
 *
 * Tracks applied migrations in a `_schema_versions` table.
 * Migration files are PHP closures in database/migrations/*.php
 * Safe to call on every page load — skips already-applied migrations.
 */

class Migrator {

    private Database $db;
    private string $migrationsPath;

    public function __construct() {
        $this->db             = Database::getInstance();
        $this->migrationsPath = __DIR__ . '/migrations/';
        $this->ensureVersionsTable();
    }

    /**
     * Create the _schema_versions tracking table if it doesn't exist.
     */
    private function ensureVersionsTable(): void {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `_schema_versions` (
                `id`         INT          PRIMARY KEY AUTO_INCREMENT,
                `migration`  VARCHAR(255) NOT NULL UNIQUE,
                `applied_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Return names of migrations already applied.
     */
    public function getApplied(): array {
        try {
            $rows = $this->db->fetchAll("SELECT migration FROM `_schema_versions` ORDER BY id");
            return array_column($rows, 'migration');
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Return migration file paths that have NOT been applied yet.
     */
    public function getPending(): array {
        $applied = $this->getApplied();
        $files   = glob($this->migrationsPath . '*.php') ?: [];
        sort($files);

        return array_values(array_filter(
            $files,
            fn($f) => !in_array(basename($f, '.php'), $applied, true)
        ));
    }

    /**
     * Run all pending migrations.
     *
     * @param  bool  $verbose  If true, return detailed results; if false, throw on error.
     * @return array           Array of ['name', 'status', 'message'] per migration.
     */
    public function run(bool $verbose = false): array {
        $results = [];

        foreach ($this->getPending() as $file) {
            $name = basename($file, '.php');
            try {
                $migration = require $file;

                if (!is_callable($migration)) {
                    throw new RuntimeException("Migration file must return a callable: {$file}");
                }

                $this->db->beginTransaction();
                $migration($this->db);
                if ($this->db->getConnection()->inTransaction()) {
                    $this->db->commit();
                }

                // Record as applied
                $this->db->insert(
                    "INSERT IGNORE INTO `_schema_versions` (migration) VALUES (?)",
                    [$name]
                );

                $results[] = ['name' => $name, 'status' => 'success', 'message' => 'Applied successfully.'];

            } catch (Exception $e) {
                // Roll back any partial changes from this migration
                try {
                    if ($this->db->getConnection()->inTransaction()) {
                        $this->db->rollback();
                    }
                } catch (Exception $rollbackErr) {
                    // ignore rollback errors
                }

                $results[] = [
                    'name'    => $name,
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ];

                error_log("TPMS Migration Error [{$name}]: " . $e->getMessage());

                if (!$verbose) {
                    throw $e; // re-throw in silent mode so index.php can catch it
                }
                // In verbose (setup.php) mode, continue with the next migration
            }
        }

        return $results;
    }

    /**
     * Run all pending migrations silently (suppress exceptions, just log).
     * Used by index.php startup hook.
     */
    public function runSilent(): void {
        try {
            $pending = $this->getPending();
            if (empty($pending)) return;
            $this->run(false);
        } catch (Exception $e) {
            error_log("TPMS Silent Migration Error: " . $e->getMessage());
        }
    }
}
