-- Admins table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Songs table
CREATE TABLE IF NOT EXISTS `songs` (
    `id` VARCHAR(36) NOT NULL PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `artist` VARCHAR(255) NOT NULL,
    `source_type` ENUM('youtube', 'upload', 'url', 'midi', 'kar', 'mp3', 'mp4', 'webm') NOT NULL DEFAULT 'upload',
    `source_url` TEXT NULL,
    `file_path` VARCHAR(500) NULL,
    `thumbnail` VARCHAR(500) NULL,
    `duration` INT UNSIGNED NULL,
    `lyrics` TEXT NULL,
    `plays_count` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_title` (`title`),
    INDEX `idx_artist` (`artist`),
    INDEX `idx_source_type` (`source_type`),
    INDEX `idx_created_at` (`created_at`),
    FULLTEXT INDEX `ft_search` (`title`, `artist`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scores table
CREATE TABLE IF NOT EXISTS `scores` (
    `id` VARCHAR(36) NOT NULL PRIMARY KEY,
    `song_id` VARCHAR(36) NOT NULL,
    `player_name` VARCHAR(100) NOT NULL,
    `score` INT UNSIGNED NOT NULL DEFAULT 0,
    `accuracy` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `perfect_hits` INT UNSIGNED DEFAULT 0,
    `good_hits` INT UNSIGNED DEFAULT 0,
    `miss_hits` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_song_id` (`song_id`),
    INDEX `idx_score` (`score`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`song_id`) REFERENCES `songs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table (for site configuration)
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'NeonVox'),
('site_description', 'The Ultimate Karaoke Experience'),
('youtube_api_key', ''),
('max_upload_size', '104857600'),
('enable_registration', '0');
