<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Extension Helper
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Extension Helper class.
 *
 * @since  __DEPLOY_VERSION__
 */
class JExtensionHelper
{
	protected static $coreExtensions = array(
		// Format: `type`, `element`, `folder`, `client_id`

		// Core component extensions.
		array('component', 'com_mailto', '', 0),
		array('component', 'com_wrapper', '', 0),
		array('component', 'com_admin', '', 1),
		array('component', 'com_ajax', '', 1),
		array('component', 'com_banners', '', 1),
		array('component', 'com_cache', '', 1),
		array('component', 'com_categories', '', 1),
		array('component', 'com_checkin', '', 1),
		array('component', 'com_contact', '', 1),
		array('component', 'com_cpanel', '', 1),
		array('component', 'com_installer', '', 1),
		array('component', 'com_languages', '', 1),
		array('component', 'com_login', '', 1),
		array('component', 'com_media', '', 1),
		array('component', 'com_menus', '', 1),
		array('component', 'com_messages', '', 1),
		array('component', 'com_modules', '', 1),
		array('component', 'com_newsfeeds', '', 1),
		array('component', 'com_plugins', '', 1),
		array('component', 'com_search', '', 1),
		array('component', 'com_templates', '', 1),
		array('component', 'com_content', '', 1),
		array('component', 'com_config', '', 1),
		array('component', 'com_redirect', '', 1),
		array('component', 'com_users', '', 1),
		array('component', 'com_finder', '', 1),
		array('component', 'com_tags', '', 1),
		array('component', 'com_contenthistory', '', 1),
		array('component', 'com_postinstall', '', 1),
		array('component', 'com_joomlaupdate', '', 1),
		array('component', 'com_fields', '', 1),
		array('component', 'com_associations', '', 1),

		// Core library extensions.
		array('library', 'phputf8', '', 0),
		array('library', 'joomla', '', 0),
		array('library', 'idna_convert', '', 0),
		array('library', 'fof', '', 0),
		array('library', 'phpass', '', 0),

		// Core module extensions.
		// - Site
		array('module', 'mod_articles_archive', '', 0),
		array('module', 'mod_articles_latest', '', 0),
		array('module', 'mod_articles_popular', '', 0),
		array('module', 'mod_banners', '', 0),
		array('module', 'mod_breadcrumbs', '', 0),
		array('module', 'mod_custom', '', 0),
		array('module', 'mod_feed', '', 0),
		array('module', 'mod_footer', '', 0),
		array('module', 'mod_login', '', 0),
		array('module', 'mod_menu', '', 0),
		array('module', 'mod_articles_news', '', 0),
		array('module', 'mod_random_image', '', 0),
		array('module', 'mod_related_items', '', 0),
		array('module', 'mod_search', '', 0),
		array('module', 'mod_stats', '', 0),
		array('module', 'mod_syndicate', '', 0),
		array('module', 'mod_users_latest', '', 0),
		array('module', 'mod_whosonline', '', 0),
		array('module', 'mod_wrapper', '', 0),
		array('module', 'mod_articles_category', '', 0),
		array('module', 'mod_articles_categories', '', 0),
		array('module', 'mod_languages', '', 0),
		array('module', 'mod_tags_popular', '', 0),
		array('module', 'mod_tags_similar', '', 0),
		array('module', 'mod_finder', '', 0),

		// - Administrator
		array('module', 'mod_custom', '', 1),
		array('module', 'mod_feed', '', 1),
		array('module', 'mod_latest', '', 1),
		array('module', 'mod_logged', '', 1),
		array('module', 'mod_login', '', 1),
		array('module', 'mod_menu', '', 1),
		array('module', 'mod_popular', '', 1),
		array('module', 'mod_quickicon', '', 1),
		array('module', 'mod_stats_admin', '', 1),
		array('module', 'mod_status', '', 1),
		array('module', 'mod_submenu', '', 1),
		array('module', 'mod_title', '', 1),
		array('module', 'mod_toolbar', '', 1),
		array('module', 'mod_multilangstatus', '', 1),
		array('module', 'mod_version', '', 1),

		// Core plugin extensions
		// - System
		array('plugin', 'languagefilter', 'system', 0),
		array('plugin', 'p3p', 'system', 0),
		array('plugin', 'cache', 'system', 0),
		array('plugin', 'debug', 'system', 0),
		array('plugin', 'log', 'system', 0),
		array('plugin', 'redirect', 'system', 0),
		array('plugin', 'remember', 'system', 0),
		array('plugin', 'sef', 'system', 0),
		array('plugin', 'logout', 'system', 0),
		array('plugin', 'languagecode', 'system', 0),
		array('plugin', 'updatenotification', 'system', 0),
		array('plugin', 'stats', 'system', 0),
		array('plugin', 'fields', 'system', 0),
		array('plugin', 'highlight', 'system', 0),

		// - Content
		array('plugin', 'contact', 'content', 0),
		array('plugin', 'emailcloak', 'content', 0),
		array('plugin', 'loadmodule', 'content', 0),
		array('plugin', 'pagebreak', 'content', 0),
		array('plugin', 'pagenavigation', 'content', 0),
		array('plugin', 'vote', 'content', 0),
		array('plugin', 'fields', 'content', 0),
		array('plugin', 'joomla', 'content', 0),
		array('plugin', 'finder', 'content', 0),

		// - Extension
		array('plugin', 'joomla', 'extension', 0),

		// - Captcha
		array('plugin', 'recaptcha', 'captcha', 0),

		// - Installer
		array('plugin', 'packageinstaller', 'installer', 0),
		array('plugin', 'folderinstaller', 'installer', 0),
		array('plugin', 'urlinstaller', 'installer', 0),

		// - User
		array('plugin', 'contactcreator', 'user', 0),
		array('plugin', 'joomla', 'user', 0),
		array('plugin', 'profile', 'user', 0),

		// - Authentication
		array('plugin', 'gmail', 'authentication', 0),
		array('plugin', 'joomla', 'authentication', 0),
		array('plugin', 'ldap', 'authentication', 0),
		array('plugin', 'cookie', 'authentication', 0),

		// - Two Factor Authentication
		array('plugin', 'totp', 'twofactorauth', 0),
		array('plugin', 'yubikey', 'twofactorauth', 0),

		// - QuickIcon
		array('plugin', 'joomlaupdate', 'quickicon', 0),
		array('plugin', 'extensionupdate', 'quickicon', 0),
		array('plugin', 'phpversioncheck', 'quickicon', 0),

		// - Editors
		array('plugin', 'codemirror', 'editors', 0),
		array('plugin', 'none', 'editors', 0),
		array('plugin', 'tinymce', 'editors', 0),

		// - Editors XTD
		array('plugin', 'article', 'editors-xtd', 0),
		array('plugin', 'image', 'editors-xtd', 0),
		array('plugin', 'pagebreak', 'editors-xtd', 0),
		array('plugin', 'readmore', 'editors-xtd', 0),
		array('plugin', 'module', 'editors-xtd', 0),
		array('plugin', 'menu', 'editors-xtd', 0),
		array('plugin', 'contact', 'editors-xtd', 0),
		array('plugin', 'fields', 'editors-xtd', 0),

		// - Search
		array('plugin', 'categories', 'search', 0),
		array('plugin', 'contacts', 'search', 0),
		array('plugin', 'content', 'search', 0),
		array('plugin', 'newsfeeds', 'search', 0),
		array('plugin', 'tags', 'search', 0),

		// - Finder
		array('plugin', 'categories', 'finder', 0),
		array('plugin', 'contacts', 'finder', 0),
		array('plugin', 'content', 'finder', 0),
		array('plugin', 'newsfeeds', 'finder', 0),
		array('plugin', 'tags', 'finder', 0),

		// - Fields
		array('plugin', 'calendar', 'fields', 0),
		array('plugin', 'checkboxes', 'fields', 0),
		array('plugin', 'color', 'fields', 0),
		array('plugin', 'editor', 'fields', 0),
		array('plugin', 'imagelist', 'fields', 0),
		array('plugin', 'integer', 'fields', 0),
		array('plugin', 'list', 'fields', 0),
		array('plugin', 'media', 'fields', 0),
		array('plugin', 'radio', 'fields', 0),
		array('plugin', 'sql', 'fields', 0),
		array('plugin', 'text', 'fields', 0),
		array('plugin', 'textarea', 'fields', 0),
		array('plugin', 'url', 'fields', 0),
		array('plugin', 'user', 'fields', 0),
		array('plugin', 'usergrouplist', 'fields', 0),

		// Core template extensions
		// - Site
		array('template', 'beez3', '', 0),
		array('template', 'protostar', '', 0),

		// - Administrator
		array('template', 'hathor', '', 1),
		array('template', 'isis', '', 1),

		// Core language extensions
		// - Site
		array('language', 'en-GB', '', 0),

		// - Administrator
		array('language', 'en-GB', '', 1),

		// Core file extensions
		array('file', 'joomla', '', 0),

		// Core package extensions
		array('package', 'pkg_en-GB', '', 0),
	);

