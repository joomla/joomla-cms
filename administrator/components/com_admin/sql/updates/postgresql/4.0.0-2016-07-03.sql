--
-- Alter the #__extensions table
--
ALTER TABLE "#__extensions" DROP COLUMN "custom_data";
ALTER TABLE "#__extensions" DROP COLUMN "system_data";
ALTER TABLE "#__extensions" ADD COLUMN "changelogurl" text;
ALTER TABLE "#__extensions" ADD COLUMN "note" character varying(255);
ALTER TABLE "#__extensions" ADD COLUMN "locked" smallint DEFAULT 0 NOT NULL;
COMMENT ON COLUMN "#__extensions"."protected" IS 'Flag to indicate if the extension is protected. Protected extensions cannot be disabled.';
COMMENT ON COLUMN "#__extensions"."locked" IS 'Flag to indicate if the extension is locked. Locked extensions cannot be uninstalled.';
ALTER TABLE "#__extensions" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__extensions" ALTER COLUMN "checked_out_time" DROP DEFAULT;

--
-- Delete entries
--
DELETE FROM "#__extensions" WHERE "type" = 'library' AND "element" IN ('phputf8', 'idna_convert');
DELETE FROM "#__extensions" WHERE "type" = 'plugin' AND "element" = 'p3p' AND "folder" = 'system';
DELETE FROM "#__extensions" WHERE "type" = 'template' AND "element" IN ('hathor', 'isis') AND "client_id" = 1;
DELETE FROM "#__extensions" WHERE "type" = 'template' AND "element" IN ('protostar', 'beez3') AND "client_id" = 0;
DELETE FROM "#__extensions" WHERE "element" IN ('mod_submenu', 'mod_status') AND "client_id" = 1;
DELETE FROM "#__extensions" WHERE "name" = 'com_mailto';

DELETE FROM "#__template_styles" WHERE "template" IN ('hathor', 'isis') AND "client_id" = 1;
DELETE FROM "#__template_styles" WHERE "template" IN ('protostar', 'beez3') AND "client_id" = 0;

