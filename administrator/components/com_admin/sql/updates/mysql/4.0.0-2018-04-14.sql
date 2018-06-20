CREATE TABLE `#__finder_links_terms` (
	`link_id` INT(10) UNSIGNED NOT NULL,
	`term_id` INT(10) UNSIGNED NOT NULL,
	`weight` FLOAT UNSIGNED NOT NULL,
	PRIMARY KEY (`link_id`, `term_id`),
	INDEX `idx_term_weight` (`term_id`, `weight`),
	INDEX `idx_link_term_weight` (`link_id`, `term_id`, `weight`)
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;
DROP TABLE #__finder_links_terms0;
DROP TABLE #__finder_links_terms1;
DROP TABLE #__finder_links_terms2;
DROP TABLE #__finder_links_terms3;
DROP TABLE #__finder_links_terms4;
DROP TABLE #__finder_links_terms5;
DROP TABLE #__finder_links_terms6;
DROP TABLE #__finder_links_terms7;
DROP TABLE #__finder_links_terms8;
DROP TABLE #__finder_links_terms9;
DROP TABLE #__finder_links_termsa;
DROP TABLE #__finder_links_termsb;
DROP TABLE #__finder_links_termsc;
DROP TABLE #__finder_links_termsd;
DROP TABLE #__finder_links_termse;
DROP TABLE #__finder_links_termsf;

ALTER TABLE `#__finder_terms`
	CHANGE COLUMN `language` `language` CHAR(7) NOT NULL DEFAULT '' AFTER `links`;

ALTER TABLE `#__finder_terms_common`
	CHANGE COLUMN `language` `language` CHAR(7) NOT NULL DEFAULT '' AFTER `term`;

ALTER TABLE `#__finder_tokens`
	CHANGE COLUMN `language` `language` CHAR(7) NOT NULL DEFAULT '' AFTER `context`;

ALTER TABLE `#__finder_tokens_aggregate`
	DROP COLUMN `map_suffix`;

ALTER TABLE `#__finder_tokens_aggregate`
	CHANGE COLUMN `language` `language` CHAR(7) NOT NULL DEFAULT '' AFTER `total_weight`;

ALTER TABLE `#__finder_links`
	CHANGE COLUMN `language` `language` CHAR(7) NOT NULL DEFAULT '' AFTER `access`;