ALTER TABLE `#__associations` ADD COLUMN `parent_id` int(11) NOT NULL DEFAULT -1 COMMENT 'The parent of an association.';
ALTER TABLE `#__associations` ADD COLUMN `parent_date` datetime COMMENT 'The save or modified date of the parent.';
