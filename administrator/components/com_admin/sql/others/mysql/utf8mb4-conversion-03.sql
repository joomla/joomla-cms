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
-- Step 3.1: Search for duplication strings in unicode collation and update weight for first one
--

UPDATE `#__finder_terms` AS old_terms INNER JOIN (
	SELECT MIN(term_id) AS term_id, ROUND(SUM(`weight`), 8) AS `weight`, COUNT(*) AS `count`
	FROM `#__finder_terms` GROUP BY term COLLATE utf8mb4_unicode_ci HAVING `count` > 1) AS new_terms
	ON old_terms.term_id = new_terms.term_id SET old_terms.`weight` = new_terms.`weight`;

--
-- Step 3.2: Search for duplication strings in unicode collation and remove all except first one
--

DELETE old_terms FROM `#__finder_terms` AS old_terms INNER JOIN (
	SELECT MIN(term_id) AS term_id, term COLLATE utf8mb4_unicode_ci AS term2, COUNT(*) AS `count`
	FROM `#__finder_terms` GROUP BY term2 HAVING `count` > 1) AS new_terms
	ON old_terms.term COLLATE utf8mb4_unicode_ci = new_terms.term AND old_terms.term_id != new_terms.term_id;

--
-- Step 3.3: Convert #__finder_xxx tables to utf8mb4 chracter set with utf8mb4_unicode_ci collation
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
ALTER TABLE `#__finder_terms` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_terms_common` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_tokens` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_tokens_aggregate` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_types` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

--
-- Step 3.4: Set default character set and collation for #__finder_xxx tables.
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
ALTER TABLE `#__finder_terms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_terms_common` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_tokens` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_tokens_aggregate` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__finder_types` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
