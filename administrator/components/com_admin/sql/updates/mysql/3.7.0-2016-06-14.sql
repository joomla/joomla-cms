CREATE TABLE IF NOT EXISTS `#__content_draft` (
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `articleId`  INT(10) UNSIGNED NOT NULL,
  `created`    DATETIME         NOT NULL,
  `sharetoken` VARCHAR(32)     NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci COMMENT='Contains shareable draft tokens for content items';