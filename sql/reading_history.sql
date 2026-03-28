CREATE TABLE IF NOT EXISTS `reading_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `manga_id` int(11) NOT NULL,
  `chapter_id` int(11) NOT NULL,
  `read_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_manga` (`user_id`, `manga_id`),
  KEY `idx_user_read` (`user_id`, `read_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
