# $Id$

# Joomla 1.0 to Joomla 1.5

# Mambot Additions
INSERT INTO `jos_mambots` VALUES (0, 'Authentication - Joomla', 'joomla', 'authentication', 0, 1, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `jos_mambots` VALUES (0, 'Authentication - LDAP', 'ldap', 'authentication', 0, 1, 0, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `jos_mambots` VALUES (0, 'Joomla SEF URLs', 'joomla.sefurlbot', 'system', 0, 2, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `jos_mambots` VALUES (0, 'XML-RPC - Joomla', 'joomla', 'xmlrpc', 0, 7, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `jos_mambots` VALUES (0, 'Visitor Statistics', 'joomla.visitorbot', 'system', 0, 3, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `jos_mambots` VALUES (0, 'Page Navigation','pagenavigation','content',0,2,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO `jos_mambots` VALUES (0, 'Editor - XStandard Lite 1.7', 'xstandard', 'editors', 0, 0, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `jos_mambots` VALUES (0, 'Authentication - GMail', 'gmail', 'authentication', 0, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `jos_mambots` VALUES (0, 'Editor Button - Readmore','readmore','editors-xtd',0,0,1,0,0,0,'0000-00-00 00:00:00','');

UPDATE `jos_mambots` SET `name` = 'Content - Image' WHERE `id` = '1'; 
UPDATE `jos_mambots` SET `name` = 'Content - Pagebreak' WHERE `id` = '2'; 
UPDATE `jos_mambots` SET `name` = 'Content - SEF' WHERE `id` = '4';
UPDATE `jos_mambots` SET `name` = 'Content - Ratinr' WHERE `id` = '5';  
UPDATE `jos_mambots` SET `name` = 'Content - Code Hightlighter (Joomla)' WHERE `id` = '8';  
UPDATE `jos_mambots` SET `name` = 'Content - Email Cloaking' WHERE `id` = '16'; 
UPDATE `jos_mambots` SET `name` = 'Content - Code Hightlighter (GeSHi)' WHERE `id` = '17';
UPDATE `jos_mambots` SET `name` = 'Content - Load Module' WHERE `id` = '19';
UPDATE `jos_mambots` SET `name` = 'Content - Page navigation' WHERE `id` = '19';    

UPDATE `jos_mambots` SET `name` = 'Search - Content' WHERE `id` = '6'; 
UPDATE `jos_mambots` SET `name` = 'Search - Weblinks' WHERE `id` = '7';  
UPDATE `jos_mambots` SET `name` = 'Search - Contacts' WHERE `id` = '13'; 
UPDATE `jos_mambots` SET `name` = 'Search - Categories' WHERE `id` = '14'; 
UPDATE `jos_mambots` SET `name` = 'Search - Sections' WHERE `id` = '15';
UPDATE `jos_mambots` SET `name` = 'Search - Newsfeeds' WHERE `id` = '18';

UPDATE `jos_mambots` SET `name` = 'Editor - No Editor' WHERE `id` = '9';
UPDATE `jos_mambots` SET `name` = 'Editor - TinyMCE 2.0' WHERE `id` = '10';

UPDATE `jos_mambots` SET `name` = 'Editor Buttton - Image' WHERE `id` = '11';
UPDATE `jos_mambots` SET `name` = 'Editor Button  - Pagebreak' WHERE `id` = '12'; 

UPDATE `jos_mambots` SET `element` = 'image' WHERE `id` = '1';
UPDATE `jos_mambots` SET `element` = 'pagebreak' WHERE `id` = '2';
UPDATE `jos_mambots` SET `element` = 'sef' WHERE `id` = '4'; 
UPDATE `jos_mambots` SET `element` = 'vote' WHERE `id` = '5'; 
UPDATE `jos_mambots` SET `element` = 'code' WHERE `id` = '8'; 
UPDATE `jos_mambots` SET `element` = 'emailcloak' WHERE `id` = '16'; 
UPDATE `jos_mambots` SET `element` = 'loadmodule' WHERE `id` = '19'; 

UPDATE `jos_mambots` SET `element` = 'content' WHERE `id` = '6';  
UPDATE `jos_mambots` SET `element` = 'weblinks' WHERE `id` = '7'; 
UPDATE `jos_mambots` SET `element` = 'contacts' WHERE `id` = '13';
UPDATE `jos_mambots` SET `element` = 'categories' WHERE `id` = '14';  
UPDATE `jos_mambots` SET `element` = 'sections' WHERE `id` = '15';
UPDATE `jos_mambots` SET `element` = 'newsfeeds' WHERE `id` = '18';

UPDATE `jos_mambots` SET `element` = 'image' WHERE `id` = '11';
UPDATE `jos_mambots` SET `element` = 'pagebreak' WHERE `id` = '12';

#Mambot Removals 
DELETE FROM `jos_mambots` WHERE `element` = 'legacybots'; 

# Module Additions
INSERT INTO `jos_modules` VALUES (1000, 'Footer', '', 1, 'footer', 0, '0000-00-00 00:00:00', 1, 'mod_footer', 0, 0, 1, '', 1, 0);
INSERT INTO `jos_modules` VALUES (1001, 'Footer', '', 0, 'footer', 0, '0000-00-00 00:00:00', 1, 'mod_footer', 0, 0, 1, '', 1, 1);
INSERT INTO `jos_modules` VALUES (0, 'Newsfeed', '', 11, 'left', 0, '0000-00-00 00:00:00', 0, 'mod_rss', 0, 0, 1, '', 1, 0);
INSERT INTO `jos_modules` VALUES (1002, 'Breadcrumbs', '', 1, 'breadcrumb', 0, '0000-00-00 00:00:00', 1, 'mod_breadcrumbs', 0, 0, 1, '', 1, 0);
INSERT INTO `jos_modules` VALUES (1003, 'Admin Menu','', 1,'menu', 0,'0000-00-00 00:00:00', 1,'mod_menu', 0, 23, 1, '', 0, 1);
INSERT INTO `jos_modules` VALUES (1004, 'Admin SubMenu','', 1,'submenu', 0,'0000-00-00 00:00:00', 1,'mod_submenu', 0, 23, 1, '', 0, 1);
INSERT INTO `jos_modules` VALUES (1005, 'User Status','', 1,'status', 0,'0000-00-00 00:00:00', 1,'mod_status', 0, 23, 1, '', 0, 1);
INSERT INTO `jos_modules` VALUES (1006, 'Title','', 1,'title', 0,'0000-00-00 00:00:00', 1,'mod_title', 0, 23, 1, '', 0, 1);
INSERT INTO `jos_modules` VALUES (9, 'Login Form', '', 3, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_login', 0, 0, 1, '', 1, 1);
INSERT INTO `jos_modules` VALUES (1007, 'CSS Admin Menu','',1,'top',0,'0000-00-00 00:00:00',1,'mod_cssmenu',0,23,1,'',1, 1);

INSERT INTO `jos_modules_menu` VALUES (1000,0);
INSERT INTO `jos_modules_menu` VALUES (1001,0);
INSERT INTO `jos_modules_menu` VALUES (1002,0);

UPDATE `jos_modules` SET `access` = '23' WHERE `client_id` = 1;
UPDATE `jos_modules` SET `title` = 'Feed Display' AND `module` = 'mod_feed' WHERE `module` = 'mod_rss';

#Menu additions
INSERT INTO `jos_template_positions` VALUES (0, 'menu', '');

#Mambot Removals 
DELETE FROM `jos_modules` WHERE `module` = 'mod_mosmsg' 
DELETE FROM `jos_modules` WHERE `module` = 'mod_rssfeed' 

# Expand content title lengths
ALTER TABLE `jos_content` CHANGE `title` `title` varchar(255) NOT NULL default '';
ALTER TABLE `jos_content` CHANGE `title_alias` `title_alias` varchar(255) NOT NULL default '';

UPDATE `jos_components` SET `admin_menu_link` = 'option=com_categories&section=com_contact_details' WHERE `id` = '9'  LIMIT 1;
UPDATE `jos_components` SET `admin_menu_link` = 'option=com_categories&section=com_weblinks' WHERE `id` = '6'  LIMIT 1;

# AJE: 17-Nov-2005
# Fix column names in phpgacl tables
ALTER TABLE `jos_core_acl_aro` CHANGE COLUMN `aro_id` `id` INTEGER NOT NULL AUTO_INCREMENT;
ALTER TABLE `jos_core_acl_aro_groups` CHANGE COLUMN `group_id` `id` INTEGER NOT NULL AUTO_INCREMENT;
ALTER TABLE `jos_core_acl_aro_sections` CHANGE COLUMN `section_id` `id` INTEGER NOT NULL AUTO_INCREMENT;

ALTER TABLE `jos_core_acl_aro_groups` ADD COLUMN `value` varchar(255) NOT NULL default '';
UPDATE `jos_core_acl_aro_groups` SET value=name;
ALTER TABLE `jos_core_acl_aro_groups` ADD UNIQUE `value_aro_groups`(`value`);
ALTER TABLE `jos_core_acl_aro_groups` DROP PRIMARY KEY, ADD PRIMARY KEY(`id`, `value`);

# LBL: 05-Jan-2006
# Mambot refactor to Plugin
RENAME TABLE `jos_mambots` TO `jos_plugins`;

# LBL: 18-Jan-2006
# Add enabling of components
ALTER TABLE `jos_components` ADD `enabled` TINYINT NOT NULL ;

# LBL: 11-Feb-2006
# Add breadcrumb template position
INSERT INTO `jos_template_positions` VALUES (0, 'breadcrumb', '');
INSERT INTO `jos_template_positions` VALUES (0, 'submenu', '');
INSERT INTO `jos_template_positions` VALUES (0, 'status', '');
INSERT INTO `jos_template_positions` VALUES (0, 'title', '');

#JJ: 21-Feb-2006
# Add client id to session table
ALTER TABLE `jos_session` ADD COLUMN `client_id` tinyint(3) NOT NULL default 0;

#JJ: 08-Mar-2006 
# Delete massmail component from component menu table
DELETE FROM `jos_components` WHERE `option` = 'com_massmail' 

#AE: 15-Mar-2006
ALTER TABLE `jos_contact_details` MODIFY COLUMN `name` VARCHAR(255) NOT NULL;
ALTER TABLE `jos_contact_details` MODIFY COLUMN `con_position` VARCHAR(255) ,
ALTER TABLE `jos_contact_details` MODIFY COLUMN `suburb` VARCHAR(100) ,
ALTER TABLE `jos_contact_details` MODIFY COLUMN `state` VARCHAR(100) ,
ALTER TABLE `jos_contact_details` MODIFY COLUMN `country` VARCHAR(100) ,
ALTER TABLE `jos_contact_details` MODIFY COLUMN `postcode` VARCHAR(100) ,
ALTER TABLE `jos_contact_details` MODIFY COLUMN `telephone` VARCHAR(255) ,
ALTER TABLE `jos_contact_details` MODIFY COLUMN `fax` VARCHAR(255) ,
ALTER TABLE `jos_contact_details` MODIFY COLUMN `image` VARCHAR(255) ,
ALTER TABLE `jos_contact_details` MODIFY COLUMN `email_to` VARCHAR(255) ,
ALTER TABLE `jos_contact_details` ADD COLUMN `mobile` VARCHAR(255) NOT NULL;
ALTER TABLE `jos_contact_details` ADD COLUMN `webpage` VARCHAR(255) NOT NULL;

#JJ: 20-Mar-2006
DROP TABLE `jos_usertypes`

#AE: 27-Mar-2006
ALTER TABLE `jos_menu` ADD COLUMN `mvcrt` VARCHAR(45) NOT NULL DEFAULT '';
ALTER TABLE `jos_menu` ADD COLUMN `lft` INTEGER UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `jos_menu` ADD COLUMN `rgt` INTEGER UNSIGNED NOT NULL DEFAULT 0;

#JJ: 17-Apr-2006
DELETE FROM `jos_modules` WHERE `title` = 'Components'

#FF: 21-Apr-2006 
DELETE FROM `jos_components` WHERE `title` = 'Syndicate'

#AE: 22-May-2006

CREATE TABLE `jos_menu_types` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `menutype` VARCHAR(75) NOT NULL DEFAULT '',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY(`id`),
  UNIQUE `menutype`(`menutype`)
) TYPE=MyISAM CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

INSERT INTO `jos_menu_types` VALUES (1, 'mainmenu', 'Main Menu', 'The main menu for the site');

INSERT INTO jos_menu_types (menutype)
SELECT
  SUBSTR(
    params,
    LOCATE('=', params, LOCATE('menutype',params))+1,
    75
    ) AS menutype
FROM jos_modules
WHERE module='mod_mainmenu'
	AND params NOT LIKE '%menutype=mainmenu%';

UPDATE jos_menu_types
	SET menutype = SUBSTR(menutype, 1, LOCATE('\n', menutype)-1);

UPDATE jos_menu_types
	SET title = menutype;
	
#AE: 24-May-2006

ALTER TABLE `jos_modules`
  MODIFY COLUMN `position` VARCHAR(50) DEFAULT '';

INSERT INTO jos_components  VALUES (0,'Media Manager','',0,0,'option=com_media','Media Manager','com_media',0,'',1,'upload_extensions=jpg,png,gif,png\r\nupload_maxsize=1000000\r\n\r\n',1);
INSERT INTO jos_components  VALUES (0,'Articles','option=com_content',0,0,'option=com_content','Articles','com_content',0,'',1,'',1);
INSERT INTO jos_components  VALUES (0,'Configuration Manager','',0,0,'','Configuration','com_config',0,'',1,'',1);
INSERT INTO jos_components  VALUES (0,'Installation Manager','',0,0,'','Installer','com_installer',0,'',1,'',1);
INSERT INTO jos_components  VALUES (0,'Lanuage Manager','',0,0,'','Lanaguages','com_languages',0,'',1,'',1);
INSERT INTO jos_components  VALUES (0,'Mass mail','',0,0,'','Mass Mail','com_massmail',0,'',1,'',1);
INSERT INTO jos_components  VALUES (0,'Menu Editor','',0,0,'','Menu Editor','com_menus',0,'',1,'',1);
INSERT INTO jos_components  VALUES (0,'Menu Manager','',0,0,'','Menu Manager','com_menumanager',0,'',1,'',1);
INSERT INTO jos_components  VALUES (0,'Messaging','',0,0,'','Messages','com_messages',0,'',1,'',1);
INSERT INTO jos_components  VALUES (0,'Modules Manager','',0,0,'','Modules','com_modules',0,'',1,'',1);
INSERT INTO jos_components  VALUES (0,'Plugin Manager','',0,0,'','Plugins','com_plugins',0,'',1,'',1);
INSERT INTO jos_components  VALUES (0,'Statistics','',0,0,'','Statistics','com_statistics',0,'',1,'',1);
INSERT INTO jos_components  VALUES (0,'Template Manager','',0,0,'','Templates','com_templates',0,'',1,'',1);
INSERT INTO jos_components  VALUES (0,'User Manager','',0,0,'','Users','com_users',0,'',1,'',1);

ALTER TABLE `jos_modules` ADD COLUMN `mvcrt` TEXT NOT NULL DEFAULT '';


