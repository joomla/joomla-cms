CREATE TABLE IF NOT EXISTS `#__action_logs_users` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `notify` tinyint(1) UNSIGNED NOT NULL,
  `extensions` text NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `idx_notify` (`notify`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
