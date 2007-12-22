# $Id$

# RC 3 to RC 4

-- 14-Dec-2007
-- Change SEF plugin from Content To System

UPDATE `jos_plugins` SET `ordering` = `ordering` + 1 WHERE `folder` LIKE 'system';

UPDATE `jos_plugins` SET `name` = 'System - SEF', `folder` = 'system', `ordering` = 1 WHERE element LIKE 'sef';

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
