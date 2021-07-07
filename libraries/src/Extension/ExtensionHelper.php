<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

defined('JPATH_PLATFORM') or die;

/**
 * Extension Helper class.
 *
 * @since       3.7.4
 *
 * @deprecated  4.0  Replace class with a non static methods for better testing
 */
class ExtensionHelper
{
	/**
	 * Array of core extensions
	 * Each element is an array with elements "type", "element", "folder" and
	 * "client_id".
	 *
	 * @var    array
	 * @since  3.7.4
	 */
	protected static $coreExtensions = array(
		// Format: `type`, `element`, `folder`, `client_id`

		// Core component extensions
		array('component', 'com_actionlogs', '', 1),
		array('component', 'com_admin', '', 1),
		array('component', 'com_ajax', '', 1),
		array('component', 'com_associations', '', 1),
		array('component', 'com_banners', '', 1),
		array('component', 'com_cache', '', 1),
		array('component', 'com_categories', '', 1),
		array('component', 'com_checkin', '', 1),
		array('component', 'com_config', '', 1),
		array('component', 'com_contact', '', 1),
		array('component', 'com_content', '', 1),
		array('component', 'com_contenthistory', '', 1),
		array('component', 'com_cpanel', '', 1),
		array('component', 'com_fields', '', 1),
		array('component', 'com_finder', '', 1),
		array('component', 'com_installer', '', 1),
		array('component', 'com_joomlaupdate', '', 1),
		array('component', 'com_languages', '', 1),
		array('component', 'com_login', '', 1),
		array('component', 'com_mailto', '', 0),
		array('component', 'com_media', '', 1),
		array('component', 'com_menus', '', 1),
		array('component', 'com_messages', '', 1),
		array('component', 'com_modules', '', 1),
		array('component', 'com_newsfeeds', '', 1),
		array('component', 'com_plugins', '', 1),
		array('component', 'com_postinstall', '', 1),
		array('component', 'com_privacy', '', 1),
		array('component', 'com_redirect', '', 1),
		array('component', 'com_search', '', 1),
		array('component', 'com_tags', '', 1),
		array('component', 'com_templates', '', 1),
		array('component', 'com_users', '', 1),
		array('component', 'com_wrapper', '', 0),

		// Core file extensions
		array('file', 'joomla', '', 0),

		// Core language extensions - administrator
		array('language', 'en-GB', '', 1),

		// Core language extensions - site
		array('language', 'en-GB', '', 0),

		// Core library extensions
		array('library', 'fof', '', 0),
		array('library', 'idna_convert', '', 0),
		array('library', 'joomla', '', 0),
		array('library', 'phpass', '', 0),
		array('library', 'phputf8', '', 0),

		// Core module extensions - administrator
		array('module', 'mod_custom', '', 1),
		array('module', 'mod_feed', '', 1),
		array('module', 'mod_latest', '', 1),
		array('module', 'mod_latestactions', '', 1),
		array('module', 'mod_logged', '', 1),
		array('module', 'mod_login', '', 1),
		array('module', 'mod_menu', '', 1),
		array('module', 'mod_multilangstatus', '', 1),
		array('module', 'mod_popular', '', 1),
		array('module', 'mod_privacy_dashboard', '', 1),
		array('module', 'mod_quickicon', '', 1),
		array('module', 'mod_sampledata', '', 1),
		array('module', 'mod_stats_admin', '', 1),
		array('module', 'mod_status', '', 1),
		array('module', 'mod_submenu', '', 1),
		array('module', 'mod_title', '', 1),
		array('module', 'mod_toolbar', '', 1),
		array('module', 'mod_version', '', 1),

		// Core module extensions - site
		array('module', 'mod_articles_archive', '', 0),
		array('module', 'mod_articles_categories', '', 0),
		array('module', 'mod_articles_category', '', 0),
		array('module', 'mod_articles_latest', '', 0),
		array('module', 'mod_articles_news', '', 0),
		array('module', 'mod_articles_popular', '', 0),
		array('module', 'mod_banners', '', 0),
		array('module', 'mod_breadcrumbs', '', 0),
		array('module', 'mod_custom', '', 0),
		array('module', 'mod_feed', '', 0),
		array('module', 'mod_finder', '', 0),
		array('module', 'mod_footer', '', 0),
		array('module', 'mod_languages', '', 0),
		array('module', 'mod_login', '', 0),
		array('module', 'mod_menu', '', 0),
		array('module', 'mod_random_image', '', 0),
		array('module', 'mod_related_items', '', 0),
		array('module', 'mod_search', '', 0),
		array('module', 'mod_stats', '', 0),
		array('module', 'mod_syndicate', '', 0),
		array('module', 'mod_tags_popular', '', 0),
		array('module', 'mod_tags_similar', '', 0),
		array('module', 'mod_users_latest', '', 0),
		array('module', 'mod_whosonline', '', 0),
		array('module', 'mod_wrapper', '', 0),

		// Core package extensions
		array('package', 'pkg_en-GB', '', 0),

		// Core plugin extensions - actionlog
		array('plugin', 'joomla', 'actionlog', 0),

		// Core plugin extensions - authentication
		array('plugin', 'cookie', 'authentication', 0),
		array('plugin', 'gmail', 'authentication', 0),
		array('plugin', 'joomla', 'authentication', 0),
		array('plugin', 'ldap', 'authentication', 0),

		// Core plugin extensions - captcha
		array('plugin', 'recaptcha', 'captcha', 0),
		array('plugin', 'recaptcha_invisible', 'captcha', 0),

		// Core plugin extensions - content
		array('plugin', 'confirmconsent', 'content', 0),
		array('plugin', 'contact', 'content', 0),
		array('plugin', 'emailcloak', 'content', 0),
		array('plugin', 'fields', 'content', 0),
		array('plugin', 'finder', 'content', 0),
		array('plugin', 'joomla', 'content', 0),
		array('plugin', 'loadmodule', 'content', 0),
		array('plugin', 'pagebreak', 'content', 0),
		array('plugin', 'pagenavigation', 'content', 0),
		array('plugin', 'vote', 'content', 0),

		// Core plugin extensions - editors
		array('plugin', 'codemirror', 'editors', 0),
		array('plugin', 'none', 'editors', 0),
		array('plugin', 'tinymce', 'editors', 0),

		// Core plugin extensions - editors xtd
		array('plugin', 'article', 'editors-xtd', 0),
		array('plugin', 'contact', 'editors-xtd', 0),
		array('plugin', 'fields', 'editors-xtd', 0),
		array('plugin', 'image', 'editors-xtd', 0),
		array('plugin', 'menu', 'editors-xtd', 0),
		array('plugin', 'module', 'editors-xtd', 0),
		array('plugin', 'pagebreak', 'editors-xtd', 0),
		array('plugin', 'readmore', 'editors-xtd', 0),

		// Core plugin extensions - extension
		array('plugin', 'joomla', 'extension', 0),

		// Core plugin extensions - fields
		array('plugin', 'calendar', 'fields', 0),
		array('plugin', 'checkboxes', 'fields', 0),
		array('plugin', 'color', 'fields', 0),
		array('plugin', 'editor', 'fields', 0),
		array('plugin', 'imagelist', 'fields', 0),
		array('plugin', 'integer', 'fields', 0),
		array('plugin', 'list', 'fields', 0),
		array('plugin', 'media', 'fields', 0),
		array('plugin', 'radio', 'fields', 0),
		array('plugin', 'repeatable', 'fields', 0),
		array('plugin', 'sql', 'fields', 0),
		array('plugin', 'text', 'fields', 0),
		array('plugin', 'textarea', 'fields', 0),
		array('plugin', 'url', 'fields', 0),
		array('plugin', 'user', 'fields', 0),
		array('plugin', 'usergrouplist', 'fields', 0),

		// Core plugin extensions - finder
		array('plugin', 'categories', 'finder', 0),
		array('plugin', 'contacts', 'finder', 0),
		array('plugin', 'content', 'finder', 0),
		array('plugin', 'newsfeeds', 'finder', 0),
		array('plugin', 'tags', 'finder', 0),

		// Core plugin extensions - installer
		array('plugin', 'folderinstaller', 'installer', 0),
		array('plugin', 'packageinstaller', 'installer', 0),
		array('plugin', 'urlinstaller', 'installer', 0),

		// Core plugin extensions - privacy
		array('plugin', 'actionlogs', 'privacy', 0),
		array('plugin', 'consents', 'privacy', 0),
		array('plugin', 'contact', 'privacy', 0),
		array('plugin', 'content', 'privacy', 0),
		array('plugin', 'message', 'privacy', 0),
		array('plugin', 'user', 'privacy', 0),

		// Core plugin extensions - quick icon
		array('plugin', 'extensionupdate', 'quickicon', 0),
		array('plugin', 'joomlaupdate', 'quickicon', 0),
		array('plugin', 'phpversioncheck', 'quickicon', 0),
		array('plugin', 'privacycheck', 'quickicon', 0),
		array('plugin', 'eos310', 'quickicon', 0),

		// Core plugin extensions - sample data
		array('plugin', 'blog', 'sampledata', 0),

		// Core plugin extensions - search
		array('plugin', 'categories', 'search', 0),
		array('plugin', 'contacts', 'search', 0),
		array('plugin', 'content', 'search', 0),
		array('plugin', 'newsfeeds', 'search', 0),
		array('plugin', 'tags', 'search', 0),

		// Core plugin extensions - system
		array('plugin', 'actionlogs', 'system', 0),
		array('plugin', 'cache', 'system', 0),
		array('plugin', 'debug', 'system', 0),
		array('plugin', 'fields', 'system', 0),
		array('plugin', 'highlight', 'system', 0),
		array('plugin', 'languagecode', 'system', 0),
		array('plugin', 'languagefilter', 'system', 0),
		array('plugin', 'log', 'system', 0),
		array('plugin', 'logout', 'system', 0),
		array('plugin', 'logrotation', 'system', 0),
		array('plugin', 'p3p', 'system', 0),
		array('plugin', 'privacyconsent', 'system', 0),
		array('plugin', 'redirect', 'system', 0),
		array('plugin', 'remember', 'system', 0),
		array('plugin', 'sef', 'system', 0),
		array('plugin', 'sessiongc', 'system', 0),
		array('plugin', 'stats', 'system', 0),
		array('plugin', 'updatenotification', 'system', 0),

		// Core plugin extensions - two factor authentication
		array('plugin', 'totp', 'twofactorauth', 0),
		array('plugin', 'yubikey', 'twofactorauth', 0),

		// Core plugin extensions - user
		array('plugin', 'contactcreator', 'user', 0),
		array('plugin', 'joomla', 'user', 0),
		array('plugin', 'profile', 'user', 0),
		array('plugin', 'terms', 'user', 0),

		// Core template extensions - administrator
		array('template', 'hathor', '', 1),
		array('template', 'isis', '', 1),

		// Core template extensions - site
		array('template', 'beez3', '', 0),
		array('template', 'protostar', '', 0),
	);

	/**
	 * Gets the core extensions.
	 *
	 * @return  array  Array with core extensions.
	 *                 Each extension is an array with following format:
	 *                 `type`, `element`, `folder`, `client_id`.
	 *
	 * @since   3.7.4
	 */
	public static function getCoreExtensions()
	{
		return self::$coreExtensions;
	}

	/**
	 * Check if an extension is core or not
	 *
	 * @param   string   $type      The extension's type.
	 * @param   string   $element   The extension's element name.
	 * @param   integer  $clientId  The extension's client ID. Default 0.
	 * @param   string   $folder    The extension's folder. Default ''.
	 *
	 * @return  boolean  True if core, false if not.
	 *
	 * @since   3.7.4
	 */
	public static function checkIfCoreExtension($type, $element, $clientId = 0, $folder = '')
	{
		return in_array(array($type, $element, $folder, $clientId), self::$coreExtensions);
	}
}
