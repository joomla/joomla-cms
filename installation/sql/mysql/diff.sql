# $Id$

# Beta 2 to RC 1

-- Fixes incompatibility with natice phpgacl schema
ALTER TABLE `jos_core_acl_aro_sections`
  CHANGE COLUMN `section_id` `id` INTEGER NOT NULL AUTO_INCREMENT;

-- Bogus indexes
ALTER TABLE `jos_core_acl_aro_sections`
  DROP INDEX `value_aro_sections`,
  DROP INDEX `hidden_aro_sections`;
