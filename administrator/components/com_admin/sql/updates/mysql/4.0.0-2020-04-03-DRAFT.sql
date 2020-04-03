-- Set components AS LOCKED (can be disabled/can NOT be uninstalled) and unprotect them and ENABLE them.

UPDATE `#__extensions`
SET `locked` = 1, `protected` = 0, `enabled` = 1
WHERE (`type` = 'component' AND `element` IN (
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
));


-- Set FRONT-end modules AS LOCKED  (can be disabled/can NOT be uninstalled) and unprotect them and ENABLE them.

UPDATE `#__extensions`
SET `locked` = 1, `protected` = 0, `enabled` = 1
WHERE (`type` = 'module' AND `client_id` = 0 AND `element` IN (
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
	'mod_finder'
));

-- Set BACK-end modules AS LOCKED  (can be disabled/can NOT be uninstalled) and unprotect them and ENABLE them.
UPDATE `#__extensions`
SET `locked` = 1, `protected` = 0, `enabled` = 1
WHERE (`type` = 'module' AND `client_id` = 1 AND `element` IN (
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
	'mod_tags_popular',
	'mod_tags_similar',
	'mod_sampledata',
	'mod_latestactions',
	'mod_privacy_dashboard',
	'mod_submenu',
	'mod_privacy_status'
));

-- Set plugins AS LOCKED  (can be disabled/can NOT be uninstalled) and unprotect them and ENABLE them.

UPDATE `#__extensions`
SET `locked` = 1, `protected` = 0, `enabled` = 1
WHERE (`type` = 'plugin' AND
	(
		(`folder` = 'actionlog' AND `element` IN (
			'joomla'
		))
		OR (`folder` = 'api-authentication' AND `element` IN (
			'basic',
			'token'
		))
		OR (`folder` = 'authentication' AND `element` IN (
			'cookie',
			'joomla',
			'ldap'
		))
		OR (`folder` = 'behaviour' AND `element` IN (
			'taggable',
			'versionable'
		))
		OR (`folder` = 'captcha' AND `element` IN (
			'recaptcha',
			'recaptcha_invisible'
		))
		OR (`folder` = 'content' AND `element` IN (
			'confirmconsent',
			'contact',
			'emailcloak',
			'fields',
			'finder',
			'joomla',
			'loadmodule',
			'pagebreak',
			'pagenavigation',
			'vote'
		))
		OR (`folder` = 'editors' AND `element` IN (
			'codemirror',
			'none',
			'tinymce'
		))
		OR (`folder` = 'editors-xtd' AND `element` IN (
			'article',
			'contact',
			'fields',
			'image',
			'menu',
			'module',
			'pagebreak',
			'readmore'
		))
		OR (`folder` = 'extension' AND `element` IN (
			'finder',
			'joomla',
			'namespacemap'
		))
		OR (`folder` = 'fields' AND `element` IN (
			'calendar',
			'checkboxes',
			'color',
			'editor',
			'imagelist',
			'integer',
			'list',
			'media',
			'radio',
			'sql',
			'subfields',
			'text',
			'textarea',
			'url',
			'user',
			'usergrouplist'
		))
		OR (`folder` = 'filesystem' AND `element` IN (
			'local'
		))
		OR (`folder` = 'finder' AND `element` IN (
			'categories',
			'contacts',
			'content',
			'newsfeeds',
			'tags'
		))
		OR (`folder` = 'installer' AND `element` IN (
			'folderinstaller',
			'override',
			'packageinstaller',
			'urlinstaller',
			'webinstaller'
		))
		OR (`folder` = 'media-action' AND `element` IN (
			'crop',
			'resize',
			'rotate'
		))
		OR (`folder` = 'privacy' AND `element` IN (
			'actionlogs',
			'consents',
			'contact',
			'content',
			'message',
			'user'
		))
		OR (`folder` = 'quickicon' AND `element` IN (
			'downloadkey',
			'extensionupdate',
			'joomlaupdate',
			'overridecheck',
			'phpversioncheck',
			'privacycheck'
		))
		OR (`folder` = 'sampledata' AND `element` IN (
			'blog',
			'multilang',
			'testing'
		))
		OR (`folder` = 'system' AND `element` IN (
			'accessibility',
			'actionlogs',
			'cache',
			'debug',
			'fields',
			'highlight',
			'httpheaders',
			'languagecode',
			'languagefilter',
			'log',
			'logout',
			'logrotation',
			'privacyconsent',
			'redirect',
			'remember',
			'sef',
			'sessiongc',
			'skipto',
			'stats',
			'updatenotification',
			'webauthn'
		))
		OR (`folder` = 'twofactorauth' AND `element` IN (
			'totp',
			'yubikey'
		))
		OR (`folder` = 'user' AND `element` IN (
			'contactcreator',
			'joomla',
			'profile',
			'terms',
			'token'
		))
		OR (`folder` = 'webservices' AND `element` IN (
			'banners',
			'config',
			'contact',
			'content',
			'languages',
			'menus',
			'messages',
			'modules',
			'newsfeeds',
			'plugins',
			'privacy',
			'redirect',
			'tags',
			'templates',
			'users'
		))
	)
);

-- Set libraries AS LOCKED  (can be disabled/can NOT be uninstalled) and unprotect them and ENABLE them.

