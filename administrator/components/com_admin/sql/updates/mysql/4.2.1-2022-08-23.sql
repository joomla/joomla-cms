-- Revert https://github.com/joomla/joomla-cms/pull/38244
-- See also file 4.2.0-2022-07-07.sql
DELETE FROM `#__extensions` WHERE `name` = 'plg_fields_menuitem' AND `type` = 'plugin' AND `element` = 'menuitem' AND `folder` = 'fields' AND `client_id` = 0;
