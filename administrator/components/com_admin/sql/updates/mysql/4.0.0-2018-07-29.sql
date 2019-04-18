DROP TABLE #__finder_taxonomy;

CREATE TABLE IF NOT EXISTS `#__finder_taxonomy` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`parent_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`lft` INT(11) NOT NULL DEFAULT '0',
	`rgt` INT(11) NOT NULL DEFAULT '0',
	`level` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`path` VARCHAR(400) NOT NULL DEFAULT '',
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`alias` VARCHAR(400) NOT NULL DEFAULT '',
	`state` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`access` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`language` CHAR(7) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	INDEX `idx_state` (`state`),
	INDEX `idx_access` (`access`),
	INDEX `idx_path` (`path`(100)),
	INDEX `idx_left_right` (`lft`, `rgt`),
	INDEX `idx_alias` (`alias`(100)),
	INDEX `idx_language` (`language`),
	INDEX `idx_parent_published` (`parent_id`, `state`, `access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_general_ci;

INSERT INTO `#__finder_taxonomy` (`id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `title`, `alias`, `state`, `access`, `language`) VALUES
(1, 0, 0, 1, 0, '', 'ROOT', 'root', 1, 1, '*');
