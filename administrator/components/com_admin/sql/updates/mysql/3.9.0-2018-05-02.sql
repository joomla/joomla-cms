INSERT INTO `#__extensions` (`extension_id`, `package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(35, 0, 'com_privacy', 'component', 'com_privacy', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);

CREATE TABLE IF NOT EXISTS `#__privacy_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL DEFAULT '',
  `requested_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` tinyint NOT NULL DEFAULT 0,
  `request_type` varchar(25) NOT NULL DEFAULT '',
  `confirm_token` varchar(100) NOT NULL DEFAULT '',
  `confirm_token_created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` int NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_checkout` (`checked_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