	protected static $whereCondition = '';

	protected static $coreExtensionsIDs = array();

	/**
	 * Class constructor.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected static function initWhereCondition()
	{
		$db = JFactory::getDbo();

		foreach (self::$coreExtensions as $extension)
		{
			self::$whereCondition .= $db->qn('type') . ' = ' . $db->q($extension[0])
				. ' AND ' . $db->qn('element') . ' = ' . $db->q($extension[1])
				. ' AND ' . $db->qn('client_id') . ' = ' . $db->q($extension[3]);

			if ($extension[2] !== '')
			{
				self::$whereCondition .= ' AND ' . $db->qn('folder') . ' = ' . $db->q($extension[2]);
			}

			self::$whereCondition .= ' OR ';
		}

		self::$whereCondition .= '1 = 2';
	}

	/**
	 * Init the array of core extensions IDs
	 * This function can be called to rebuild the array
	 * e.g. after a discovery installation.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function initCoreExtensionsIds()
	{
		if (self::$whereCondition === '')
		{
			self::initWhereCondition();
		}

		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('extension_id'))
			->from($db->qn('#__extensions'))
			->where(self::$whereCondition);

		// Get the IDs in ascending order
		$query->order($db->qn('extension_id') . ' ASC');

		self::$coreExtensionsIDs = $db->setQuery($query)->loadColumn();

		if (self::$coreExtensionsIDs === null)
		{
			throw new RuntimeException(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'), 1000);
		}
	}

	/**
	 * Gets the core extensions.
	 *
	 * @return  array  Array with core extensions.
	 *                 Each extension is an array with following format:
	 *                 `type`, `element`, `folder`, `client_id`.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getCoreExtensions()
	{
		return self::$coreExtensions;
	}

	/**
	 * Gets the where condition for database queries for core extensions.
	 * In opposite to using the list of IDs as returned by functions
	 * getCoreExtensionsIds and getCoreExtensionsIdsList, this does not
	 * result in an extra query to the database just for getting the IDs.
	 *
	 * @return  string  The where condition for restricting queries on the
	 *                  extensions table to core extensions.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getWhereCondition()
	{
		if (self::$whereCondition === '')
		{
			self::initWhereCondition();
		}

		return self::$whereCondition;
	}

	/**
	 * Gets the IDs of installed core extensions.
	 *
	 * Note that this causes an extra database query to the extensions table
	 * to get the IDs, so it is only economic if you need this array of IDs later
	 * in the code. Using function getWhereCondition() to get a where clause to
	 * restrict your queries to core extensions will not cause such an extra read
	 * of the database and so is more economic if you query the database only 1 time
	 * and so not using the primary key will cause less performance loss than doing
	 * an additional query.
	 *
	 * @return  array  Array of core extension IDs.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getCoreExtensionsIds()
	{
		if (!isset(self::$coreExtensionsIDs[0]))
		{
			self::initCoreExtensionsIds();
		}

		return self::$coreExtensionsIDs;
	}

	/**
	 * Gets the IDs of installed core extensions as comma-separated list which
	 * can be used as condition in SQL statements like "WHERE extension_id IN (1,2,3)"
	 * or "WHERE extension_id NOT IN (1,2,3)".
	 *
	 * Note that this causes an extra database query to the extensions table
	 * to get the IDs, so it is only economic if you need this list of IDs later
	 * in the code or more than 1 time. Using function getWhereCondition() to
	 * get a where clause to restrict your queries to core extensions will not
	 * cause such an extra read of the database and so is more economic if you
	 * query the database only 1 time and so not using the primary key will cause
	 * less performance loss than doing an additional query.
	 *
	 * @return  string  Comma-separated list of core extension IDs.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getCoreExtensionsIdsList()
	{
		if (!isset(self::$coreExtensionsIDs[0]))
		{
			self::initCoreExtensionsIds();
		}

		return implode(',', self::$coreExtensionsIDs);
	}

	/**
	 * Check if an extension is core or not
	 *
	 * @param   string   $type       The extension's type.
	 * @param   string   $element    The extension's element name.
	 * @param   string   $folder     The extension's folder.
	 * @param   integer  $client_id  The extension's client ID.
	 *
	 * @return  boolean  True if core, false if not.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function checkIfCoreExtension($type, $element, $folder, $client_id)
	{
		if (in_array(array($type, $element, $folder, $client_id), self::$coreExtensions))
		{
			return true;
		}

		return false;
	}

	/**
	 * Check if an extension ID belongs to a core extension or not
	 *
	 * @param   integer  $extension_id  The extension's ID.
	 *
	 * @return  boolean  True if core, false if not.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function checkIfCoreExtensionID($extension_id)
	{
		if (!isset(self::$coreExtensionsIDs[0]))
		{
			self::initCoreExtensionsIds();
		}

		if (in_array($extension_id, self::$coreExtensionsIDs))
		{
			return true;
		}

		return false;
	}
}
