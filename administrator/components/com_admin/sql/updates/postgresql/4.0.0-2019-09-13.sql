-- From 4.0.0-2019-07-14.sql
-- The following 3 statements were modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
ALTER TABLE "#__contact_details" DROP COLUMN "xreference" /** CAN FAIL **/;
ALTER TABLE "#__content" DROP COLUMN "xreference" /** CAN FAIL **/;
ALTER TABLE "#__newsfeeds" DROP COLUMN "xreference" /** CAN FAIL **/;
-- From 4.0.0-2019-07-16.sql
-- This has been removed as com_csp has been removed from the final build

-- From 4.0.0-2019-08-03.sql
-- The following two statements were modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
ALTER TABLE "#__update_sites" ADD COLUMN "checked_out" bigint DEFAULT 0 NOT NULL /** CAN FAIL **/;
ALTER TABLE "#__update_sites" ADD COLUMN "checked_out_time" timestamp without time zone DEFAULT NULL /** CAN FAIL **/;

-- From 4.0.0-2019-08-20.sql
-- The following two statements were modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
ALTER TABLE "#__content_frontpage" ADD COLUMN "featured_up" timestamp without time zone /** CAN FAIL **/;
ALTER TABLE "#__content_frontpage" ADD COLUMN "featured_down" timestamp without time zone /** CAN FAIL **/;

-- From 4.0.0-2019-08-21.sql
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_webservices_banners', 'plugin', 'banners', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_config', 'plugin', 'config', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_contact', 'plugin', 'contact', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_languages', 'plugin', 'languages', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_menus', 'plugin', 'menus', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_messages', 'plugin', 'messages', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_modules', 'plugin', 'modules', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_newsfeeds', 'plugin', 'newsfeeds', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_plugins', 'plugin', 'plugins', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_privacy', 'plugin', 'privacy', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_redirect', 'plugin', 'redirect', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_tags', 'plugin', 'tags', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_templates', 'plugin', 'templates', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_users', 'plugin', 'users', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0);

-- From 4.0.0-2019-09-13.sql
-- The following statement was modified for 4.1.1 by adding the "ON CONFLICT" clause.
-- See https://github.com/joomla/joomla-cms/pull/37156
INSERT INTO "#__menu" ("menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "checked_out", "checked_out_time", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 'main', 'com_messages_manager', 'Private Messages', '', 'Messaging/Private Messages', 'index.php?option=com_messages&view=messages', 'component', 1, 10, 2, "extension_id", 0, NULL, 0, 0, 'class:messages-add', 0, '', 18, 19, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_messages'
ON CONFLICT DO NOTHING;
