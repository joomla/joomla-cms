# $Id$

#
# Database updates for 1.6.4 to 1.7.0
#
ALTER TABLE `#__modules` ADD COLUMN `assignment` TINYINT NOT NULL DEFAULT 0  AFTER `language` ;
