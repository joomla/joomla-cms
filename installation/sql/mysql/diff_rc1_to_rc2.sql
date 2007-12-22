# $Id$

# RC 1 to RC 2

-- 30-July-2007 --
-- Fixes delete user issue

CREATE TABLE  `jos_core_acl_aro_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(230) NOT NULL default '0',
  `value` varchar(100) NOT NULL,
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) TYPE=MyISAM CHARACTER SET `utf8`;

-- 29-July-2007 --
-- Fixes large object in session data

ALTER TABLE `jos_session`
  MODIFY COLUMN `data` LONGTEXT;

# Beta 2 to RC 1

-- Fixes incompatibility with natice phpgacl schema
ALTER TABLE `jos_core_acl_aro_sections`
  CHANGE COLUMN `section_id` `id` INTEGER NOT NULL AUTO_INCREMENT;

-- Bogus indexes
ALTER TABLE `jos_core_acl_aro_sections`
  DROP INDEX `value_aro_sections`,
  DROP INDEX `hidden_aro_sections`;