ALTER TABLE `#__finder_terms`
	DROP INDEX `idx_term`,
	ADD UNIQUE INDEX `idx_term` (`term`, `language`);
ALTER TABLE `#__finder_terms`
	ADD INDEX `language` (`language`);