--
-- Step 3 of the UTF-8 Multibyte (utf8mb4) conversion for MySQL
--
-- Convert #__finder_... tables to utf8mb4 or utf8, then set
-- default character sets and collations for them.
--
-- Do not rename this file or any other of the utf8mb4-conversion-*.sql
-- files unless you want to change PHP code, too.
--
-- This file here will be processed with reporting exceptions, in opposite
-- to the file for step 1.
--

--
-- Step 3.1: Convert #__finder_xxx tables to utf8mb4 chracter set with utf8mb4_unicode_ci collation
-- except #__finder_terms table, this will be converted later.
--

ALTER TABLE `#__finder_filters` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms0` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms1` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms2` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms3` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms4` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms5` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms6` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms7` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms8` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms9` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_termsa` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_termsb` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_termsc` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_termsd` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_termse` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_termsf` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_taxonomy` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_taxonomy_map` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_terms_common` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_tokens` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_tokens_aggregate` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_types` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

--
-- Step 3.2: Set default character set and collation for #__finder_xxx tables
-- except #__finder_terms table.
--

ALTER TABLE `#__finder_filters` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms0` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms1` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms2` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms3` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms4` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms5` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms6` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms7` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms8` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_terms9` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_termsa` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_termsb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_termsc` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_termsd` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_termse` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_links_termsf` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_taxonomy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_taxonomy_map` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_terms_common` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_tokens` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_tokens_aggregate` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_types` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

--
-- Step 3.3: Clean up temporary tables.
--

DROP TABLE IF EXISTS `#__finder_terms_new`;
DROP TABLE IF EXISTS `#__finder_terms_old`;

--
-- Step 3.4: Create a copy of structure and indexes for #__finder_terms table.
--

CREATE TABLE `#__finder_terms_new` LIKE `#__finder_terms`;

--
-- Step 3.5: Convert #__finder_terms_new table to utf8mb4 chracter set with utf8mb4_unicode_ci collation.
--

ALTER TABLE `#__finder_terms_new` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_terms_new` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

--
-- Step 3.6: Copy data to #__finder_terms_new table.
--

INSERT INTO `#__finder_terms_new` (
  `term_id`, `term`, `stem`, `common`, `phrase`, `weight`, `soundex`, `links`, `language`
) SELECT `term_id`, `term`, `stem`, `common`, `phrase`, `weight`, `soundex`, `links`, `language`
  FROM `#__finder_terms`
  ON DUPLICATE KEY UPDATE `weight` = `#__finder_terms_new`.`weight` + VALUES(`weight`);

--
-- Step 3.7: Swap tables.
--

RENAME TABLE `#__finder_terms` TO `#__finder_terms_old`, `#__finder_terms_new` TO `#__finder_terms`;

--
-- Step 3.8: Remove old table.
--

DROP TABLE `#__finder_terms_old`;
