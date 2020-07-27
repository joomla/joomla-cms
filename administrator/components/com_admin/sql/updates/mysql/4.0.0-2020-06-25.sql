ALTER TABLE `#__template_styles` ADD COLUMN `inheritable` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__template_styles` ADD COLUMN `parent` varchar(50) DEFAULT '';
