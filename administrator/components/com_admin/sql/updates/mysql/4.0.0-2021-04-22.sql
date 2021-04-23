ALTER TABLE `#__mail_templates` ADD COLUMN `extension` varchar(127) NOT NULL DEFAULT '' AFTER `template_id`;
UPDATE `#__mail_templates` SET `extension` = SUBSTRING(`template_id`, 1, POSITION('.' IN `template_id`) - 1);
