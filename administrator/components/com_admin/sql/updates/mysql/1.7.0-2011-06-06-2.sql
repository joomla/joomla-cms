# $Id$

#
# Database updates for ordering languages
#

ALTER TABLE `#__languages` ADD COLUMN `ordering` int(11) default 0 AFTER `published`;
ALTER TABLE `#__languages` ADD INDEX `idx_ordering` (`ordering`);

