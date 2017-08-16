-- Add locked field to extensions table.
ALTER TABLE `#__extensions` MODIFY `protected` tinyint(3) NOT NULL DEFAULT 0 COMMENT 'Flag to indicate if the extension is protected. Protected extensions cannot be disabled.';
ALTER TABLE `#__extensions` ADD COLUMN `locked` tinyint(3) NOT NULL DEFAULT 0 COMMENT 'Flag to indicate if the extension is locked. Locked extensions cannot be uninstalled.';

-- Set all core extensions as locked extensions and unprotected them.
UPDATE `#__extensions`
SET `locked` = 1, `protected` = 0
WHERE (`type` = 'component' AND `element` IN (
	'com_mailto',
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
	'com_search',
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
	'com_associations'
))
OR (`type` = 'module' AND `element` IN (
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
	'mod_search',
	'mod_stats',
	'mod_syndicate',
	'mod_users_latest',
	'mod_whosonline',
	'mod_wrapper',
	'mod_articles_category',
	'mod_articles_categories',
	'mod_languages',
	'mod_finder',
	'mod_custom',
	'mod_feed',
	'mod_latest',
	'mod_logged',
	'mod_login',
	'mod_menu',
	'mod_popular',
	'mod_quickicon',
	'mod_status',
	'mod_submenu',
	'mod_title',
	'mod_toolbar',
	'mod_multilangstatus',
	'mod_version',
	'mod_stats_admin',
	'mod_tags_popular',
	'mod_tags_similar',
	'mod_sampledata'
))
OR (`type` = 'plugin' AND
	(
		(`folder` = 'system' AND `element` IN ('languagefilter', 'p3p', 'cache', 'debug', 'log', 'redirect', 'remember', 'sef', 'logout', 'languagecode', 'highlight', 'updatenotification', 'stats', 'fields'))
		OR (`folder` = 'content' AND `element` IN ('contact', 'emailcloak', 'loadmodule', 'pagebreak', 'pagenavigation', 'vote', 'joomla', 'finder', 'fields'))
		OR (`folder` = 'user' AND `element` IN ('contactcreator', 'joomla', 'profile'))
		OR (`folder` = 'quickicon' AND `element` IN ('joomlaupdate', 'extensionupdate', 'phpversioncheck'))
		OR (`folder` = 'editors' AND `element` IN ('codemirror', 'none', 'tinymce'))
		OR (`folder` = 'editors-xtd' AND `element` IN ('article', 'image', 'pagebreak', 'readmore', 'module', 'menu', 'contact', 'fields'))
		OR (`folder` = 'authentication' AND `element` IN ('gmail', 'joomla', 'ldap', 'cookie'))
		OR (`folder` = 'twofactorauth' AND `element` IN ('totp', 'yubikey'))
		OR (`folder` = 'installer' AND `element` IN ('packageinstaller', 'folderinstaller', 'urlinstaller'))
		OR (`folder` = 'extension' AND `element` IN ('joomla'))
		OR (`folder` = 'captcha' AND `element` IN ('recaptcha'))
		OR (`folder` = 'search' AND `element` IN ('categories', 'contacts', 'content', 'newsfeeds', 'tags'))
		OR (`folder` = 'finder' AND `element` IN ('categories', 'contacts', 'content', 'newsfeeds', 'tags'))
		OR (`folder` = 'fields' AND `element` IN ('calendar', 'checkboxes', 'color', 'editor', 'imagelist', 'integer', 'list', 'media', 'radio', 'sql', 'text', 'textarea', 'url', 'user', 'usergrouplist'))
		OR (`folder` = 'sampledata' AND `element` IN ('blog'))
	)
)
OR (`type` = 'library' AND `element` IN ('phputf8', 'joomla', 'idna_convert', 'fof', 'phpass'))
OR (`type` = 'template' AND `element` IN ('beez3', 'hathor', 'protostar', 'isis'))
OR (`type` = 'language' AND `element` IN ('en-GB'))
OR (`type` = 'file' AND `element` IN ('joomla'))
OR (`type` = 'package' AND `element` IN ('pkg_en-GB'));

-- Now protect from disabling essential core extensions.
UPDATE `#__extensions`
SET `protected` = 1, `enabled` = 1
WHERE (`type` = 'component' AND `element` IN (
	'com_mailto',
	'com_admin',
	'com_cache',
	'com_categories',
	'com_checkin',
	'com_cpanel',
	'com_installer',
	'com_languages',
	'com_login',
	'com_media',
	'com_menus',
	'com_modules',
	'com_plugins',
	'com_templates',
	'com_content',
	'com_config',
	'com_users',
	'com_joomlaupdate',
	'com_ajax',
	'com_postinstall'
))
OR (`type` = 'library' AND `element` IN ('phputf8', 'joomla', 'idna_convert', 'fof', 'phpass'))
OR (`type` = 'language' AND `element` IN ('en-GB'))
OR (`type` = 'file' AND `element` IN ('joomla'))
OR (`type` = 'package' AND `element` IN ('pkg_en-GB'));
