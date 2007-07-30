# $Id$

# RC 1 to RC 2

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
