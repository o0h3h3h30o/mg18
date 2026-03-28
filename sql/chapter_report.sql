-- Run this on production server

-- Clean old sessions (optional, CI3 leftovers)
-- DELETE FROM ci_sessions WHERE timestamp < UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY);

-- Chapter Report table

CREATE TABLE IF NOT EXISTS `chapter_report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chapter_id` int(10) unsigned NOT NULL,
  `manga_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `reason` varchar(255) NOT NULL DEFAULT 'broken_images',
  `note` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('pending','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_chapter_id` (`chapter_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
