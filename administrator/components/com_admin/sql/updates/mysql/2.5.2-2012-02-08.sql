ALTER TABLE `#__languages` ADD COLUMN `access` integer unsigned NOT NULL default 0 AFTER `published`;

ALTER TABLE `#__languages` ADD KEY `idx_access` (`access`);