INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Content Submenu', '', NULL, 1, 'cpanel-content', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"content","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 1, '*')
RETURNING id INTO lastmoduleid;

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Menus Submenu', '', NULL, 1, 'cpanel-menus', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"menus","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 1, '*')
RETURNING id INTO lastmoduleid;

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Components Submenu', '', NULL, 1, 'cpanel-components', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"components","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 1, '*')
RETURNING id INTO lastmoduleid;

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Users Submenu', '', NULL, 1, 'cpanel-content', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"users","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 1, '*')
RETURNING id INTO lastmoduleid;

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (lastmoduleid, 0);
