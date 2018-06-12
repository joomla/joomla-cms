-- Normalize finder tables default values.
-- finder_filters table
ALTER TABLE `#__finder_filters` MODIFY `title` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_filters` MODIFY `alias` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_filters` MODIFY `created_by` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__finder_filters` MODIFY `created_by_alias` varchar(255) NOT NULL DEFAULT '';
-- finder_links table
ALTER TABLE `#__finder_links` MODIFY `url` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_links` MODIFY `route` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_links` MODIFY `description` text NOT NULL DEFAULT '';
ALTER TABLE `#__finder_links` MODIFY `state` int(5) NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links` MODIFY `access` int(5) NOT NULL DEFAULT 0;
ALTER TABLE `#__finder_links` MODIFY `language` char(7) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_links` MODIFY `type_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__finder_links` ADD INDEX `idx_language` (`language`);
-- finder_links_terms tables
ALTER TABLE `#__finder_links_terms` MODIFY `weight` float unsigned NOT NULL DEFAULT 0;
-- finder_taxonomy table
ALTER TABLE `#__finder_taxonomy` MODIFY `title` varchar(255) NOT NULL DEFAULT '';
-- finder_terms table
ALTER TABLE `#__finder_terms` MODIFY `term` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_terms` MODIFY `stem` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_terms` MODIFY `soundex` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_terms` MODIFY `language` char(7) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_terms` ADD INDEX `idx_stem` (`stem`);
ALTER TABLE `#__finder_terms` ADD INDEX `idx_language` (`language`);
-- finder_terms table_common
ALTER TABLE `#__finder_terms_common` MODIFY `term` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_terms_common` MODIFY `language` char(7) NOT NULL DEFAULT '';
-- finder_tokens table
ALTER TABLE `#__finder_tokens` MODIFY `term` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens` MODIFY `stem` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens` MODIFY `weight` float unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__finder_tokens` MODIFY `language` char(7) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens` ADD INDEX `idx_stem` (`stem`);
ALTER TABLE `#__finder_tokens` ADD INDEX `idx_language` (`language`);
-- finder_tokens_aggregate table
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `term_id` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `map_suffix` char(1) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `term` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `stem` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `term_weight` float unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `context_weight` float unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `total_weight` float unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `language` char(7) NOT NULL DEFAULT '';
-- finder_types table
ALTER TABLE `#__finder_types` MODIFY `title` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_types` MODIFY `mime` varchar(100) NOT NULL DEFAULT '';

