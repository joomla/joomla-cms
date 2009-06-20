# $Id$

# 1.5 to 1.6

-- ----------------------------------------------------------------
-- jos_categories
-- ----------------------------------------------------------------

ALTER TABLE `jos_categories`
 MODIFY COLUMN `description` VARCHAR(5120) NOT NULL DEFAULT '';

ALTER TABLE `jos_categories`
 MODIFY COLUMN `params` VARCHAR(2048) NOT NULL DEFAULT '';
 
ALTER TABLE `jos_categories`
 ADD COLUMN `lft` INTEGER NOT NULL DEFAULT 0 COMMENT 'Nested set lft.' AFTER `parent_id`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `rgt` INTEGER NOT NULL DEFAULT 0 COMMENT 'Nested set rgt.' AFTER `lft`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `level` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `rgt`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `path` VARCHAR(1024) NOT NULL DEFAULT '' AFTER `level`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `metadesc` VARCHAR(1024) NOT NULL COMMENT 'The meta description for the page.' AFTER `params`;

ALTER TABLE `jos_categories`
 ADD COLUMN `metakey` VARCHAR(1024) NOT NULL COMMENT 'The meta keywords for the page.' AFTER `metadesc`;

ALTER TABLE `jos_categories`
 ADD COLUMN `metadata` VARCHAR(2048) NOT NULL COMMENT 'JSON encoded metadata properties.' AFTER `metakey`;

ALTER TABLE `jos_categories`
 ADD COLUMN `created_user_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `metadata`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `created_time` TIMESTAMP NOT NULL AFTER `created_user_id`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `modified_user_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `created_time`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `modified_time` TIMESTAMP NOT NULL AFTER `modified_user_id`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `hits` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_time`;

ALTER TABLE `jos_categories` 
 ADD COLUMN `language` VARCHAR(7) NOT NULL AFTER `hits`;

ALTER TABLE `jos_categories`
 ADD INDEX idx_path(`path`),
 ADD INDEX idx_left_right(`lft`, `rgt`);

-- TODO: Merge from sections and add uncategorised nodes.

-- ----------------------------------------------------------------
-- jos_components
-- ----------------------------------------------------------------

ALTER TABLE `jos_components`
 MODIFY COLUMN `enabled` TINYINT(4) UNSIGNED NOT NULL DEFAULT 1;

-- ----------------------------------------------------------------
-- jos_content
-- ----------------------------------------------------------------

ALTER TABLE `jos_content`
 ADD COLUMN `featured` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Set if article is featured.' AFTER `metadata`;

ALTER TABLE `jos_content`
 ADD INDEX idx_featured_catid(`featured`, `catid`);

ALTER TABLE `jos_content`
 ADD COLUMN `language` VARCHAR(10) NOT NULL COMMENT 'The language code for the article.' AFTER `featured`;

ALTER TABLE `jos_content`
 ADD COLUMN `xreference` VARCHAR(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.' AFTER `language`;

ALTER TABLE `jos_content`
 ADD INDEX idx_language(`language`);
 
ALTER TABLE `jos_content`
 ADD INDEX idx_xreference(`xreference`);

UPDATE `jos_content` AS a
 SET a.featured = 1
 WHERE a.id IN (
 	SELECT f.content_id
 	FROM `jos_content_frontpage` AS f
 );

-- ----------------------------------------------------------------
-- jos_menu
-- ----------------------------------------------------------------

ALTER TABLE `jos_menu`
 DROP COLUMN `sublevel`,
 DROP COLUMN `pollid`,
 DROP COLUMN `utaccess`;

ALTER TABLE `jos_menu`
 MODIFY COLUMN `menutype` VARCHAR(24) NOT NULL COMMENT 'The type of menu this item belongs to. FK to jos_menu_types.menutype';

ALTER TABLE `jos_menu`
 CHANGE COLUMN `name` `title` VARCHAR(255) NOT NULL COMMENT 'The display title of the menu item.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `alias` VARCHAR(255) NOT NULL COMMENT 'The SEF alias of the menu item.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `link` VARCHAR(1024) NOT NULL COMMENT 'The actually link the menu item refers to.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `type` VARCHAR(16) NOT NULL COMMENT 'The type of link: Component, URL, Alias, Separator';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `published` TINYINT NOT NULL DEFAULT 0 COMMENT 'The published state of the menu link.';

ALTER TABLE `jos_menu`
 CHANGE COLUMN `parent` `parent_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The parent menu item in the menu tree.';

