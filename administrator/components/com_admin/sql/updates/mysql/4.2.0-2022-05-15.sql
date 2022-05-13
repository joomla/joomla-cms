CREATE TABLE IF NOT EXISTS `#__user_tfa` (
  `id`         int unsigned NOT NULL AUTO_INCREMENT,
  `user_id`    int unsigned NOT NULL,
  `title`      VARCHAR(255)    NOT NULL,
  `method`     VARCHAR(100)    NOT NULL,
  `default`    TINYINT(1)      NOT NULL DEFAULT 0,
  `options`    LONGTEXT        NULL,
  `created_on` DATETIME        NULL,
  `last_used`  DATETIME        NULL,
  INDEX `#__loginguard_tfa_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci COMMENT='Two Factor Authentication settings';
