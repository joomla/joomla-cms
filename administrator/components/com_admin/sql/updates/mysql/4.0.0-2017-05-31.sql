ALTER TABLE `#__menu` ADD COLUMN `publish_up` datetime;
ALTER TABLE `#__menu` ADD COLUMN `publish_down` datetime;
ALTER TABLE `#__menu` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

DELETE FROM `#__menu` WHERE `link` = 'index.php?option=com_postinstall' AND `menutype` = 'main';
DELETE FROM `#__menu` WHERE `link` = 'index.php?option=com_redirect' AND `menutype` = 'main';
DELETE FROM `#__menu` WHERE `link` = 'index.php?option=com_joomlaupdate' AND `menutype` = 'main';
DELETE FROM `#__menu` WHERE `link` = 'index.php?option=com_csp' AND `menutype` = 'main';
DELETE FROM `#__menu` WHERE `link` = 'index.php?option=com_messages' AND `menutype` = 'main';
DELETE FROM `#__menu` WHERE `link` = 'index.php?option=com_messages&view=messages' AND `menutype` = 'main';
DELETE FROM `#__menu` WHERE `link` = 'index.php?option=com_messages&task=message.add' AND `menutype` = 'main';

UPDATE `#__menu` SET `link` = 'index.php?option=com_config&view=config' WHERE `link` = 'index.php?option=com_config&view=config&controller=config.display.config';
UPDATE `#__menu` SET `link` = 'index.php?option=com_config&view=templates' WHERE `link` = 'index.php?option=com_config&view=templates&controller=config.display.templates';
UPDATE `#__menu` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
UPDATE `#__menu` SET `link`='index.php?option=com_banners&view=banners' WHERE `id`=3;
UPDATE `#__menu` SET `link`='index.php?option=com_categories&view=categories&extension=com_banners' WHERE `id`=4;
UPDATE `#__menu` SET `link`='index.php?option=com_contact&view=contacts' WHERE `id`=8;
UPDATE `#__menu` SET `link`='index.php?option=com_categories&view=categories&extension=com_contact' WHERE `id`=9;
UPDATE `#__menu` SET `link`='index.php?option=com_newsfeeds&view=newsfeeds' WHERE `id`=14;
UPDATE `#__menu` SET `link`='index.php?option=com_categories&view=categories&extension=com_newsfeeds' WHERE `id`=15;
UPDATE `#__menu` SET `link`='index.php?option=com_redirect&view=links' WHERE `id`=16;
UPDATE `#__menu` SET `link`='index.php?option=com_search&view=searches' WHERE `id`=17;
UPDATE `#__menu` SET `link`='index.php?option=com_finder&view=index' WHERE `id`=18;
UPDATE `#__menu` SET `link`='index.php?option=com_tags&view=tags' WHERE `id`=20;
UPDATE `#__menu` SET `link`='index.php?option=com_associations&view=associations' WHERE `id`=22;

INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`, `publish_up`, `publish_down`)
SELECT 'main', 'com_messages_manager', 'Private Messages', '', 'Messaging/Private Messages', 'index.php?option=com_messages&view=messages', 'component', 1, 10, 2, `extension_id`, 0, NULL, 0, 0, 'class:messages-add', 0, '', 18, 19, 0, '*', 1, NULL, NULL FROM `#__extensions` WHERE `name` = 'com_messages';

UPDATE `#__menu` SET `img` = 'class:bookmark' WHERE `path` = 'Banners';
UPDATE `#__menu` SET `img` = 'class:address-book' WHERE `path` = 'Contacts';
UPDATE `#__menu` SET `img` = 'class:rss' WHERE `path` = 'News Feeds';
UPDATE `#__menu` SET `img` = 'class:language' WHERE `path` = 'Multilingual Associations';
UPDATE `#__menu` SET `img` = 'class:search-plus' WHERE `path` = 'Smart Search';
