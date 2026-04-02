-- Migration: Session DB + Comments
-- Run this on production server

-- =============================================
-- 1. Fix ci_sessions table for CI4 DatabaseHandler
-- =============================================
-- Truncate old CI3 sessions (they're expired anyway)
TRUNCATE TABLE ci_sessions;

-- Widen id column (CI4 adds prefix like "ci_session:xxx")
ALTER TABLE ci_sessions MODIFY id varchar(256) NOT NULL;

-- Add PRIMARY KEY on id (CI4 requires it)
-- Drop existing key if any, then add PK
ALTER TABLE ci_sessions ADD PRIMARY KEY (id);

-- Ensure timestamp index exists
-- ALTER TABLE ci_sessions ADD KEY ci_sessions_timestamp (timestamp);
-- (likely already exists from CI3)

-- =============================================
-- 2. Comments table - already exists, no changes needed
-- =============================================
-- Table structure:
--   id (PK), comment (text), post_id (int), post_type (varchar: 'manga'|'chapter'),
--   user_id (int), parent_comment (int, nullable), created_at, updated_at
-- post_type values: 'manga' for manga page comments, 'chapter' for chapter comments

-- Add index for faster lookups if not exists
ALTER TABLE comments ADD INDEX idx_post (`post_id`, `post_type`) ;
ALTER TABLE comments ADD INDEX idx_parent (`parent_comment`);
-- Note: If indexes already exist, these will error - that's OK, just ignore

-- Add likes/dislikes denormalized columns
ALTER TABLE comments ADD COLUMN `likes` int(10) unsigned NOT NULL DEFAULT 0 AFTER `parent_comment`;
ALTER TABLE comments ADD COLUMN `dislikes` int(10) unsigned NOT NULL DEFAULT 0 AFTER `likes`;

-- =============================================
-- 2b. Comment reactions table (like/dislike tracking)
-- =============================================
CREATE TABLE IF NOT EXISTS `comment_reactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `type` enum('like','dislike') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_comment` (`user_id`, `comment_id`),
  KEY `idx_comment_id` (`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 3. Chapter report table (from previous migration)
-- =============================================
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
