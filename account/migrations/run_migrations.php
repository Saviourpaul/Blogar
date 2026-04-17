<?php
/*
 * Database Migration Setup for Comment System
 * This script adds the necessary tables and columns for the Reddit-style comment system
 * Run this script once to initialize the database structure
 */

require_once __DIR__ . '/../config/database.php';

$migration_functions = [
    'add_comment_interactions' => function($connection) {
        // Add columns to comments table
        $columns_to_add = [
            'edited_at' => "ALTER TABLE comments ADD COLUMN edited_at TIMESTAMP NULL DEFAULT NULL AFTER created_at",
            'is_edited' => "ALTER TABLE comments ADD COLUMN is_edited BOOLEAN DEFAULT FALSE AFTER edited_at",
            'edit_expires_at' => "ALTER TABLE comments ADD COLUMN edit_expires_at TIMESTAMP NULL DEFAULT NULL AFTER is_edited",
            'deleted_at' => "ALTER TABLE comments ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER edit_expires_at",
            'deleted_by' => "ALTER TABLE comments ADD COLUMN deleted_by INT NULL DEFAULT NULL AFTER deleted_at"
        ];

        foreach ($columns_to_add as $column_name => $sql) {
            // Check if column exists
            $result = $connection->query("SHOW COLUMNS FROM comments LIKE '$column_name'");
            if ($result && $result->num_rows === 0) {
                if (!$connection->query($sql)) {
                    throw new Exception("Error adding column $column_name: " . $connection->error);
                }
                echo "✓ Added column $column_name to comments table<br>";
            } else {
                echo "✓ Column $column_name already exists<br>";
            }
        }

        // Add optional settings support for configurable edit window
        $settingsResult = $connection->query("SHOW COLUMNS FROM settings LIKE 'comment_edit_window'");
        if ($settingsResult && $settingsResult->num_rows === 0) {
            $settingsSql = "ALTER TABLE settings ADD COLUMN comment_edit_window INT NOT NULL DEFAULT 15 AFTER enable_comment";
            if (!$connection->query($settingsSql)) {
                throw new Exception("Error adding comment_edit_window to settings: " . $connection->error);
            }
            echo "âœ“ Added comment_edit_window to settings table<br>";
        } else {
            echo "âœ“ Column comment_edit_window already exists in settings<br>";
        }

        // Create comment_interactions table
        $sql = "CREATE TABLE IF NOT EXISTS `comment_interactions` (
          `id` int NOT NULL AUTO_INCREMENT,
          `comment_id` int NOT NULL,
          `user_id` int NULL,
          `interaction_type` enum('like','dislike','share') NOT NULL,
          `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `unique_interaction` (`comment_id`, `user_id`, `interaction_type`),
          KEY `idx_comment_id` (`comment_id`),
          KEY `idx_user_id` (`user_id`),
          CONSTRAINT `fk_comment_interactions_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$connection->query($sql)) {
            // Table might already exist, check it
            $result = $connection->query("SHOW TABLES LIKE 'comment_interactions'");
            if ($result && $result->num_rows > 0) {
                echo "✓ comment_interactions table already exists<br>";
            } else {
                throw new Exception("Error creating comment_interactions table: " . $connection->error);
            }
        } else {
            echo "✓ Created comment_interactions table<br>";
        }

        // Create comment_edits table
        $sql = "CREATE TABLE IF NOT EXISTS `comment_edits` (
          `id` int NOT NULL AUTO_INCREMENT,
          `comment_id` int NOT NULL,
          `edited_by` int NULL,
          `previous_content` longtext NOT NULL,
          `new_content` longtext NOT NULL,
          `edited_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_comment_id` (`comment_id`),
          KEY `idx_edited_by` (`edited_by`),
          CONSTRAINT `fk_comment_edits_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
          CONSTRAINT `fk_comment_edits_user` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$connection->query($sql)) {
            // Table might already exist, check it
            $result = $connection->query("SHOW TABLES LIKE 'comment_edits'");
            if ($result && $result->num_rows > 0) {
                echo "✓ comment_edits table already exists<br>";
            } else {
                throw new Exception("Error creating comment_edits table: " . $connection->error);
            }
        } else {
            echo "✓ Created comment_edits table<br>";
        }

        // Add indexes for performance
        $indexes = [
            'idx_post_parent' => "ALTER TABLE comments ADD INDEX idx_post_parent (post_id, parent_id)",
            'idx_comments_user' => "ALTER TABLE comments ADD INDEX idx_comments_user (user_id)",
            'idx_comments_created' => "ALTER TABLE comments ADD INDEX idx_comments_created (created_at)",
            'idx_comments_deleted' => "ALTER TABLE comments ADD INDEX idx_comments_deleted (deleted_at)"
        ];

        foreach ($indexes as $index_name => $sql) {
            $result = $connection->query("SHOW INDEX FROM comments WHERE Key_name = '$index_name'");
            if ($result && $result->num_rows === 0) {
                if (!$connection->query($sql)) {
                    // Silently fail, index might already exist with different name
                }
            }
        }

        echo "✓ All indexes processed<br>";
    }
];

// Run migrations
if (PHP_SAPI === 'cli') {
    // Running from command line
    if (isset($argv[1]) && $argv[1] === 'rollback') {
        echo "Rollback feature not yet implemented\n";
    } else {
        try {
            echo "========================================\n";
            echo "Running Database Migrations\n";
            echo "========================================\n\n";
            
            foreach ($migration_functions as $name => $function) {
                echo "Running migration: $name\n";
                $function($connection);
                echo "\n";
            }
            
            echo "========================================\n";
            echo "All migrations completed successfully!\n";
            echo "========================================\n";
        } catch (Exception $e) {
            echo "✗ Migration failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
} else {
    // Running from web request
    echo '<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .header { background-color: #f0f0f0; padding: 10px; border-radius: 5px; }
    </style>';
    echo '<div class="header"><h2>Database Migration Setup</h2></div>';
    echo '<pre style="background-color: #f9f9f9; padding: 10px; border-radius: 5px;">';
    
    try {
        foreach ($migration_functions as $name => $function) {
            echo "Running migration: $name\n";
            $function($connection);
            echo "\n";
        }
        echo '<span class="success">✓ All migrations completed successfully!</span>';
    } catch (Exception $e) {
        echo '<span class="error">✗ Migration failed: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
    
    echo '</pre>';
}