ALTER TABLE `jos_menu`
 ADD COLUMN `level` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The relative level in the tree.' AFTER `parent_id`;

ALTER TABLE `jos_menu`
 CHANGE COLUMN `componentid` `component_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to jos_components.id';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `ordering` INTEGER NOT NULL DEFAULT 0 COMMENT 'The relative ordering of the menu item in the tree.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `checked_out` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to jos_users.id';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `checked_out_time` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'The time the menu item was checked out.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `browserNav` TINYINT NOT NULL DEFAULT 0 COMMENT 'The click behaviour of the link.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `access` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The access level required to view the menu item.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `params` VARCHAR(10240) NOT NULL COMMENT 'JSON encoded data for the menu item.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `lft` INTEGER NOT NULL DEFAULT 0 COMMENT 'Nested set lft.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `rgt` INTEGER NOT NULL DEFAULT 0 COMMENT 'Nested set rgt.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `home` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Indicates if this menu item is the home or default page.';

ALTER TABLE `jos_menu`
 ADD COLUMN `path` VARCHAR(1024) NOT NULL COMMENT 'The computed path of the menu item based on the alias field.' AFTER `alias`;

INSERT INTO `jos_menu` VALUES 
 (0, '', 'Menu_Item_Root', 'root', '', '', '', 1, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, 0, '', 0, 37, 0);

-- TODO: Need to devise how to shift the parent_id's of the existing menus to relate to the new root.
-- UPDATE `jos_menu`
--  SET `parent_id` = (SELECT `id` FROM `jos_menu` WHERE `alias` = 'root')
--  WHERE `alias` != 'root';

-- ----------------------------------------------------------------
-- jos_menu_types
-- ----------------------------------------------------------------

ALTER TABLE `jos_menu_types`
 MODIFY COLUMN `menutype` VARCHAR(24) NOT NULL,
 MODIFY COLUMN `title` VARCHAR(48) NOT NULL,
 DROP INDEX `menutype`; 

-- ----------------------------------------------------------------
-- jos_modules
-- ----------------------------------------------------------------

UPDATE `#__modules`
 SET `menutype` = 'mod_menu'
 WHERE `menutype` = 'mod_mainmenu';
 
-- ----------------------------------------------------------------
-- jos_plugins
-- ----------------------------------------------------------------

INSERT INTO `#__plugins` VALUES (NULL, 'Editor - CodeMirror', 'codemirror', 'editors', 1, 0, 1, 1, 0, 0, '0000-00-00 00:00:00', 'linenumbers=0\n\n');

-- ----------------------------------------------------------------
-- jos_session
-- ----------------------------------------------------------------

ALTER TABLE `jos_session`
 MODIFY COLUMN `session_id` VARCHAR(32);

ALTER TABLE `jos_session`
 MODIFY COLUMN `guest` TINYINT UNSIGNED DEFAULT 1;

ALTER TABLE `jos_session`
 MODIFY COLUMN `client_id` TINYINT UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `jos_session`
 MODIFY COLUMN `data` VARCHAR(20480);

-- ----------------------------------------------------------------
-- jos_weblinks
-- ----------------------------------------------------------------

ALTER TABLE `jos_weblinks`
 ADD COLUMN `access` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `approved`;

-- ----------------------------------------------------------------
-- Reconfigure the admin module permissions
-- ----------------------------------------------------------------

UPDATE `#__categories`
 SET access = access + 1;

UPDATE `#__contact_details`
 SET access = access + 1;

UPDATE `#__content`
 SET access = access + 1;

UPDATE `#__menu`
 SET access = access + 1;

UPDATE `#__modules`
 SET access = access + 1;

UPDATE `#__plugins`
 SET access = access + 1;

UPDATE `#__sections`
 SET access = access + 1;

-- ----------------------------------------------------------------
-- Table drops.
-- ----------------------------------------------------------------

DROP TABLE `#__groups`;

-- Note, devise the migration
DROP TABLE `#__core_acl_aro`;
DROP TABLE `#__core_acl_aro_map`;
DROP TABLE `#__core_acl_aro_groups`;
DROP TABLE `#__core_acl_groups_aro_map`;
DROP TABLE `#__core_acl_aro_sections`;

