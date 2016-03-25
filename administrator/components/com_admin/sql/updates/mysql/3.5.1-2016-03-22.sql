ALTER TABLE `#__utf8_conversion` ADD COLUMN `extension_id` int(11) NOT NULL DEFAULT 0, ADD PRIMARY KEY(`extension_id`);
UPDATE `#__utf8_conversion` SET `extension_id`=700 WHERE `extension_id`=0;
