DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `actor_id` int(10) unsigned NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'reply',
  `comment_id` int(10) unsigned DEFAULT NULL,
  `manga_id` int(10) unsigned DEFAULT NULL,
  `manga_slug` varchar(255) DEFAULT NULL,
  `manga_name` varchar(255) DEFAULT NULL,
  `chapter_slug` varchar(255) NOT NULL DEFAULT '',
  `preview` varchar(200) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_unread` (`user_id`,`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
