-- The following two statements were modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
ALTER TABLE `#__redirect_links` DROP INDEX `idx_link_modifed` /** CAN FAIL **/;
ALTER TABLE `#__redirect_links` ADD INDEX `idx_link_modified` (`modified_date`) /** CAN FAIL **/;
