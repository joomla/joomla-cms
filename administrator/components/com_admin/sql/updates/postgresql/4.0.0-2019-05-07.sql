INSERT INTO "#__extensions" ("extension_id", "package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'mod_frontend', 'module', 'mod_frontend', '', 1, 1, 1, 0, '', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'mod_messages', 'module', 'mod_messages', '', 1, 1, 1, 0, '', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'mod_post_installation_messages', 'module', 'mod_post_installation_messages', '', 1, 1, 1, 0, '', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'mod_user', 'module', 'mod_user', '', 1, 1, 1, 0, '', '', 0, '1970-01-01 00:00:00', 0, 0),

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Frontend Link', '', '', 5, 'status', 0, NULL, NULL, NULL, 1, 'mod_frontend', 1, 1, '', 1, '*');
RETURNING id INTO lastmoduleid;

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Messages', '', '', 4, 'status', 0, NULL, NULL, NULL, 1, 'mod_messages', 3, 1, '', 1, '*');
RETURNING id INTO lastmoduleid;

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Post Install Messages', '', '', 3, 'status', 0, NULL, NULL, NULL, 1, 'mod_post_installation_messages', 3, 1, '', 1, '*');
RETURNING id INTO lastmoduleid;

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('User Status', '', '', 6, 'status', 0, NULL, NULL, NULL, 1, 'mod_user', 3, 1, '', 1, '*');
RETURNING id INTO lastmoduleid;

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

DELETE FROM "#__extensions" WHERE "element" = 'mod_status';

INSERT INTO `#__modules` (`title`, `note`, `content`, `ordering`, `position`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES
('Site', '', NULL, 1, 'icon', 0, NULL, NULL, NULL, 1, 'mod_quickicon', 1, 1, '{"context":"site_quickicon","header_icon":"fa fa-desktop","show_users":"1","show_articles":"1","show_categories":"1","show_media":"1","show_menuItems":"1","show_modules":"1","show_plugins":"1","show_templates":"1","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"style":"0","module_tag":"div","bootstrap_size":"6","header_tag":"h3","header_class":""}', 1, '*');

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

INSERT INTO `#__modules` (`title`, `note`, `content`, `ordering`, `position`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES
('System', '', NULL, 2, 'icon', 0, NULL, NULL, NULL, 1, 'mod_quickicon', 1, 1, '{"context":"system_quickicon","header_icon":"fa fa-wrench","show_global":"1","show_checkin":"1","show_cache":"1","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"style":"0","module_tag":"div","bootstrap_size":"6","header_tag":"h3","header_class":""}', 1, '*');

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

INSERT INTO `#__modules` (`title`, `note`, `content`, `ordering`, `position`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES
('3rd Party', '', NULL, 4, 'icon', 0, NULL, NULL, NULL, 1, 'mod_quickicon', 1, 1, '{"context":"mod_quickicon","header_icon":"fa fa-boxes","load_plugins":"1","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"style":"0","module_tag":"div","bootstrap_size":"6","header_tag":"h3","header_class":""}', 1, '*');

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

UPDATE `#__modules` SET `title` = 'Update Checks',`ordering` = 3,`position` = 'icon',`showtitle` = 1,`params` = '{"context":"update_quickicon","header_icon":"fa fa-sync","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"style":"0","module_tag":"div","bootstrap_size":"12","header_tag":"h3","header_class":""}' WHERE `#__modules`.`id` = 9;


INSERT INTO `#__modules` (`title`, `note`, `content`, `ordering`, `position`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES
('Help Submenu', '', NULL, 1, 'cpanel-help', 0, NULL, NULL, NULL, 1, 'mod_submenu', 1, 0, '{\"menutype\":\"*\",\"preset\":\"help\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"style\":\"System-none\",\"module_tag\":\"div\",\"bootstrap_size\":\"0\",\"header_tag\":\"h3\",\"header_class\":\"\"}', 1, '*');

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

UPDATE `#__modules` SET `ordering` = 2,`position` = 'status' WHERE `#__modules`.`id` = 79;
