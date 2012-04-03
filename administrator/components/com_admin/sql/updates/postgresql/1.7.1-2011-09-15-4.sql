--
-- id column not present, it could not be inserted as NULL
--
INSERT INTO  "#__modules"  ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Multilanguage status', '', '', 1, 'status', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 0, 'mod_multilangstatus', 3, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*');

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES ( currval('#__modules_id_seq'::regclass), 0);
