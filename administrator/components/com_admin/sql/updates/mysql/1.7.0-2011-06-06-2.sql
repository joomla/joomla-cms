ALTER TABLE `#__languages` ADD COLUMN `ordering` int(11) NOT NULL default 0 AFTER `published`;
ALTER TABLE `#__languages` ADD INDEX `idx_ordering` (`ordering`);

