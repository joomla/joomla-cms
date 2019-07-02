ALTER TABLE `#__associations` ADD COLUMN `master_id` int(11) NOT NULL DEFAULT -1 COMMENT 'The master item of an association.';
ALTER TABLE `#__associations` ADD COLUMN `master_date` datetime COMMENT 'The save or modified date of the master item.';