--
-- Insert entries
--
INSERT INTO "#__extensions" ("name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "ordering", "state") VALUES
('plg_behaviour_taggable', 'plugin', 'taggable', 'behaviour', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_behaviour_versionable', 'plugin', 'versionable', 'behaviour', 0, 1, 1, 0, '', '{}', 0, 0),
('atum', 'template', 'atum', '', 1, 1, 1, 0, '{}', '{}', 0, 0),
('cassiopeia', 'template', 'cassiopeia', '', 0, 1, 1, 0, '{}', '{}', 0, 0),
('plg_filesystem_local', 'plugin', 'local', 'filesystem', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_media-action_crop', 'plugin', 'crop', 'media-action', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_media-action_resize', 'plugin', 'resize', 'media-action', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_media-action_rotate', 'plugin', 'rotate', 'media-action', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_system_httpheaders', 'plugin', 'httpheaders', 'system', 0, 0, 1, 0, '', '{}', 0, 0),
('com_workflow', 'component', 'com_workflow', '', 1, 1, 0, 1, '', '{}', 0, 0),
('plg_workflow_publishing', 'plugin', 'publishing', 'workflow', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_workflow_featuring', 'plugin', 'featuring', 'workflow', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_workflow_notification', 'plugin', 'notification', 'workflow', 0, 1, 1, 0, '', '{}', 0, 0),
('com_csp', 'component', 'com_csp', '', 1, 1, 1, 0, '', '{}', 0, 0),
('plg_extension_namespacemap', 'plugin', 'namespacemap', 'extension', 0, 0, 1, 1, '', '{}', 0, 0),
('plg_installer_override', 'plugin', 'override', 'installer', 0, 1, 1, 1, '', '', 4, 0),
('plg_quickicon_overridecheck', 'plugin', 'overridecheck', 'quickicon', 0, 1, 1, 1, '', '', 0, 0),
('plg_extension_finder', 'plugin', 'finder', 'extension', 0, 1, 1, 0, '', '', 0, 0),
('plg_api-authentication_basic', 'plugin', 'basic', 'api-authentication', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_content', 'plugin', 'content', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('com_mails', 'component', 'com_mails', '', 1, 1, 1, 1, '', '{}', 0, 0),
('plg_system_skipto', 'plugin', 'skipto', 'system', 0, 1, 1, 0, '', '{}',  0, 0),
('plg_sampledata_multilang', 'plugin', 'multilang', 'sampledata', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_installer_webinstaller', 'plugin', 'webinstaller', 'installer', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_fields_subfields', 'plugin', 'subfields', 'fields', 0, 1, 1, 0, '', '', 0, 0),
('plg_system_webauthn', 'plugin', 'webauthn', 'system', 0, 1, 1, 0, '', '{}', 8, 0),
('mod_loginsupport', 'module', 'mod_loginsupport', '', 1, 1, 1, 1, '', '', 0, 0),
('mod_frontend', 'module', 'mod_frontend', '', 1, 1, 1, 0, '', '', 0, 0),
('mod_messages', 'module', 'mod_messages', '', 1, 1, 1, 0, '', '', 0, 0),
('mod_post_installation_messages', 'module', 'mod_post_installation_messages', '', 1, 1, 1, 0, '', '', 0, 0),
('mod_user', 'module', 'mod_user', '', 1, 1, 1, 0, '', '', 0, 0),
('mod_submenu', 'module', 'mod_submenu', '', 1, 1, 1, 0, '', '{}', 0, 0),
('mod_privacy_status', 'module', 'mod_privacy_status', '', 1, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_banners', 'plugin', 'banners', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_config', 'plugin', 'config', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_contact', 'plugin', 'contact', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_languages', 'plugin', 'languages', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_menus', 'plugin', 'menus', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_messages', 'plugin', 'messages', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_modules', 'plugin', 'modules', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_newsfeeds', 'plugin', 'newsfeeds', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_plugins', 'plugin', 'plugins', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_privacy', 'plugin', 'privacy', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_redirect', 'plugin', 'redirect', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_tags', 'plugin', 'tags', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_templates', 'plugin', 'templates', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_webservices_users', 'plugin', 'users', 'webservices', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_user_token', 'plugin', 'token', 'user', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_api-authentication_token', 'plugin', 'token', 'api-authentication', 0, 1, 1, 0, '', '{}', 0, 0),
('plg_system_accessibility', 'plugin', 'accessibility', 'system', 0, 0, 1, 0, '', '{}', 0, 0),
('plg_quickicon_downloadkey', 'plugin', 'downloadkey', 'quickicon', 0, 1, 1, 0, '', '', 0, 0),
('plg_content_imagelazyload', 'plugin', 'imagelazyload', 'content', 0, 1, 1, 0, '', '', 0, 0);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "ordering", "state")
SELECT "extension_id", 'English (en-GB)', 'language', 'en-GB', '', 3, 1, 1, 1, '', '', 0, 0 FROM "#__extensions" WHERE "name" = 'English (en-GB) Language Pack';

INSERT INTO "#__template_styles" ("template", "client_id", "home", "title", "params") VALUES
('atum', 1, (CASE WHEN (SELECT count FROM (SELECT count("id") AS count FROM "#__template_styles" WHERE home = '1' AND client_id = 1 AND "template" IN ('isis', 'hathor')) as c) = 0 THEN '0' ELSE '1' END), 'atum - Default', '{}'),
('cassiopeia', 0, (CASE WHEN (SELECT count FROM (SELECT count("id") AS count FROM "#__template_styles" WHERE home = '1' AND client_id = 0 AND "template" IN ('protostar', 'beez3')) as c) = 0 THEN '0' ELSE '1' END), 'cassiopeia - Default', '{}');

UPDATE "#__extensions" SET "client_id" = 1 WHERE "name" = 'com_wrapper';
UPDATE "#__extensions" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';
UPDATE "#__extensions" SET "enabled" = 0 WHERE "name" = 'plg_api-authentication_basic' AND "type" = 'plugin';

-- Set all core extensions as locked extensions and unprotected them.
UPDATE "#__extensions"
SET "locked" = 1, "protected" = 0
WHERE ("type" = 'component' AND "element" IN (
                                              'com_wrapper',
                                              'com_admin',
                                              'com_banners',
                                              'com_cache',
                                              'com_categories',
                                              'com_checkin',
                                              'com_contact',
                                              'com_cpanel',
                                              'com_installer',
                                              'com_languages',
                                              'com_login',
                                              'com_media',
                                              'com_menus',
                                              'com_messages',
                                              'com_modules',
                                              'com_newsfeeds',
                                              'com_plugins',
                                              'com_templates',
                                              'com_content',
                                              'com_config',
                                              'com_redirect',
                                              'com_users',
                                              'com_finder',
                                              'com_joomlaupdate',
                                              'com_tags',
                                              'com_contenthistory',
                                              'com_ajax',
                                              'com_postinstall',
                                              'com_fields',
                                              'com_associations',
                                              'com_privacy',
                                              'com_actionlogs',
                                              'com_workflow',
                                              'com_csp',
                                              'com_mails'
    ))
   OR ("type" = 'module' AND "client_id" = 0 AND "element" IN (
                                                               'mod_articles_archive',
                                                               'mod_articles_latest',
                                                               'mod_articles_popular',
                                                               'mod_banners',
                                                               'mod_breadcrumbs',
                                                               'mod_custom',
                                                               'mod_feed',
                                                               'mod_footer',
                                                               'mod_login',
                                                               'mod_menu',
                                                               'mod_articles_news',
                                                               'mod_random_image',
                                                               'mod_related_items',
                                                               'mod_stats',
                                                               'mod_syndicate',
                                                               'mod_users_latest',
                                                               'mod_whosonline',
                                                               'mod_wrapper',
                                                               'mod_articles_category',
                                                               'mod_articles_categories',
                                                               'mod_languages',
                                                               'mod_finder',
                                                               'mod_tags_popular',
                                                               'mod_tags_similar'
    ))
   OR ("type" = 'module' AND "client_id" = 1 AND "element" IN (
                                                               'mod_custom',
                                                               'mod_feed',
                                                               'mod_latest',
                                                               'mod_logged',
                                                               'mod_login',
                                                               'mod_loginsupport',
                                                               'mod_menu',
                                                               'mod_popular',
                                                               'mod_quickicon',
                                                               'mod_frontend',
                                                               'mod_messages',
                                                               'mod_post_installation_messages',
                                                               'mod_user',
                                                               'mod_title',
                                                               'mod_toolbar',
                                                               'mod_multilangstatus',
                                                               'mod_version',
                                                               'mod_stats_admin',
                                                               'mod_sampledata',
                                                               'mod_latestactions',
                                                               'mod_privacy_dashboard',
                                                               'mod_submenu',
                                                               'mod_privacy_status'
    ))
   OR ("type" = 'plugin' AND
       (
               ("folder" = 'actionlog' AND "element" IN ('joomla'))
               OR ("folder" = 'api-authentication' AND "element" IN ('basic', 'token'))
               OR ("folder" = 'authentication' AND "element" IN ('cookie', 'joomla', 'ldap'))
               OR ("folder" = 'behaviour' AND "element" IN ('taggable', 'versionable'))
               OR ("folder" = 'captcha' AND "element" IN ('recaptcha', 'recaptcha_invisible'))
               OR ("folder" = 'content' AND "element" IN ('confirmconsent', 'contact', 'emailcloak', 'fields', 'finder', 'joomla', 'loadmodule', 'pagebreak', 'pagenavigation', 'vote'))
               OR ("folder" = 'editors' AND "element" IN ('codemirror', 'none', 'tinymce'))
               OR ("folder" = 'editors-xtd' AND "element" IN ('article', 'contact', 'fields', 'image', 'menu', 'module', 'pagebreak', 'readmore'))
               OR ("folder" = 'extension' AND "element" IN ('finder', 'joomla', 'namespacemap'))
               OR ("folder" = 'fields' AND "element" IN ('calendar', 'checkboxes', 'color', 'editor', 'imagelist', 'integer', 'list', 'media', 'radio', 'sql', 'subfields', 'text', 'textarea', 'url', 'user', 'usergrouplist'))
               OR ("folder" = 'filesystem' AND "element" IN ('local'))
               OR ("folder" = 'finder' AND "element" IN ('categories', 'contacts', 'content', 'newsfeeds', 'tags'))
               OR ("folder" = 'installer' AND "element" IN ('folderinstaller', 'override', 'packageinstaller', 'urlinstaller', 'webinstaller'))
               OR ("folder" = 'media-action' AND "element" IN ('crop', 'resize', 'rotate'))
               OR ("folder" = 'privacy' AND "element" IN ('actionlogs', 'consents', 'contact', 'content', 'message', 'user'))
               OR ("folder" = 'quickicon' AND "element" IN ('downloadkey', 'extensionupdate', 'joomlaupdate', 'overridecheck', 'phpversioncheck', 'privacycheck'))
               OR ("folder" = 'sampledata' AND "element" IN ('blog', 'multilang', 'testing'))
               OR ("folder" = 'system' AND "element" IN ('accessibility', 'actionlogs', 'cache', 'debug', 'fields', 'highlight', 'httpheaders', 'languagecode', 'languagefilter', 'log', 'logout', 'logrotation', 'privacyconsent', 'redirect', 'remember', 'sef', 'sessiongc', 'skipto', 'stats', 'updatenotification', 'webauthn'))
               OR ("folder" = 'twofactorauth' AND "element" IN ('totp', 'yubikey'))
               OR ("folder" = 'user' AND "element" IN ('contactcreator', 'joomla', 'profile', 'terms', 'token'))
               OR ("folder" = 'webservices' AND "element" IN ('banners', 'config', 'contact', 'content', 'languages', 'menus', 'messages', 'modules', 'newsfeeds', 'plugins', 'privacy', 'redirect', 'tags', 'templates', 'users'))
           )
    )
   OR ("type" = 'library' AND "element" IN ('joomla', 'phpass'))
   OR ("type" = 'template' AND "element" IN ('cassiopeia', 'atum'))
   OR ("type" = 'language' AND "element" IN ('en-GB'))
   OR ("type" = 'file' AND "element" IN ('joomla'))
   OR ("type" = 'package' AND "element" IN ('pkg_en-GB'));

-- Now protect from disabling essential core extensions.
UPDATE "#__extensions"
SET "protected" = 1, "enabled" = 1
WHERE ("type" = 'component' AND "element" IN (
                                              'com_admin',
                                              'com_ajax',
                                              'com_cache',
                                              'com_categories',
                                              'com_checkin',
                                              'com_config',
                                              'com_content',
                                              'com_cpanel',
                                              'com_installer',
                                              'com_joomlaupdate',
                                              'com_languages',
                                              'com_login',
                                              'com_mails',
                                              'com_media',
                                              'com_menus',
                                              'com_messages',
                                              'com_modules',
                                              'com_plugins',
                                              'com_postinstall',
                                              'com_templates',
                                              'com_users',
                                              'com_workflow'
    ))
   OR ("type" = 'plugin' AND
       (
               ("folder" = 'authentication' AND "element" IN ('joomla'))
               OR ("folder" = 'editors' AND "element" IN ('none'))
               OR ("folder" = 'extension' AND "element" IN ('namespacemap'))
           )
    )
   OR ("type" = 'library' AND "element" IN ('joomla', 'phpass'))
   OR ("type" = 'language' AND "element" IN ('en-GB'))
   OR ("type" = 'file' AND "element" IN ('joomla'))
   OR ("type" = 'package' AND "element" IN ('pkg_en-GB'));

-- Set core extensions (from J3) as unlocked extensions and unprotect them.
UPDATE "#__extensions"
SET "protected" = 0, "locked" = 0
WHERE ("type" = 'library' AND "element" IN (
    'fof'
    ));



