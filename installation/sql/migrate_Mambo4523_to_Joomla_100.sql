# $Id: convert_Mambo4523_to_Joomla_100.sql 47 2005-09-15 02:55:27Z rhuk $

# Converts Mambo 4.5.2.3 to Joomla! 1.0

DELETE FROM `mos_modules` WHERE `title` = 'Mamboforge' AND `position` = 'cpanel';

UPDATE `mos_templates_menu` SET `template` = 'joomla_admin' WHERE `template` = 'mambo_admin_blue' AND `client_id` = '1' LIMIT 1;
UPDATE `mos_templates_menu` SET `template` = 'joomla_admin' WHERE `template` = 'mambo_admin' AND `client_id` = '1' LIMIT 1;

UPDATE `mos_mambots` SET `published` = '1' WHERE `element` = 'tinymce' AND `folder` = 'editors' AND `published` = '0' LIMIT 1;
UPDATE `mos_mambots` SET `published` = '1' WHERE `element` = 'none' AND `folder` = 'editors' AND `published` = '0' LIMIT 1;
