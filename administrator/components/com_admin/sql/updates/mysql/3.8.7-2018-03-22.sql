ALTER TABLE `#__fields` ADD COLUMN `access_form` int(11) NOT NULL DEFAULT '1' AFTER `access`;
ALTER TABLE `#__fields_groups` ADD COLUMN `access_form` int(11) NOT NULL DEFAULT '1' AFTER `access`;
