# $Id$

# RC 3 to RC 4

-- 26-Oct-2007 --
-- Database index optimizations

ALTER TABLE `jos_categories`
  DROP INDEX `idx_section`;

ALTER TABLE `jos_components`
  ADD INDEX `parent_option` ( `parent` , `option` ( 32 ) );

ALTER TABLE `jos_contact_details`
  ADD INDEX `catid` ( `catid` );

ALTER TABLE `jos_content`
  ADD INDEX `idx_createdby` ( `created_by` ),
  DROP INDEX `idx_mask`;

-- Watch out: This operation already found it's way into the joomla.sql file in RC 1.
-- However, that file has not been used by the installer for a long time.
-- You should *only* run this query if your jos_core_acl_aro_sections table has duplicate indexes.
ALTER TABLE `jos_core_acl_aro_sections`
  DROP INDEX `value_aro_sections`,
  DROP INDEX `hidden_aro_sections`;

ALTER TABLE `jos_messages`
  ADD INDEX `useridto_state` ( `user_id_to`, `state` );

ALTER TABLE `jos_newsfeeds`
  ADD INDEX `catid` ( `catid` );

ALTER TABLE `jos_session`
  DROP PRIMARY KEY,
  ADD PRIMARY KEY (`session_id`(64)),
  ADD INDEX `userid` ( `userid` ),
  ADD INDEX `time` ( `time` );

ALTER TABLE `jos_templates_menu`
  DROP PRIMARY KEY,
  ADD PRIMARY KEY ( `menuid`, `client_id`, `template` ( 255 ) );

ALTER TABLE `jos_users`
  ADD INDEX `gid_block` (`gid`, `block`),
  ADD INDEX `username` ( `username` ),
  ADD INDEX `email` ( `email` );


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
