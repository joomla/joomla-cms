-- Normalize finder tables default values.
-- finder_filters table
ALTER TABLE `#__finder_filters` MODIFY `title` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_filters` MODIFY `alias` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_filters` MODIFY `created_by` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__finder_filters` MODIFY `created_by_alias` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_filters` MODIFY `data` text NOT NULL DEFAULT '';
ALTER TABLE `#__finder_filters` MODIFY `params` mediumtext NOT NULL DEFAULT '';
-- finder_links table
ALTER TABLE `#__finder_links` MODIFY `url` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_links` MODIFY `route` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_links` MODIFY `state` int(5) NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links` MODIFY `access` int(5) NOT NULL DEFAULT 0;
ALTER TABLE `#__finder_links` MODIFY `language` varchar(8) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_links` MODIFY `type_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__finder_links` MODIFY `object` mediumblob NOT NULL DEFAULT '';
-- finder_links_termsx tables
ALTER TABLE `#__finder_links_terms0` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_terms1` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_terms2` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_terms3` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_terms4` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_terms5` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_terms6` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_terms7` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_terms8` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_terms9` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_termsa` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_termsb` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_termsc` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_termsd` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_termse` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_links_termsf` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
-- finder_terms table
ALTER TABLE `#__finder_terms` MODIFY `term` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_terms` MODIFY `stem` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_terms` MODIFY `soundex` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_terms` MODIFY `weight` float unsigned NOT NULL DEFAULT 1;
-- finder_terms table_common
ALTER TABLE `#__finder_terms` MODIFY `term` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_terms` MODIFY `language` varchar(3) NOT NULL DEFAULT '';
-- finder_tokens table
ALTER TABLE `#__finder_tokens` MODIFY `term` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens` MODIFY `stem` varchar(75) NOT NULL DEFAULT '';
-- finder_tokens_aggregate table
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `term_id` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `map_suffix` char(1) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `term` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `stem` varchar(75) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `term_weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `context_weight` float unsigned NOT NULL DEFAULT 1;
ALTER TABLE `#__finder_tokens_aggregate` MODIFY `total_weight` float unsigned NOT NULL DEFAULT 1;
-- finder_types table
ALTER TABLE `#__finder_types` MODIFY `title` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_types` MODIFY `mime` varchar(100) NOT NULL DEFAULT '';