UPDATE `#__extensions`
SET `locked` = 1, `protected` = 0, `enabled` = 1
WHERE (`type` = 'library' AND `element` IN (
	'joomla',
	'phpass'
));

-- Set templates AS LOCKED  (can be disabled/can NOT be uninstalled) and unprotect them and ENABLE them.

UPDATE `#__extensions`
SET `locked` = 1, `protected` = 0, `enabled` = 1
WHERE (`type` = 'template' AND `element` IN (
	'cassiopeia',
	'atum'
));

-- Set languages AS LOCKED  (can be disabled/can NOT be uninstalled)) and unprotect them and ENABLE them.

UPDATE `#__extensions`
SET `locked` = 1, `protected` = 0, `enabled` = 1
WHERE (`type` = 'language' AND `element` IN (
	'en-GB'
));

-- Set file extensions AS LOCKED  (can be disabled/can NOT be uninstalled) and unprotect them and ENABLE them.

UPDATE `#__extensions`
SET `locked` = 1, `protected` = 0, `enabled` = 1
WHERE (`type` = 'file' AND `element` IN (
	'joomla'
));

-- Set packages AS LOCKED  (can be disabled/can NOT be uninstalled) and unprotect them and ENABLE them.

UPDATE `#__extensions`
SET `locked` = 1, `protected` = 0, `enabled` = 1
WHERE (`type` = 'package' AND `element` IN (
	'pkg_en-GB'
));

-- 
-- Set components AS PROTECTED (can NOT be uninstalled/can NOT be disabled) and ENABLE them and remove LOCKED state.

UPDATE `#__extensions`
SET `protected` = 1, `enabled` = 1, `locked` = 0
WHERE (`type` = 'component' AND `element` IN (
	'com_actionlogs',
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
	'com_privacy',
	'com_redirect',
	'com_tags',
	'com_templates',
	'com_users',
	'com_workflow',
	'com_wrapper'
));

-- Set FRONT-end modules AS PROTECTED (can NOT be uninstalled/can NOT be disabled) and ENABLE them and remove LOCKED state.

UPDATE `#__extensions`
SET `protected` = 1, `enabled` = 1, `locked` = 0
WHERE (`type` = 'module' AND `client_id` = 0 AND `element` IN (
	'mod_breadcrumbs',
	'mod_custom',
	'mod_languages',
	'mod_login',
	'mod_menu',
	'mod_syndicate'
));


-- Set BACK-end modules AS PROTECTED (can NOT be uninstalled/can NOT be disabled) and ENABLE them and remove LOCKED state.

UPDATE `#__extensions`
SET `protected` = 1, `enabled` = 1, `locked` = 0
WHERE (`type` = 'module' AND `client_id` = 1 AND `element` IN (
	'mod_custom',
	'mod_login',
	'mod_loginsupport',
	'mod_quickicon',
	'mod_toolbar'
));

-- Set plugins AS PROTECTED (can NOT be uninstalled/can NOT be disabled) and ENABLE them and remove LOCKED state.

UPDATE `#__extensions`
SET `protected` = 1, `enabled` = 1, `locked` = 0
WHERE (`type` = 'plugin' AND
	(
		(`folder` = 'authentication' AND `element` IN (
			'joomla'
		))
		OR (`folder` = 'editors' AND `element` IN (
			'codemirror',
			'none'
		))
		OR (`folder` = 'extension' AND `element` IN (
			'joomla',
			'namespacemap'
		))
		OR (`folder` = 'installer' AND `element` IN (
			'folderinstaller',
			'override',
			'packageinstaller',
			'urlinstaller'
		))
		OR (`folder` = 'quickicon' AND `element` IN (
			'downloadkey',
			'extensionupdate',
			'joomlaupdate',
			'overridecheck',
			'phpversioncheck'
		))
		OR (`folder` = 'sampledata' AND `element` IN (
			'blog',
			'multilang',
			'testing'
		))
		OR (`folder` = 'system' AND `element` IN (
			'log',
			'logout',
			'remember'
		))
	)
);

-- Set libraries AS PROTECTED (can NOT be uninstalled/can NOT be disabled) and ENABLE them and remove LOCKED state.

UPDATE `#__extensions`
SET `protected` = 1, `enabled` = 1, `locked` = 0
WHERE (`type` = 'library' AND `element` IN (
	'joomla',
	'phpass'
));

-- Set languages AS PROTECTED (can NOT be uninstalled/can NOT be disabled) and ENABLE them and remove LOCKED state.

UPDATE `#__extensions`
SET `protected` = 1, `enabled` = 1, `locked` = 0
WHERE (`type` = 'language' AND `element` IN (
	'en-GB'
));

-- Set file extensions AS PROTECTED (can NOT be uninstalled/can NOT be disabled) and ENABLE them and remove LOCKED state.

UPDATE `#__extensions`
SET `protected` = 1, `enabled` = 1, `locked` = 0
WHERE (`type` = 'file' AND `element` IN (
	'joomla'
));

-- Set packages AS PROTECTED (can NOT be uninstalled/can NOT be disabled) and ENABLE them and remove LOCKED state.

UPDATE `#__extensions`
SET `protected` = 1, `enabled` = 1, `locked` = 0
WHERE (`type` = 'package' AND `element` IN (
	'pkg_en-GB'
));
