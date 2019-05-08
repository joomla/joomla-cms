INSERT INTO "#__extensions" ("extension_id", "package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'mod_status_frontend', 'module', 'mod_status_frontend', '', 1, 1, 1, 0, '', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'mod_status_messages', 'module', 'mod_status_messages', '', 1, 1, 1, 0, '', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'mod_status_post_installation_messages', 'module', 'mod_status_post_installation_messages', '', 1, 1, 1, 0, '', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'mod_status_user', 'module', 'mod_status_user', '', 1, 1, 1, 0, '', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'mod_status_language', 'module', 'mod_status_language', '', 1, 1, 1, 0, '', '', 0, '1970-01-01 00:00:00', 0, 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
(99, 0, 'Frontend Link', '', '', 2, 'status', 0, NULL, NULL, NULL, 1, 'mod_status_frontend', 3, 1, '', 1, '*');
RETURNING id INTO lastmoduleid;

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
(100, 0, 'Messages', '', '', 3, 'status', 0, NULL, NULL, NULL, 1, 'mod_status_messages', 3, 1, '', 1, '*');
RETURNING id INTO lastmoduleid;

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
(101, 0, 'Post Install Messages', '', '', 4, 'status', 0, NULL, NULL, NULL, 1, 'mod_status_post_installation_messages', 3, 1, '', 1, '*');
RETURNING id INTO lastmoduleid;

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
(102, 0, 'User Status', '', '', 5, 'status', 0, NULL, NULL, NULL, 1, 'mod_status_user', 3, 1, '', 1, '*');
RETURNING id INTO lastmoduleid;

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
(103, 0, 'Language Status', '', '', 6, 'status', 0, NULL, NULL, NULL, 1, 'mod_status_language', 3, 1, '', 1, '*');
RETURNING id INTO lastmoduleid;

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

DELETE FROM "#__extensions" WHERE "element" = 'mod_status';
