-- From 4.0.0-2019-07-02.sql
CREATE TABLE IF NOT EXISTS "#__webauthn_credentials" (
    "id"         varchar(1000)    NOT NULL,
    "user_id"    varchar(128)     NOT NULL,
    "label"      varchar(190)     NOT NULL,
    "credential" TEXT             NOT NULL,
    PRIMARY KEY ("id")
);

CREATE INDEX "#__webauthn_credentials_user_id" ON "#__webauthn_credentials" ("user_id");

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_system_webauthn', 'plugin', 'webauthn', 'system', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 8, 0);

-- From 4.0.0-2019-07-13.sql
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'mod_loginsupport', 'module', 'mod_loginsupport', '', 1, 1, 1, 1, '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'mod_frontend', 'module', 'mod_frontend', '', 1, 1, 1, 0, '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'mod_messages', 'module', 'mod_messages', '', 1, 1, 1, 0, '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'mod_post_installation_messages', 'module', 'mod_post_installation_messages', '', 1, 1, 1, 0, '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'mod_user', 'module', 'mod_user', '', 1, 1, 1, 0, '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'mod_submenu', 'module', 'mod_submenu', '', 1, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'mod_privacy_status', 'module', 'mod_privacy_status', '', 1, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0);

DELETE FROM "#__extensions" WHERE "element" = 'mod_status' AND "client_id" = 1;

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Login Support', '', '', 1, 'sidebar', 0, NULL, NULL, NULL, 1, 'mod_loginsupport', 1, 1, '{"forum_url":"https://forum.joomla.org/","documentation_url":"https://docs.joomla.org/","news_url":"https://www.joomla.org/announcements.html","automatic_title":1,"prepare_content":1,"layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('System Dashboard', '', '', 1, 'cpanel-system', 0, NULL, NULL, NULL, 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"system","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"12","header_tag":"h3","header_class":"","style":"System-none"}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Content Dashboard', '', '', 1, 'cpanel-content', 0, NULL, NULL, NULL, 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"content","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"3","header_tag":"h3","header_class":"","style":"System-none"}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Menus Dashboard', '', '', 1, 'cpanel-menus', 0, NULL, NULL, NULL, 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"menus","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"6","header_tag":"h3","header_class":"","style":"System-none"}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Components Dashboard', '', '', 1, 'cpanel-components', 0, NULL, NULL, NULL, 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"components","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"12","header_tag":"h3","header_class":"","style":"System-none"}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Users Dashboard', '', '', 1, 'cpanel-users', 0, NULL, NULL, NULL, 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"users","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"6","header_tag":"h3","header_class":"","style":"System-none"}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Popular Articles', '', '', 3, 'cpanel-content', 0, NULL, NULL, NULL, 1, 'mod_popular', 3, 1, '{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0", "bootstrap_size": "6"}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Recently Added Articles', '', '', 4, 'cpanel-content', 0, NULL, NULL, NULL, 1, 'mod_latest', 3, 1, '{"count":"5","ordering":"c_dsc","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0", "bootstrap_size": "6"}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Logged-in Users', '', '', 2, 'cpanel-users', 0, NULL, NULL, NULL, 1, 'mod_logged', 3, 1, '{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0", "bootstrap_size": "6"}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Frontend Link', '', '', 5, 'status', 0, NULL, NULL, NULL, 1, 'mod_frontend', 1, 1, '', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Messages', '', '', 4, 'status', 0, NULL, NULL, NULL, 1, 'mod_messages', 3, 1, '', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Post Install Messages', '', '', 3, 'status', 0, NULL, NULL, NULL, 1, 'mod_post_installation_messages', 3, 1, '', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('User Status', '', '', 6, 'status', 0, NULL, NULL, NULL, 1, 'mod_user', 3, 1, '', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Site', '', '', 1, 'icon', 0, NULL, NULL, NULL, 1, 'mod_quickicon', 1, 1, '{"context":"site_quickicon","header_icon":"fas fa-desktop","show_users":"1","show_articles":"1","show_categories":"1","show_media":"1","show_menuItems":"1","show_modules":"1","show_plugins":"1","show_templates":"1","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"style":"0","module_tag":"div","bootstrap_size":"6","header_tag":"h3","header_class":""}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('System', '', '', 2, 'icon', 0, NULL, NULL, NULL, 1, 'mod_quickicon', 1, 1, '{"context":"system_quickicon","header_icon":"fas fa-wrench","show_global":"1","show_checkin":"1","show_cache":"1","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"style":"0","module_tag":"div","bootstrap_size":"6","header_tag":"h3","header_class":""}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('3rd Party', '', '', 4, 'icon', 0, NULL, NULL, NULL, 1, 'mod_quickicon', 1, 1, '{"context":"mod_quickicon","header_icon":"fas fa-boxes","load_plugins":"1","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"style":"0","module_tag":"div","bootstrap_size":"6","header_tag":"h3","header_class":""}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

UPDATE "#__modules" SET "title" = 'Update Checks',"ordering" = 3,"position" = 'icon',"showtitle" = 1,"params" = '{"context":"update_quickicon","header_icon":"fas fa-sync","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"style":"0","module_tag":"div","bootstrap_size":"12","header_tag":"h3","header_class":""}' WHERE "#__modules"."id" = 9;

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Help Dashboard', '', '', 1, 'cpanel-help', 0, NULL, NULL, NULL, 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"help","layout":"_:default","moduleclass_sfx":"","style":"System-none","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":""}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

UPDATE "#__modules" SET "ordering" = 2,"position" = 'status' WHERE "#__modules"."id" = 79;

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Privacy Requests', '', '', 1, 'cpanel-privacy', 0, NULL, NULL, NULL, 1, 'mod_privacy_dashboard', 1, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"cachemode":"static","style":"0","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":""}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Privacy Status', '', '', 1, 'cpanel-privacy', 0, NULL, NULL, NULL, 1, 'mod_privacy_status', 1, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"cachemode":"static","style":"0","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":""}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);
