<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Script file of Joomla CMS
 *
 * @since  1.6.4
 */
class JoomlaInstallerScript
{
	/**
	 * The Joomla Version we are updating from
	 *
	 * @var    string
	 * @since  3.7
	 */
	protected $fromVersion = null;

	/**
	 * Function to act prior to installation process begins
	 *
	 * @param   string     $action    Which action is happening (install|uninstall|discover_install|update)
	 * @param   JInstaller $installer The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function preflight($action, $installer)
	{
		if ($action === 'update')
		{
			// Get the version we are updating from
			if (!empty($installer->extension->manifest_cache))
			{
				$manifestValues = json_decode($installer->extension->manifest_cache, true);

				if ((array_key_exists('version', $manifestValues)))
				{
					$this->fromVersion = $manifestValues['version'];

					return true;
				}
			}

			return false;
		}

		return true;
	}

	/**
	 * Method to update Joomla!
	 *
	 * @param   JInstaller $installer The class calling this method
	 *
	 * @return  void
	 */
	public function update($installer)
	{
		$options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_update.php';

		JLog::addLogger($options, JLog::INFO, array('Update', 'databasequery', 'jerror'));
		JLog::add(JText::_('COM_JOOMLAUPDATE_UPDATE_LOG_DELETE_FILES'), JLog::INFO, 'Update');

		// This needs to stay for 2.5 update compatibility
		$this->deleteUnexistingFiles();
		$this->updateManifestCaches();
		$this->updateDatabase();
		$this->clearRadCache();
		$this->updateAssets($installer);
		$this->clearStatsCache();
		$this->convertTablesToUtf8mb4(true);
		$this->cleanJoomlaCache();

		// VERY IMPORTANT! THIS METHOD SHOULD BE CALLED LAST, SINCE IT COULD
		// LOGOUT ALL THE USERS
		$this->flushSessions();
	}

	/**
	 * Called after any type of action
	 *
	 * @param   string     $action    Which action is happening (install|uninstall|discover_install|update)
	 * @param   JInstaller $installer The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function postflight($action, $installer)
	{
		if ($action === 'update')
		{
			if (!empty($this->fromVersion) && version_compare($this->fromVersion, '3.7.0', 'lt'))
			{
				/*
				 * Add a menu item for com_associations, we need to do that here because with a plain sql statement we
				 * damage the nested set structure for the menu table
				 */
				$newMenuItem = JTable::getInstance('Menu');

				$data              = array();
				$data['menutype']  = 'main';
				$data['title']     = 'com_associations';
				$data['alias']     = 'Multilingual Associations';
				$data['path']      = 'Multilingual Associations';
				$data['link']      = 'index.php?option=com_associations';
				$data['type']      = 'component';
				$data['published'] = 1;
				$data['parent_id'] = 1;

				// We have used a SQL Statement to add the extension so using 34 is safe (fingers crossed)
				$data['component_id'] = 34;
				$data['img']          = 'class:associations';
				$data['language']     = '*';
				$data['client_id']    = 1;

				$newMenuItem->setLocation($data['parent_id'], 'last-child');

				if (!$newMenuItem->save($data))
				{
					// Install failed, roll back changes
					$installer->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $newMenuItem->getError()));

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to clear our stats plugin cache to ensure we get fresh data on Joomla Update
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	protected function clearStatsCache()
	{
		$db = JFactory::getDbo();

		try
		{
			// Get the params for the stats plugin
			$params = $db->setQuery(
				$db->getQuery(true)
					->select($db->quoteName('params'))
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
					->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
					->where($db->quoteName('element') . ' = ' . $db->quote('stats'))
			)->loadResult();
		}
		catch (Exception $e)
		{
			echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br />';

			return;
		}

		$params = json_decode($params, true);

		// Reset the last run parameter
		if (isset($params['lastrun']))
		{
			$params['lastrun'] = '';
		}

		$params = json_encode($params);

		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . ' = ' . $db->quote($params))
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('element') . ' = ' . $db->quote('stats'));

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br />';

			return;
		}
	}

	/**
	 * Method to update Database
	 *
	 * @return  void
	 */
	protected function updateDatabase()
	{
		if (JFactory::getDbo()->getServerType() === 'mysql')
		{
			$this->updateDatabaseMysql();
		}

		$this->uninstallEosPlugin();
		$this->removeJedUpdateserver();
	}

	/**
	 * Method to update MySQL Database
	 *
	 * @return  void
	 */
	protected function updateDatabaseMysql()
	{
		$db = JFactory::getDbo();

		$db->setQuery('SHOW ENGINES');

		try
		{
			$results = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br />';

			return;
		}

		foreach ($results as $result)
		{
			if ($result->Support != 'DEFAULT')
			{
				continue;
			}

			$db->setQuery('ALTER TABLE #__update_sites_extensions ENGINE = ' . $result->Engine);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br />';

				return;
			}

			break;
		}
	}

	/**
	 * Uninstall the 2.5 EOS plugin
	 *
	 * @return  void
	 */
	protected function uninstallEosPlugin()
	{
		$db = JFactory::getDbo();

		// Check if the 2.5 EOS plugin is present and uninstall it if so
		$id = $db->setQuery(
			$db->getQuery(true)
				->select('extension_id')
				->from('#__extensions')
				->where('name = ' . $db->quote('PLG_EOSNOTIFY'))
		)->loadResult();

		if (!$id)
		{
			return;
		}

		// We need to unprotect the plugin so we can uninstall it
		$db->setQuery(
			$db->getQuery(true)
				->update('#__extensions')
				->set('protected = 0')
				->where($db->quoteName('extension_id') . ' = ' . $id)
		)->execute();

		$installer = new JInstaller;
		$installer->uninstall('plugin', $id);
	}

	/**
	 * Remove the never used JED Updateserver
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function removeJedUpdateserver()
	{
		$db = JFactory::getDbo();

		try
		{
			// Get the update site ID of the JED Update server
			$id = $db->setQuery(
				$db->getQuery(true)
					->select('update_site_id')
					->from($db->quoteName('#__update_sites'))
					->where($db->quoteName('location') . ' = ' . $db->quote('https://update.joomla.org/jed/list.xml'))
			)->loadResult();

			// Delete from update sites
			$db->setQuery(
				$db->getQuery(true)
					->delete($db->quoteName('#__update_sites'))
					->where($db->quoteName('update_site_id') . ' = ' . $id)
			)->execute();

			// Delete from update sites extensions
			$db->setQuery(
				$db->getQuery(true)
					->delete($db->quoteName('#__update_sites_extensions'))
					->where($db->quoteName('update_site_id') . ' = ' . $id)
			)->execute();
		}
		catch (Exception $e)
		{
			echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br />';

			return;
		}
	}

	/**
	 * Update the manifest caches
	 *
	 * @return  void
	 */
	protected function updateManifestCaches()
	{
		$extensions = array(
			// Components
			// `type`, `element`, `folder`, `client_id`
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

			// Libraries
			array('library', 'phputf8', '', 0),
			array('library', 'joomla', '', 0),
			array('library', 'idna_convert', '', 0),
			array('library', 'fof', '', 0),
			array('library', 'phpass', '', 0),

			// Modules
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

			// Plugins
			array('plugin', 'gmail', 'authentication', 0),
			array('plugin', 'joomla', 'authentication', 0),
			array('plugin', 'ldap', 'authentication', 0),
			array('plugin', 'contact', 'content', 0),
			array('plugin', 'emailcloak', 'content', 0),
			array('plugin', 'loadmodule', 'content', 0),
			array('plugin', 'pagebreak', 'content', 0),
			array('plugin', 'pagenavigation', 'content', 0),
			array('plugin', 'vote', 'content', 0),
			array('plugin', 'codemirror', 'editors', 0),
			array('plugin', 'none', 'editors', 0),
			array('plugin', 'tinymce', 'editors', 0),
			array('plugin', 'article', 'editors-xtd', 0),
			array('plugin', 'image', 'editors-xtd', 0),
			array('plugin', 'pagebreak', 'editors-xtd', 0),
			array('plugin', 'readmore', 'editors-xtd', 0),
			array('plugin', 'categories', 'search', 0),
			array('plugin', 'contacts', 'search', 0),
			array('plugin', 'content', 'search', 0),
			array('plugin', 'newsfeeds', 'search', 0),
			array('plugin', 'tags', 'search', 0),
			array('plugin', 'languagefilter', 'system', 0),
			array('plugin', 'p3p', 'system', 0),
			array('plugin', 'cache', 'system', 0),
			array('plugin', 'debug', 'system', 0),
			array('plugin', 'log', 'system', 0),
			array('plugin', 'redirect', 'system', 0),
			array('plugin', 'remember', 'system', 0),
			array('plugin', 'sef', 'system', 0),
			array('plugin', 'logout', 'system', 0),
			array('plugin', 'contactcreator', 'user', 0),
			array('plugin', 'joomla', 'user', 0),
			array('plugin', 'profile', 'user', 0),
			array('plugin', 'joomla', 'extension', 0),
			array('plugin', 'joomla', 'content', 0),
			array('plugin', 'languagecode', 'system', 0),
			array('plugin', 'joomlaupdate', 'quickicon', 0),
			array('plugin', 'extensionupdate', 'quickicon', 0),
			array('plugin', 'recaptcha', 'captcha', 0),
			array('plugin', 'categories', 'finder', 0),
			array('plugin', 'contacts', 'finder', 0),
			array('plugin', 'content', 'finder', 0),
			array('plugin', 'newsfeeds', 'finder', 0),
			array('plugin', 'tags', 'finder', 0),
			array('plugin', 'totp', 'twofactorauth', 0),
			array('plugin', 'yubikey', 'twofactorauth', 0),
			array('plugin', 'updatenotification', 'system', 0),
			array('plugin', 'module', 'editors-xtd', 0),
			array('plugin', 'stats', 'system', 0),
			array('plugin', 'packageinstaller', 'installer', 0),
			array('plugin', 'folderinstaller', 'installer', 0),
			array('plugin', 'urlinstaller', 'installer', 0),
			array('plugin', 'phpversioncheck', 'quickicon', 0),
			array('plugin', 'menu', 'editors-xtd', 0),
			array('plugin', 'contact', 'editors-xtd', 0),
			array('plugin', 'fields', 'system', 0),
			array('plugin', 'calendar', 'fields', 0),
			array('plugin', 'checkboxes', 'fields', 0),
			array('plugin', 'color', 'fields', 0),
			array('plugin', 'editor', 'fields', 0),
			array('plugin', 'gallery', 'fields', 0),
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

			// Templates
			array('template', 'beez3', '', 0),
			array('template', 'hathor', '', 1),
			array('template', 'protostar', '', 0),
			array('template', 'isis', '', 1),

			// Languages
			array('language', 'en-GB', '', 0),
			array('language', 'en-GB', '', 1),

			// Files
			array('file', 'joomla', '', 0),

			// Packages
			array('package', 'pkg_en-GB', '', 0),
		);

		// Attempt to refresh manifest caches
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__extensions');

		foreach ($extensions as $extension)
		{
			$query->where(
				'type=' . $db->quote($extension[0])
				. ' AND element=' . $db->quote($extension[1])
				. ' AND folder=' . $db->quote($extension[2])
				. ' AND client_id=' . $extension[3], 'OR'
			);
		}

		$db->setQuery($query);

		try
		{
			$extensions = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br />';

			return;
		}

		$installer = new JInstaller;

		foreach ($extensions as $extension)
		{
			if (!$installer->refreshManifestCache($extension->extension_id))
			{
				echo JText::sprintf('FILES_JOOMLA_ERROR_MANIFEST', $extension->type, $extension->element, $extension->name, $extension->client_id) . '<br />';
			}
		}
	}

	/**
	 * Delete files that should not exist
	 *
	 * @return  void
	 */
	public function deleteUnexistingFiles()
	{
		$files = array(
			// Joomla 1.6 - 1.7 - 2.5
			'/libraries/cms/cmsloader.php',
			'/libraries/joomla/database/databaseexception.php',
			'/libraries/joomla/database/databasequery.php',
			'/libraries/joomla/environment/response.php',
			'/libraries/joomla/form/fields/templatestyle.php',
			'/libraries/joomla/form/fields/user.php',
			'/libraries/joomla/form/fields/menu.php',
			'/libraries/joomla/form/fields/helpsite.php',
			'/libraries/joomla/github/gists.php',
			'/libraries/joomla/github/issues.php',
			'/libraries/joomla/github/pulls.php',
			'/libraries/joomla/log/logentry.php',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.0.sql',
			'/administrator/components/com_admin/sql/updates/sqlsrv/2.5.2-2012-03-05.sql',
			'/administrator/components/com_admin/sql/updates/sqlsrv/2.5.3-2012-03-13.sql',
			'/administrator/components/com_admin/sql/updates/sqlsrv/index.html',
			'/administrator/components/com_content/models/fields/filters.php',
			'/administrator/components/com_users/controllers/config.php',
			'/administrator/components/com_users/helpers/levels.php',
			'/administrator/language/en-GB/en-GB.plg_system_finder.ini',
			'/administrator/language/en-GB/en-GB.plg_system_finder.sys.ini',
			'/administrator/modules/mod_quickicon/tmpl/default_button.php',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advhr/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advimage/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advlink/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advlist/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/autolink/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/autoresize/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/autosave/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/bbcode/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/contextmenu/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/directionality/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullpage/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullscreen/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/iespell/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/insertdatetime/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/layer/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/lists/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/nonbreaking/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/noneditable/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/pagebreak/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/paste/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/preview/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/print/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/save/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/searchreplace/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/spellchecker/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/style/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/tabfocus/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/template/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/visualchars/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/wordcount/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/editor_template_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/editor_template_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/tiny_mce_src.js',
			'/media/com_finder/images/calendar.png',
			'/media/com_finder/images/mime/index.html',
			'/media/com_finder/images/mime/pdf.png',
			'/components/com_media/controller.php',
			'/components/com_media/helpers/index.html',
			'/components/com_media/helpers/media.php',
			// Joomla 3.0
			'/administrator/components/com_admin/sql/updates/mysql/1.7.0-2011-06-06-2.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.0-2011-06-06.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.0.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.1-2011-09-15-2.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.1-2011-09-15-3.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.1-2011-09-15-4.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.1-2011-09-15.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.1-2011-09-17.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.1-2011-09-20.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.3-2011-10-15.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.3-2011-10-19.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.3-2011-11-10.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.4-2011-11-19.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.4-2011-11-23.sql',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.4-2011-12-12.sql',
			'/administrator/components/com_admin/views/sysinfo/tmpl/default_navigation.php',
			'/administrator/components/com_categories/config.xml',
			'/administrator/components/com_categories/helpers/categoriesadministrator.php',
			'/administrator/components/com_contact/elements/contact.php',
			'/administrator/components/com_contact/elements/index.html',
			'/administrator/components/com_content/elements/article.php',
			'/administrator/components/com_content/elements/author.php',
			'/administrator/components/com_content/elements/index.html',
			'/administrator/components/com_installer/models/fields/client.php',
			'/administrator/components/com_installer/models/fields/group.php',
			'/administrator/components/com_installer/models/fields/index.html',
			'/administrator/components/com_installer/models/fields/search.php',
			'/administrator/components/com_installer/models/forms/index.html',
			'/administrator/components/com_installer/models/forms/manage.xml',
			'/administrator/components/com_installer/views/install/tmpl/default_form.php',
			'/administrator/components/com_installer/views/manage/tmpl/default_filter.php',
			'/administrator/components/com_languages/views/installed/tmpl/default_ftp.php',
			'/administrator/components/com_languages/views/installed/tmpl/default_navigation.php',
			'/administrator/components/com_modules/models/fields/index.html',
			'/administrator/components/com_modules/models/fields/moduleorder.php',
			'/administrator/components/com_modules/models/fields/moduleposition.php',
			'/administrator/components/com_newsfeeds/elements/index.html',
			'/administrator/components/com_newsfeeds/elements/newsfeed.php',
			'/administrator/components/com_templates/views/prevuuw/index.html',
			'/administrator/components/com_templates/views/prevuuw/tmpl/default.php',
			'/administrator/components/com_templates/views/prevuuw/tmpl/index.html',
			'/administrator/components/com_templates/views/prevuuw/view.html.php',
			'/administrator/includes/menu.php',
			'/administrator/includes/router.php',
			'/administrator/manifests/packages/pkg_joomla.xml',
			'/administrator/modules/mod_submenu/helper.php',
			'/administrator/templates/hathor/css/ie6.css',
			'/administrator/templates/hathor/html/mod_submenu/index.html',
			'/administrator/templates/hathor/html/mod_submenu/default.php',
			'/components/com_media/controller.php',
			'/components/com_media/helpers/index.html',
			'/components/com_media/helpers/media.php',
			'/includes/menu.php',
			'/includes/pathway.php',
			'/includes/router.php',
			'/language/en-GB/en-GB.pkg_joomla.sys.ini',
			'/libraries/cms/controller/index.html',
			'/libraries/cms/controller/legacy.php',
			'/libraries/cms/model/index.html',
			'/libraries/cms/model/legacy.php',
			'/libraries/cms/schema/changeitemmysql.php',
			'/libraries/cms/schema/changeitemsqlazure.php',
			'/libraries/cms/schema/changeitemsqlsrv.php',
			'/libraries/cms/view/index.html',
			'/libraries/cms/view/legacy.php',
			'/libraries/joomla/application/application.php',
			'/libraries/joomla/application/categories.php',
			'/libraries/joomla/application/cli/daemon.php',
			'/libraries/joomla/application/cli/index.html',
			'/libraries/joomla/application/component/controller.php',
			'/libraries/joomla/application/component/controlleradmin.php',
			'/libraries/joomla/application/component/controllerform.php',
			'/libraries/joomla/application/component/helper.php',
			'/libraries/joomla/application/component/index.html',
			'/libraries/joomla/application/component/model.php',
			'/libraries/joomla/application/component/modeladmin.php',
			'/libraries/joomla/application/component/modelform.php',
			'/libraries/joomla/application/component/modelitem.php',
			'/libraries/joomla/application/component/modellist.php',
			'/libraries/joomla/application/component/view.php',
			'/libraries/joomla/application/helper.php',
			'/libraries/joomla/application/input.php',
			'/libraries/joomla/application/input/cli.php',
			'/libraries/joomla/application/input/cookie.php',
			'/libraries/joomla/application/input/files.php',
			'/libraries/joomla/application/input/index.html',
			'/libraries/joomla/application/menu.php',
			'/libraries/joomla/application/module/helper.php',
			'/libraries/joomla/application/module/index.html',
			'/libraries/joomla/application/pathway.php',
			'/libraries/joomla/application/web/webclient.php',
			'/libraries/joomla/base/node.php',
			'/libraries/joomla/base/object.php',
			'/libraries/joomla/base/observable.php',
			'/libraries/joomla/base/observer.php',
			'/libraries/joomla/base/tree.php',
			'/libraries/joomla/cache/storage/eaccelerator.php',
			'/libraries/joomla/cache/storage/helpers/helper.php',
			'/libraries/joomla/cache/storage/helpers/index.html',
			'/libraries/joomla/database/database/index.html',
			'/libraries/joomla/database/database/mysql.php',
			'/libraries/joomla/database/database/mysqlexporter.php',
			'/libraries/joomla/database/database/mysqli.php',
			'/libraries/joomla/database/database/mysqliexporter.php',
			'/libraries/joomla/database/database/mysqliimporter.php',
			'/libraries/joomla/database/database/mysqlimporter.php',
			'/libraries/joomla/database/database/mysqliquery.php',
			'/libraries/joomla/database/database/mysqlquery.php',
			'/libraries/joomla/database/database/sqlazure.php',
			'/libraries/joomla/database/database/sqlazurequery.php',
			'/libraries/joomla/database/database/sqlsrv.php',
			'/libraries/joomla/database/database/sqlsrvquery.php',
			'/libraries/joomla/database/exception.php',
			'/libraries/joomla/database/table.php',
			'/libraries/joomla/database/table/asset.php',
			'/libraries/joomla/database/table/category.php',
			'/libraries/joomla/database/table/content.php',
			'/libraries/joomla/database/table/extension.php',
			'/libraries/joomla/database/table/index.html',
			'/libraries/joomla/database/table/language.php',
			'/libraries/joomla/database/table/menu.php',
			'/libraries/joomla/database/table/menutype.php',
			'/libraries/joomla/database/table/module.php',
			'/libraries/joomla/database/table/session.php',
			'/libraries/joomla/database/table/update.php',
			'/libraries/joomla/database/table/user.php',
			'/libraries/joomla/database/table/usergroup.php',
			'/libraries/joomla/database/table/viewlevel.php',
			'/libraries/joomla/database/tablenested.php',
			'/libraries/joomla/environment/request.php',
			'/libraries/joomla/environment/uri.php',
			'/libraries/joomla/error/error.php',
			'/libraries/joomla/error/exception.php',
			'/libraries/joomla/error/index.html',
			'/libraries/joomla/error/log.php',
			'/libraries/joomla/error/profiler.php',
			'/libraries/joomla/filesystem/archive.php',
			'/libraries/joomla/filesystem/archive/bzip2.php',
			'/libraries/joomla/filesystem/archive/gzip.php',
			'/libraries/joomla/filesystem/archive/index.html',
			'/libraries/joomla/filesystem/archive/tar.php',
			'/libraries/joomla/filesystem/archive/zip.php',
			'/libraries/joomla/form/fields/category.php',
			'/libraries/joomla/form/fields/componentlayout.php',
			'/libraries/joomla/form/fields/contentlanguage.php',
			'/libraries/joomla/form/fields/editor.php',
			'/libraries/joomla/form/fields/editors.php',
			'/libraries/joomla/form/fields/media.php',
			'/libraries/joomla/form/fields/menuitem.php',
			'/libraries/joomla/form/fields/modulelayout.php',
			'/libraries/joomla/html/editor.php',
			'/libraries/joomla/html/html/access.php',
			'/libraries/joomla/html/html/batch.php',
			'/libraries/joomla/html/html/behavior.php',
			'/libraries/joomla/html/html/category.php',
			'/libraries/joomla/html/html/content.php',
			'/libraries/joomla/html/html/contentlanguage.php',
			'/libraries/joomla/html/html/date.php',
			'/libraries/joomla/html/html/email.php',
			'/libraries/joomla/html/html/form.php',
			'/libraries/joomla/html/html/grid.php',
			'/libraries/joomla/html/html/image.php',
			'/libraries/joomla/html/html/index.html',
			'/libraries/joomla/html/html/jgrid.php',
			'/libraries/joomla/html/html/list.php',
			'/libraries/joomla/html/html/menu.php',
			'/libraries/joomla/html/html/number.php',
			'/libraries/joomla/html/html/rules.php',
			'/libraries/joomla/html/html/select.php',
			'/libraries/joomla/html/html/sliders.php',
			'/libraries/joomla/html/html/string.php',
			'/libraries/joomla/html/html/tabs.php',
			'/libraries/joomla/html/html/tel.php',
			'/libraries/joomla/html/html/user.php',
			'/libraries/joomla/html/pagination.php',
			'/libraries/joomla/html/pane.php',
			'/libraries/joomla/html/parameter.php',
			'/libraries/joomla/html/parameter/element.php',
			'/libraries/joomla/html/parameter/element/calendar.php',
			'/libraries/joomla/html/parameter/element/category.php',
			'/libraries/joomla/html/parameter/element/componentlayouts.php',
			'/libraries/joomla/html/parameter/element/contentlanguages.php',
			'/libraries/joomla/html/parameter/element/editors.php',
			'/libraries/joomla/html/parameter/element/filelist.php',
			'/libraries/joomla/html/parameter/element/folderlist.php',
			'/libraries/joomla/html/parameter/element/helpsites.php',
			'/libraries/joomla/html/parameter/element/hidden.php',
			'/libraries/joomla/html/parameter/element/imagelist.php',
			'/libraries/joomla/html/parameter/element/index.html',
			'/libraries/joomla/html/parameter/element/languages.php',
			'/libraries/joomla/html/parameter/element/list.php',
			'/libraries/joomla/html/parameter/element/menu.php',
			'/libraries/joomla/html/parameter/element/menuitem.php',
			'/libraries/joomla/html/parameter/element/modulelayouts.php',
			'/libraries/joomla/html/parameter/element/password.php',
			'/libraries/joomla/html/parameter/element/radio.php',
			'/libraries/joomla/html/parameter/element/spacer.php',
			'/libraries/joomla/html/parameter/element/sql.php',
			'/libraries/joomla/html/parameter/element/templatestyle.php',
			'/libraries/joomla/html/parameter/element/text.php',
			'/libraries/joomla/html/parameter/element/textarea.php',
			'/libraries/joomla/html/parameter/element/timezones.php',
			'/libraries/joomla/html/parameter/element/usergroup.php',
			'/libraries/joomla/html/parameter/index.html',
			'/libraries/joomla/html/toolbar.php',
			'/libraries/joomla/html/toolbar/button.php',
			'/libraries/joomla/html/toolbar/button/confirm.php',
			'/libraries/joomla/html/toolbar/button/custom.php',
			'/libraries/joomla/html/toolbar/button/help.php',
			'/libraries/joomla/html/toolbar/button/index.html',
			'/libraries/joomla/html/toolbar/button/link.php',
			'/libraries/joomla/html/toolbar/button/popup.php',
			'/libraries/joomla/html/toolbar/button/separator.php',
			'/libraries/joomla/html/toolbar/button/standard.php',
			'/libraries/joomla/html/toolbar/index.html',
			'/libraries/joomla/image/filters/brightness.php',
			'/libraries/joomla/image/filters/contrast.php',
			'/libraries/joomla/image/filters/edgedetect.php',
			'/libraries/joomla/image/filters/emboss.php',
			'/libraries/joomla/image/filters/grayscale.php',
			'/libraries/joomla/image/filters/index.html',
			'/libraries/joomla/image/filters/negate.php',
			'/libraries/joomla/image/filters/sketchy.php',
			'/libraries/joomla/image/filters/smooth.php',
			'/libraries/joomla/language/help.php',
			'/libraries/joomla/language/latin_transliterate.php',
			'/libraries/joomla/log/logexception.php',
			'/libraries/joomla/log/loggers/database.php',
			'/libraries/joomla/log/loggers/echo.php',
			'/libraries/joomla/log/loggers/formattedtext.php',
			'/libraries/joomla/log/loggers/index.html',
			'/libraries/joomla/log/loggers/messagequeue.php',
			'/libraries/joomla/log/loggers/syslog.php',
			'/libraries/joomla/log/loggers/w3c.php',
			'/libraries/joomla/methods.php',
			'/libraries/joomla/session/storage/eaccelerator.php',
			'/libraries/joomla/string/stringnormalize.php',
			'/libraries/joomla/utilities/date.php',
			'/libraries/joomla/utilities/simplecrypt.php',
			'/libraries/joomla/utilities/simplexml.php',
			'/libraries/joomla/utilities/string.php',
			'/libraries/joomla/utilities/xmlelement.php',
			'/media/plg_quickicon_extensionupdate/extensionupdatecheck.js',
			'/media/plg_quickicon_joomlaupdate/jupdatecheck.js',
			// Joomla! 3.1
			'/libraries/joomla/application/router.php',
			'/libraries/joomla/form/rules/boolean.php',
			'/libraries/joomla/form/rules/color.php',
			'/libraries/joomla/form/rules/email.php',
			'/libraries/joomla/form/rules/equals.php',
			'/libraries/joomla/form/rules/index.html',
			'/libraries/joomla/form/rules/options.php',
			'/libraries/joomla/form/rules/rules.php',
			'/libraries/joomla/form/rules/tel.php',
			'/libraries/joomla/form/rules/url.php',
			'/libraries/joomla/form/rules/username.php',
			'/libraries/joomla/html/access.php',
			'/libraries/joomla/html/behavior.php',
			'/libraries/joomla/html/content.php',
			'/libraries/joomla/html/date.php',
			'/libraries/joomla/html/email.php',
			'/libraries/joomla/html/form.php',
			'/libraries/joomla/html/grid.php',
			'/libraries/joomla/html/html.php',
			'/libraries/joomla/html/index.html',
			'/libraries/joomla/html/jgrid.php',
			'/libraries/joomla/html/list.php',
			'/libraries/joomla/html/number.php',
			'/libraries/joomla/html/rules.php',
			'/libraries/joomla/html/select.php',
			'/libraries/joomla/html/sliders.php',
			'/libraries/joomla/html/string.php',
			'/libraries/joomla/html/tabs.php',
			'/libraries/joomla/html/tel.php',
			'/libraries/joomla/html/user.php',
			'/libraries/joomla/html/language/index.html',
			'/libraries/joomla/html/language/en-GB/en-GB.jhtmldate.ini',
			'/libraries/joomla/html/language/en-GB/index.html',
			'/libraries/joomla/installer/adapters/component.php',
			'/libraries/joomla/installer/adapters/file.php',
			'/libraries/joomla/installer/adapters/index.html',
			'/libraries/joomla/installer/adapters/language.php',
			'/libraries/joomla/installer/adapters/library.php',
			'/libraries/joomla/installer/adapters/module.php',
			'/libraries/joomla/installer/adapters/package.php',
			'/libraries/joomla/installer/adapters/plugin.php',
			'/libraries/joomla/installer/adapters/template.php',
			'/libraries/joomla/installer/extension.php',
			'/libraries/joomla/installer/helper.php',
			'/libraries/joomla/installer/index.html',
			'/libraries/joomla/installer/librarymanifest.php',
			'/libraries/joomla/installer/packagemanifest.php',
			'/libraries/joomla/pagination/index.html',
			'/libraries/joomla/pagination/object.php',
			'/libraries/joomla/pagination/pagination.php',
			'/libraries/legacy/html/contentlanguage.php',
			'/libraries/legacy/html/index.html',
			'/libraries/legacy/html/menu.php',
			'/libraries/legacy/menu/index.html',
			'/libraries/legacy/menu/menu.php',
			'/libraries/legacy/pathway/index.html',
			'/libraries/legacy/pathway/pathway.php',
			'/media/system/css/mooRainbow.css',
			'/media/system/js/mooRainbow-uncompressed.js',
			'/media/system/js/mooRainbow.js',
			'/media/system/js/swf-uncompressed.js',
			'/media/system/js/swf.js',
			'/media/system/js/uploader-uncompressed.js',
			'/media/system/js/uploader.js',
			'/media/system/swf/index.html',
			'/media/system/swf/uploader.swf',
			// Joomla! 3.2
			'/administrator/components/com_contact/models/fields/modal/contacts.php',
			'/administrator/components/com_newsfeeds/models/fields/modal/newsfeeds.php',
			'/libraries/idna_convert/example.php',
			'/media/editors/tinymce/jscripts/tiny_mce/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/license.txt',
			'/media/editors/tinymce/jscripts/tiny_mce/tiny_mce.js',
			'/media/editors/tinymce/jscripts/tiny_mce/tiny_mce_popup.js',
			'/media/editors/tinymce/jscripts/tiny_mce/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/langs/en.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advhr/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advhr/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advhr/rule.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advhr/css/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advhr/css/advhr.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advhr/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advhr/js/rule.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advhr/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advhr/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advimage/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advimage/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advimage/image.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advimage/css/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advimage/css/advimage.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advimage/img/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advimage/img/sample.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advimage/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advimage/js/image.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advimage/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advimage/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advlink/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advlink/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advlink/link.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advlink/css/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advlink/css/advlink.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advlink/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advlink/js/advlink.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advlink/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advlink/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advlist/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/advlist/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/autolink/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/autolink/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/autoresize/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/autoresize/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/autosave/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/autosave/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/autosave/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/autosave/langs/en.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/bbcode/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/bbcode/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/compat3x/editable_selects.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/compat3x/form_utils.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/compat3x/mctabs.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/compat3x/tiny_mce_popup.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/compat3x/validate.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/contextmenu/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/contextmenu/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/directionality/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/directionality/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/emotions.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-cool.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-cry.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-embarassed.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-foot-in-mouth.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-frown.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-innocent.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-kiss.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-laughing.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-money-mouth.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-sealed.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-smile.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-surprised.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-tongue-out.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-undecided.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-wink.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/img/smiley-yell.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/js/emotions.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/emotions/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullpage/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullpage/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullpage/fullpage.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullpage/css/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullpage/css/fullpage.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullpage/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullpage/js/fullpage.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullpage/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullpage/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullscreen/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullscreen/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/fullscreen/fullscreen.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/iespell/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/iespell/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/template.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/skins/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/skins/clearlooks2/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/skins/clearlooks2/window.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/alert.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/button.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/buttons.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/confirm.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/corners.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/horizontal.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/vertical.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/insertdatetime/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/insertdatetime/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/layer/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/layer/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/lists/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/lists/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/media.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/moxieplayer.swf',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/css/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/css/media.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/js/embed.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/js/media.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/nonbreaking/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/nonbreaking/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/noneditable/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/noneditable/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/pagebreak/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/pagebreak/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/paste/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/paste/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/paste/pastetext.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/paste/pastetext.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/paste/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/paste/js/pastetext.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/paste/js/pasteword.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/paste/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/paste/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/preview/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/preview/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/preview/example.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/preview/preview.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/preview/jscripts/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/preview/jscripts/embed.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/print/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/print/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/save/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/save/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/searchreplace/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/searchreplace/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/searchreplace/searchreplace.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/searchreplace/css/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/searchreplace/css/searchreplace.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/searchreplace/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/searchreplace/js/searchreplace.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/searchreplace/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/searchreplace/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/spellchecker/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/spellchecker/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/spellchecker/css/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/spellchecker/css/content.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/spellchecker/img/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/spellchecker/img/wline.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/style/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/style/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/style/props.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/style/readme.txt',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/style/css/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/style/css/props.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/style/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/style/js/props.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/style/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/style/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/tabfocus/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/tabfocus/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/cell.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/merge_cells.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/row.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/table.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/css/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/css/cell.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/css/row.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/css/table.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/js/cell.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/js/merge_cells.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/js/row.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/js/table.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/table/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/template/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/template/blank.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/template/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/template/template.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/template/css/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/template/css/template.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/template/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/template/js/template.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/template/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/template/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/visualblocks/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/visualblocks/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/visualblocks/css/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/visualblocks/css/visualblocks.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/visualchars/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/visualchars/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/wordcount/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/wordcount/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/abbr.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/acronym.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/attributes.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/cite.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/del.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/ins.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/css/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/css/attributes.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/css/popup.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/js/abbr.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/js/acronym.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/js/attributes.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/js/cite.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/js/del.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/js/element_common.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/js/ins.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/xhtmlxtras/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/about.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/anchor.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/charmap.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/color_picker.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/editor_template.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/image.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/link.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/shortcuts.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/source_editor.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/img/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/img/colorpicker.jpg',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/img/flash.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/img/icons.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/img/iframe.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/img/pagebreak.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/img/quicktime.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/img/realmedia.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/img/shockwave.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/img/trans.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/img/video.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/img/windowsmedia.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/js/about.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/js/anchor.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/js/charmap.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/js/color_picker.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/js/image.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/js/link.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/js/source_editor.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/langs/en.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/default/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/default/content.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/default/dialog.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/default/ui.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/default/img/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/default/img/buttons.png',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/default/img/items.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/default/img/menu_arrow.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/default/img/menu_check.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/default/img/progress.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/default/img/tabs.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/highcontrast/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/highcontrast/content.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/highcontrast/dialog.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/highcontrast/ui.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/o2k7/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/o2k7/content.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/o2k7/dialog.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/o2k7/ui.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/o2k7/ui_black.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/o2k7/ui_silver.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/o2k7/img/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/o2k7/img/button_bg.png',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/o2k7/img/button_bg_black.png',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/advanced/skins/o2k7/img/button_bg_silver.png',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/editor_template.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/img/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/img/icons.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/langs/en.js',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/skins/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/skins/default/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/skins/default/content.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/skins/default/ui.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/skins/o2k7/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/skins/o2k7/content.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/skins/o2k7/ui.css',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/skins/o2k7/img/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/themes/simple/skins/o2k7/img/button_bg.png',
			'/media/editors/tinymce/jscripts/tiny_mce/utils/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/utils/editable_selects.js',
			'/media/editors/tinymce/jscripts/tiny_mce/utils/form_utils.js',
			'/media/editors/tinymce/jscripts/tiny_mce/utils/mctabs.js',
			'/media/editors/tinymce/jscripts/tiny_mce/utils/validate.js',
			'/administrator/components/com_banners/models/fields/ordering.php',
			'/administrator/components/com_contact/models/fields/ordering.php',
			'/administrator/components/com_newsfeeds/models/fields/ordering.php',
			'/administrator/components/com_plugins/models/fields/ordering.php',
			'/administrator/components/com_weblinks/models/fields/ordering.php',
			'/administrator/includes/application.php',
			'/includes/application.php',
			'/libraries/legacy/application/helper.php',
			'/libraries/joomla/plugin/helper.php',
			'/libraries/joomla/plugin/index.html',
			'/libraries/joomla/plugin/plugin.php',
			'/libraries/legacy/component/helper.php',
			'/libraries/legacy/component/index.html',
			'/libraries/legacy/module/helper.php',
			'/libraries/legacy/module/index.html',
			'/administrator/components/com_templates/controllers/source.php',
			'/administrator/components/com_templates/models/source.php',
			'/administrator/components/com_templates/views/source/index.html',
			'/administrator/components/com_templates/views/source/tmpl/edit.php',
			'/administrator/components/com_templates/views/source/tmpl/edit_ftp.php',
			'/administrator/components/com_templates/views/source/tmpl/index.html',
			'/administrator/components/com_templates/views/source/view.html.php',
			'/media/editors/codemirror/css/csscolors.css',
			'/media/editors/codemirror/css/jscolors.css',
			'/media/editors/codemirror/css/phpcolors.css',
			'/media/editors/codemirror/css/sparqlcolors.css',
			'/media/editors/codemirror/css/xmlcolors.css',
			'/media/editors/codemirror/js/basefiles-uncompressed.js',
			'/media/editors/codemirror/js/basefiles.js',
			'/media/editors/codemirror/js/codemirror-uncompressed.js',
			'/media/editors/codemirror/js/editor.js',
			'/media/editors/codemirror/js/highlight.js',
			'/media/editors/codemirror/js/mirrorframe.js',
			'/media/editors/codemirror/js/parsecss.js',
			'/media/editors/codemirror/js/parsedummy.js',
			'/media/editors/codemirror/js/parsehtmlmixed.js',
			'/media/editors/codemirror/js/parsejavascript.js',
			'/media/editors/codemirror/js/parsephp.js',
			'/media/editors/codemirror/js/parsephphtmlmixed.js',
			'/media/editors/codemirror/js/parsesparql.js',
			'/media/editors/codemirror/js/parsexml.js',
			'/media/editors/codemirror/js/select.js',
			'/media/editors/codemirror/js/stringstream.js',
			'/media/editors/codemirror/js/tokenize.js',
			'/media/editors/codemirror/js/tokenizejavascript.js',
			'/media/editors/codemirror/js/tokenizephp.js',
			'/media/editors/codemirror/js/undo.js',
			'/media/editors/codemirror/js/util.js',
			'/administrator/components/com_weblinks/models/fields/index.html',
			'/plugins/user/joomla/postinstall/actions.php',
			'/plugins/user/joomla/postinstall/index.html',
			'/media/com_finder/js/finder.js',
			'/media/com_finder/js/highlighter.js',
			'/libraries/joomla/registry/format.php',
			'/libraries/joomla/registry/index.html',
			'/libraries/joomla/registry/registry.php',
			'/libraries/joomla/registry/format/index.html',
			'/libraries/joomla/registry/format/ini.php',
			'/libraries/joomla/registry/format/json.php',
			'/libraries/joomla/registry/format/php.php',
			'/libraries/joomla/registry/format/xml.php',
			'/libraries/joomla/github/users.php',
			'/media/system/js/validate-jquery-uncompressed.js',
			'/templates/beez3/html/message.php',
			'/libraries/fof/platform/joomla.php',
			'/libraries/fof/readme.txt',
			// Joomla 3.3.1
			'/administrator/templates/isis/html/message.php',
			// Joomla 3.3.6
			'/media/editors/tinymce/plugins/compat3x/editable_selects.js',
			'/media/editors/tinymce/plugins/compat3x/form_utils.js',
			'/media/editors/tinymce/plugins/compat3x/mctabs.js',
			'/media/editors/tinymce/plugins/compat3x/tiny_mce_popup.js',
			'/media/editors/tinymce/plugins/compat3x/validate.js',
			// Joomla! 3.4
			'/administrator/components/com_tags/helpers/html/index.html',
			'/administrator/components/com_tags/models/fields/index.html',
			'/administrator/manifests/libraries/phpmailer.xml',
			'/administrator/templates/hathor/html/com_finder/filter/index.html',
			'/administrator/templates/hathor/html/com_finder/statistics/index.html',
			'/components/com_contact/helpers/icon.php',
			'/language/en-GB/en-GB.lib_phpmailer.sys.ini',
			'/libraries/compat/jsonserializable.php',
			'/libraries/compat/password/lib/index.html',
			'/libraries/compat/password/lib/password.php',
			'/libraries/compat/password/lib/version_test.php',
			'/libraries/compat/password/index.html',
			'/libraries/compat/password/LICENSE.md',
			'/libraries/compat/index.html',
			'/libraries/fof/controller.php',
			'/libraries/fof/dispatcher.php',
			'/libraries/fof/inflector.php',
			'/libraries/fof/input.php',
			'/libraries/fof/model.php',
			'/libraries/fof/query.abstract.php',
			'/libraries/fof/query.element.php',
			'/libraries/fof/query.mysql.php',
			'/libraries/fof/query.mysqli.php',
			'/libraries/fof/query.sqlazure.php',
			'/libraries/fof/query.sqlsrv.php',
			'/libraries/fof/render.abstract.php',
			'/libraries/fof/render.joomla.php',
			'/libraries/fof/render.joomla3.php',
			'/libraries/fof/render.strapper.php',
			'/libraries/fof/string.utils.php',
			'/libraries/fof/table.php',
			'/libraries/fof/template.utils.php',
			'/libraries/fof/toolbar.php',
			'/libraries/fof/view.csv.php',
			'/libraries/fof/view.html.php',
			'/libraries/fof/view.json.php',
			'/libraries/fof/view.php',
			'/libraries/framework/Joomla/Application/Cli/Output/Processor/ColorProcessor.php',
			'/libraries/framework/Joomla/Application/Cli/Output/Processor/ProcessorInterface.php',
			'/libraries/framework/Joomla/Application/Cli/Output/Stdout.php',
			'/libraries/framework/Joomla/Application/Cli/Output/Xml.php',
			'/libraries/framework/Joomla/Application/Cli/CliOutput.php',
			'/libraries/framework/Joomla/Application/Cli/ColorProcessor.php',
			'/libraries/framework/Joomla/Application/Cli/ColorStyle.php',
			'/libraries/framework/index.html',
			'/libraries/framework/Joomla/DI/Exception/DependencyResolutionException.php',
			'/libraries/framework/Joomla/DI/Exception/index.html',
			'/libraries/framework/Joomla/DI/Container.php',
			'/libraries/framework/Joomla/DI/ContainerAwareInterface.php',
			'/libraries/framework/Joomla/DI/index.html',
			'/libraries/framework/Joomla/DI/ServiceProviderInterface.php',
			'/libraries/framework/Joomla/Registry/Format/index.html',
			'/libraries/framework/Joomla/Registry/Format/Ini.php',
			'/libraries/framework/Joomla/Registry/Format/Json.php',
			'/libraries/framework/Joomla/Registry/Format/Php.php',
			'/libraries/framework/Joomla/Registry/Format/Xml.php',
			'/libraries/framework/Joomla/Registry/Format/Yaml.php',
			'/libraries/framework/Joomla/Registry/AbstractRegistryFormat.php',
			'/libraries/framework/Joomla/Registry/index.html',
			'/libraries/framework/Joomla/Registry/Registry.php',
			'/libraries/framework/Symfony/Component/Yaml/Exception/DumpException.php',
			'/libraries/framework/Symfony/Component/Yaml/Exception/ExceptionInterface.php',
			'/libraries/framework/Symfony/Component/Yaml/Exception/index.html',
			'/libraries/framework/Symfony/Component/Yaml/Exception/ParseException.php',
			'/libraries/framework/Symfony/Component/Yaml/Exception/RuntimeException.php',
			'/libraries/framework/Symfony/Component/Yaml/Dumper.php',
			'/libraries/framework/Symfony/Component/Yaml/Escaper.php',
			'/libraries/framework/Symfony/Component/Yaml/index.html',
			'/libraries/framework/Symfony/Component/Yaml/Inline.php',
			'/libraries/framework/Symfony/Component/Yaml/LICENSE',
			'/libraries/framework/Symfony/Component/Yaml/Parser.php',
			'/libraries/framework/Symfony/Component/Yaml/Unescaper.php',
			'/libraries/framework/Symfony/Component/Yaml/Yaml.php',
			'/libraries/joomla/string/inflector.php',
			'/libraries/joomla/string/normalise.php',
			'/libraries/phpmailer/language/index.html',
			'/libraries/phpmailer/language/phpmailer.lang-joomla.php',
			'/libraries/phpmailer/index.html',
			'/libraries/phpmailer/LICENSE',
			'/libraries/phpmailer/phpmailer.php',
			'/libraries/phpmailer/pop.php',
			'/libraries/phpmailer/smtp.php',
			'/media/editors/codemirror/css/ambiance.css',
			'/media/editors/codemirror/css/codemirror.css',
			'/media/editors/codemirror/css/configuration.css',
			'/media/editors/codemirror/css/index.html',
			'/media/editors/codemirror/js/brace-fold.js',
			'/media/editors/codemirror/js/clike.js',
			'/media/editors/codemirror/js/closebrackets.js',
			'/media/editors/codemirror/js/closetag.js',
			'/media/editors/codemirror/js/codemirror.js',
			'/media/editors/codemirror/js/css.js',
			'/media/editors/codemirror/js/foldcode.js',
			'/media/editors/codemirror/js/foldgutter.js',
			'/media/editors/codemirror/js/fullscreen.js',
			'/media/editors/codemirror/js/htmlmixed.js',
			'/media/editors/codemirror/js/indent-fold.js',
			'/media/editors/codemirror/js/index.html',
			'/media/editors/codemirror/js/javascript.js',
			'/media/editors/codemirror/js/less.js',
			'/media/editors/codemirror/js/matchbrackets.js',
			'/media/editors/codemirror/js/matchtags.js',
			'/media/editors/codemirror/js/php.js',
			'/media/editors/codemirror/js/xml-fold.js',
			'/media/editors/codemirror/js/xml.js',
			'/media/editors/tinymce/skins/lightgray/fonts/icomoon.svg',
			'/media/editors/tinymce/skins/lightgray/fonts/icomoon.ttf',
			'/media/editors/tinymce/skins/lightgray/fonts/icomoon.woff',
			'/media/editors/tinymce/skins/lightgray/fonts/icomoon-small.eot',
			'/media/editors/tinymce/skins/lightgray/fonts/icomoon-small.svg',
			'/media/editors/tinymce/skins/lightgray/fonts/icomoon-small.ttf',
			'/media/editors/tinymce/skins/lightgray/fonts/icomoon-small.woff',
			'/media/editors/tinymce/skins/lightgray/fonts/readme.md',
			'/media/editors/tinymce/skins/lightgray/fonts/tinymce.dev.svg',
			'/media/editors/tinymce/skins/lightgray/fonts/tinymce-small.dev.svg',
			'/media/editors/tinymce/skins/lightgray/img/wline.gif',
			'/plugins/editors/codemirror/styles.css',
			'/plugins/editors/codemirror/styles.min.css',
			// Joomla! 3.4.1
			'/libraries/joomla/environment/request.php',
			'/media/editors/tinymce/templates/template_list.js',
			'/media/editors/codemirror/lib/addons-uncompressed.js',
			'/media/editors/codemirror/lib/codemirror-uncompressed.css',
			'/media/editors/codemirror/lib/codemirror-uncompressed.js',
			'/administrator/help/en-GB/Components_Banners_Banners.html',
			'/administrator/help/en-GB/Components_Banners_Banners_Edit.html',
			'/administrator/help/en-GB/Components_Banners_Categories.html',
			'/administrator/help/en-GB/Components_Banners_Category_Edit.html',
			'/administrator/help/en-GB/Components_Banners_Clients.html',
			'/administrator/help/en-GB/Components_Banners_Clients_Edit.html',
			'/administrator/help/en-GB/Components_Banners_Tracks.html',
			'/administrator/help/en-GB/Components_Contact_Categories.html',
			'/administrator/help/en-GB/Components_Contact_Category_Edit.html',
			'/administrator/help/en-GB/Components_Contacts_Contacts.html',
			'/administrator/help/en-GB/Components_Contacts_Contacts_Edit.html',
			'/administrator/help/en-GB/Components_Content_Categories.html',
			'/administrator/help/en-GB/Components_Content_Category_Edit.html',
			'/administrator/help/en-GB/Components_Messaging_Inbox.html',
			'/administrator/help/en-GB/Components_Messaging_Read.html',
			'/administrator/help/en-GB/Components_Messaging_Write.html',
			'/administrator/help/en-GB/Components_Newsfeeds_Categories.html',
			'/administrator/help/en-GB/Components_Newsfeeds_Category_Edit.html',
			'/administrator/help/en-GB/Components_Newsfeeds_Feeds.html',
			'/administrator/help/en-GB/Components_Newsfeeds_Feeds_Edit.html',
			'/administrator/help/en-GB/Components_Redirect_Manager.html',
			'/administrator/help/en-GB/Components_Redirect_Manager_Edit.html',
			'/administrator/help/en-GB/Components_Search.html',
			'/administrator/help/en-GB/Components_Weblinks_Categories.html',
			'/administrator/help/en-GB/Components_Weblinks_Category_Edit.html',
			'/administrator/help/en-GB/Components_Weblinks_Links.html',
			'/administrator/help/en-GB/Components_Weblinks_Links_Edit.html',
			'/administrator/help/en-GB/Content_Article_Manager.html',
			'/administrator/help/en-GB/Content_Article_Manager_Edit.html',
			'/administrator/help/en-GB/Content_Featured_Articles.html',
			'/administrator/help/en-GB/Content_Media_Manager.html',
			'/administrator/help/en-GB/Extensions_Extension_Manager_Discover.html',
			'/administrator/help/en-GB/Extensions_Extension_Manager_Install.html',
			'/administrator/help/en-GB/Extensions_Extension_Manager_Manage.html',
			'/administrator/help/en-GB/Extensions_Extension_Manager_Update.html',
			'/administrator/help/en-GB/Extensions_Extension_Manager_Warnings.html',
			'/administrator/help/en-GB/Extensions_Language_Manager_Content.html',
			'/administrator/help/en-GB/Extensions_Language_Manager_Edit.html',
			'/administrator/help/en-GB/Extensions_Language_Manager_Installed.html',
			'/administrator/help/en-GB/Extensions_Module_Manager.html',
			'/administrator/help/en-GB/Extensions_Module_Manager_Edit.html',
			'/administrator/help/en-GB/Extensions_Plugin_Manager.html',
			'/administrator/help/en-GB/Extensions_Plugin_Manager_Edit.html',
			'/administrator/help/en-GB/Extensions_Template_Manager_Styles.html',
			'/administrator/help/en-GB/Extensions_Template_Manager_Styles_Edit.html',
			'/administrator/help/en-GB/Extensions_Template_Manager_Templates.html',
			'/administrator/help/en-GB/Extensions_Template_Manager_Templates_Edit.html',
			'/administrator/help/en-GB/Extensions_Template_Manager_Templates_Edit_Source.html',
			'/administrator/help/en-GB/Glossary.html',
			'/administrator/help/en-GB/Menus_Menu_Item_Manager.html',
			'/administrator/help/en-GB/Menus_Menu_Item_Manager_Edit.html',
			'/administrator/help/en-GB/Menus_Menu_Manager.html',
			'/administrator/help/en-GB/Menus_Menu_Manager_Edit.html',
			'/administrator/help/en-GB/Site_Global_Configuration.html',
			'/administrator/help/en-GB/Site_Maintenance_Clear_Cache.html',
			'/administrator/help/en-GB/Site_Maintenance_Global_Check-in.html',
			'/administrator/help/en-GB/Site_Maintenance_Purge_Expired_Cache.html',
			'/administrator/help/en-GB/Site_System_Information.html',
			'/administrator/help/en-GB/Start_Here.html',
			'/administrator/help/en-GB/Users_Access_Levels.html',
			'/administrator/help/en-GB/Users_Access_Levels_Edit.html',
			'/administrator/help/en-GB/Users_Debug_Users.html',
			'/administrator/help/en-GB/Users_Groups.html',
			'/administrator/help/en-GB/Users_Groups_Edit.html',
			'/administrator/help/en-GB/Users_Mass_Mail_Users.html',
			'/administrator/help/en-GB/Users_User_Manager.html',
			'/administrator/help/en-GB/Users_User_Manager_Edit.html',
			'/administrator/components/com_config/views/index.html',
			'/administrator/components/com_config/views/application/index.html',
			'/administrator/components/com_config/views/application/view.html.php',
			'/administrator/components/com_config/views/application/tmpl/default.php',
			'/administrator/components/com_config/views/application/tmpl/default_cache.php',
			'/administrator/components/com_config/views/application/tmpl/default_cookie.php',
			'/administrator/components/com_config/views/application/tmpl/default_database.php',
			'/administrator/components/com_config/views/application/tmpl/default_debug.php',
			'/administrator/components/com_config/views/application/tmpl/default_filters.php',
			'/administrator/components/com_config/views/application/tmpl/default_ftp.php',
			'/administrator/components/com_config/views/application/tmpl/default_ftplogin.php',
			'/administrator/components/com_config/views/application/tmpl/default_locale.php',
			'/administrator/components/com_config/views/application/tmpl/default_mail.php',
			'/administrator/components/com_config/views/application/tmpl/default_metadata.php',
			'/administrator/components/com_config/views/application/tmpl/default_navigation.php',
			'/administrator/components/com_config/views/application/tmpl/default_permissions.php',
			'/administrator/components/com_config/views/application/tmpl/default_seo.php',
			'/administrator/components/com_config/views/application/tmpl/default_server.php',
			'/administrator/components/com_config/views/application/tmpl/default_session.php',
			'/administrator/components/com_config/views/application/tmpl/default_site.php',
			'/administrator/components/com_config/views/application/tmpl/default_system.php',
			'/administrator/components/com_config/views/application/tmpl/index.html',
			'/administrator/components/com_config/views/close/index.html',
			'/administrator/components/com_config/views/close/view.html.php',
			'/administrator/components/com_config/views/component/index.html',
			'/administrator/components/com_config/views/component/view.html.php',
			'/administrator/components/com_config/views/component/tmpl/default.php',
			'/administrator/components/com_config/views/component/tmpl/index.html',
			'/administrator/components/com_config/models/fields/filters.php',
			'/administrator/components/com_config/models/fields/index.html',
			'/administrator/components/com_config/models/forms/application.xml',
			'/administrator/components/com_config/models/forms/index.html',
			// Joomla 3.4.2
			'/libraries/composer_autoload.php',
			'/administrator/templates/hathor/html/com_categories/categories/default_batch.php',
			'/administrator/templates/hathor/html/com_tags/tags/default_batch.php',
			'/media/editors/codemirror/mode/clike/scala.html',
			'/media/editors/codemirror/mode/css/less.html',
			'/media/editors/codemirror/mode/css/less_test.js',
			'/media/editors/codemirror/mode/css/scss.html',
			'/media/editors/codemirror/mode/css/scss_test.js',
			'/media/editors/codemirror/mode/css/test.js',
			'/media/editors/codemirror/mode/gfm/test.js',
			'/media/editors/codemirror/mode/haml/test.js',
			'/media/editors/codemirror/mode/javascript/json-ld.html',
			'/media/editors/codemirror/mode/javascript/test.js',
			'/media/editors/codemirror/mode/javascript/typescript.html',
			'/media/editors/codemirror/mode/markdown/test.js',
			'/media/editors/codemirror/mode/php/test.js',
			'/media/editors/codemirror/mode/ruby/test.js',
			'/media/editors/codemirror/mode/shell/test.js',
			'/media/editors/codemirror/mode/slim/test.js',
			'/media/editors/codemirror/mode/stex/test.js',
			'/media/editors/codemirror/mode/textile/test.js',
			'/media/editors/codemirror/mode/verilog/test.js',
			'/media/editors/codemirror/mode/xml/test.js',
			'/media/editors/codemirror/mode/xquery/test.js',
			// Joomla 3.4.3
			'/libraries/classloader.php',
			'/libraries/ClassLoader.php',
			// Joomla 3.4.6
			'/components/com_wrapper/views/wrapper/metadata.xml',
			// Joomla 3.5.0
			'/media/com_joomlaupdate/default.js',
			'/media/com_joomlaupdate/encryption.js',
			'/media/com_joomlaupdate/json2.js',
			'/media/com_joomlaupdate/update.js',
			'/media/com_finder/css/finder-rtl.css',
			'/media/com_finder/css/selectfilter.css',
			'/media/com_finder/css/sliderfilter.css',
			'/media/com_finder/js/sliderfilter.js',
			'/media/editors/codemirror/mode/kotlin/kotlin.js',
			'/media/editors/codemirror/mode/kotlin/kotlin.min.js',
			'/media/editors/tinymce/plugins/compat3x/editable_selects.js',
			'/media/editors/tinymce/plugins/compat3x/form_utils.js',
			'/media/editors/tinymce/plugins/compat3x/mctabs.js',
			'/media/editors/tinymce/plugins/compat3x/tiny_mce_popup.js',
			'/media/editors/tinymce/plugins/compat3x/validate.js',
			'/libraries/vendor/symfony/yaml/Symfony/Component/Yaml/Dumper.php',
			'/libraries/vendor/symfony/yaml/Symfony/Component/Yaml/Escaper.php',
			'/libraries/vendor/symfony/yaml/Symfony/Component/Yaml/Inline.php',
			'/libraries/vendor/symfony/yaml/Symfony/Component/Yaml/LICENSE',
			'/libraries/vendor/symfony/yaml/Symfony/Component/Yaml/Parser.php',
			'/libraries/vendor/symfony/yaml/Symfony/Component/Yaml/Unescaper.php',
			'/libraries/vendor/symfony/yaml/Symfony/Component/Yaml/Yaml.php',
			'/libraries/vendor/symfony/yaml/Symfony/Component/Yaml/Exception/DumpException.php',
			'/libraries/vendor/symfony/yaml/Symfony/Component/Yaml/Exception/ExceptionInterface.php',
			'/libraries/vendor/symfony/yaml/Symfony/Component/Yaml/Exception/ParseException.php',
			'/libraries/vendor/symfony/yaml/Symfony/Component/Yaml/Exception/RuntimeException.php',
			'/libraries/vendor/phpmailer/phpmailer/extras/class.html2text.php',
			'/libraries/joomla/document/error/error.php',
			'/libraries/joomla/document/feed/feed.php',
			'/libraries/joomla/document/html/html.php',
			'/libraries/joomla/document/image/image.php',
			'/libraries/joomla/document/json/json.php',
			'/libraries/joomla/document/opensearch/opensearch.php',
			'/libraries/joomla/document/raw/raw.php',
			'/libraries/joomla/document/xml/xml.php',
			'/plugins/editors/tinymce/fields/skins.php',
			'/plugins/user/profile/fields/dob.php',
			'/plugins/user/profile/fields/tos.php',
			'/administrator/components/com_installer/views/languages/tmpl/default_filter.php',
			'/administrator/components/com_joomlaupdate/helpers/download.php',
			'/administrator/components/com_config/controller/application/refreshhelp.php',
			'/administrator/components/com_media/models/forms/index.html',
			// Joomla 3.6.0
			'/libraries/simplepie/README.txt',
			'/libraries/simplepie/simplepie.php',
			'/libraries/simplepie/LICENSE.txt',
			'/libraries/simplepie/idn/LICENCE',
			'/libraries/simplepie/idn/ReadMe.txt',
			'/libraries/simplepie/idn/idna_convert.class.php',
			'/libraries/simplepie/idn/npdata.ser',
			'/administrator/manifests/libraries/simplepie.xml',
			'/administrator/templates/isis/js/jquery.js',
			'/administrator/templates/isis/js/bootstrap.min.js',
			'/media/system/js/permissions.min.js',
			'/libraries/platform.php',
			'/plugins/user/profile/fields/tos.php',
			'/libraries/joomla/application/web/client.php',
			// Joomla! 3.6.1
			'/libraries/joomla/database/iterator/azure.php',
			'/media/editors/tinymce/skins/lightgray/fonts/icomoon.eot',
			// Joomla! 3.6.3
			'/media/editors/codemirror/mode/jade/jade.js',
			'/media/editors/codemirror/mode/jade/jade.min.js',
			// Joomla 3.7.0
			'/libraries/joomla/user/authentication.php',
			'/libraries/platform.php',
			'/libraries/joomla/data/data.php',
			'/libraries/joomla/data/dumpable.php',
			'/libraries/joomla/data/set.php',
			'/administrator/components/com_banners/views/banners/tmpl/default_batch.php',
			'/administrator/components/com_categories/views/category/tmpl/edit_extrafields.php',
			'/administrator/components/com_categories/views/category/tmpl/edit_options.php',
			'/administrator/components/com_categories/views/categories/tmpl/default_batch.php',
			'/administrator/components/com_content/views/articles/tmpl/default_batch.php',
			'/administrator/components/com_menus/views/items/tmpl/default_batch.php',
			'/administrator/components/com_modules/views/modules/tmpl/default_batch.php',
			'/administrator/components/com_newsfeeds/views/newsfeeds/tmpl/default_batch.php',
			'/administrator/components/com_redirect/views/links/tmpl/default_batch.php',
			'/administrator/components/com_tags/views/tags/tmpl/default_batch.php',
			'/administrator/components/com_users/views/users/tmpl/default_batch.php',
			'/components/com_contact/metadata.xml',
			'/components/com_contact/views/category/metadata.xml',
			'/components/com_contact/views/contact/metadata.xml',
			'/components/com_contact/views/featured/metadata.xml',
			'/components/com_content/metadata.xml',
			'/components/com_content/views/archive/metadata.xml',
			'/components/com_content/views/article/metadata.xml',
			'/components/com_content/views/categories/metadata.xml',
			'/components/com_content/views/category/metadata.xml',
			'/components/com_content/views/featured/metadata.xml',
			'/components/com_content/views/form/metadata.xml',
			'/components/com_finder/views/search/metadata.xml',
			'/components/com_mailto/views/mailto/metadata.xml',
			'/components/com_mailto/views/sent/metadata.xml',
			'/components/com_newsfeeds/metadata.xml',
			'/components/com_newsfeeds/views/category/metadata.xml',
			'/components/com_newsfeeds/views/newsfeed/metadata.xml',
			'/components/com_search/views/search/metadata.xml',
			'/components/com_tags/metadata.xml',
			'/components/com_tags/views/tag/metadata.xml',
			'/components/com_users/metadata.xml',
			'/components/com_users/views/login/metadata.xml',
			'/components/com_users/views/profile/metadata.xml',
			'/components/com_users/views/registration/metadata.xml',
			'/components/com_users/views/remind/metadata.xml',
			'/components/com_users/views/reset/metadata.xml',
			'/components/com_wrapper/metadata.xml',
			'/administrator/components/com_cache/layouts/joomla/searchtools/default/bar.php',
			'/administrator/components/com_cache/layouts/joomla/searchtools/default.php',
			'/administrator/components/com_languages/layouts/joomla/searchtools/default/bar.php',
			'/administrator/components/com_languages/layouts/joomla/searchtools/default.php',
			'/administrator/components/com_menus/layouts/joomla/searchtools/default/bar.php',
			'/administrator/components/com_menus/layouts/joomla/searchtools/default.php',
			'/administrator/components/com_modules/layouts/joomla/searchtools/default/bar.php',
			'/administrator/components/com_modules/layouts/joomla/searchtools/default.php',
			'/administrator/components/com_templates/layouts/joomla/searchtools/default/bar.php',
			'/administrator/components/com_templates/layouts/joomla/searchtools/default.php',
			// Joomla __DEPLOY_VERSION__
			'/administrator/modules/mod_menu/tmpl/default_enabled.php',
			'/administrator/modules/mod_menu/tmpl/default_disabled.php',
			'/administrator/templates/hathor/html/mod_menu/default_enabled.php',
		);

		// TODO There is an issue while deleting folders using the ftp mode
		$folders = array(
			'/administrator/components/com_admin/sql/updates/sqlsrv',
			'/media/com_finder/images/mime',
			'/media/com_finder/images',
			'/components/com_media/helpers',
			// Joomla 3.0
			'/administrator/components/com_contact/elements',
			'/administrator/components/com_content/elements',
			'/administrator/components/com_newsfeeds/elements',
			'/administrator/components/com_templates/views/prevuuw/tmpl',
			'/administrator/components/com_templates/views/prevuuw',
			'/libraries/cms/controller',
			'/libraries/cms/model',
			'/libraries/cms/view',
			'/libraries/joomla/application/cli',
			'/libraries/joomla/application/component',
			'/libraries/joomla/application/input',
			'/libraries/joomla/application/module',
			'/libraries/joomla/cache/storage/helpers',
			'/libraries/joomla/database/table',
			'/libraries/joomla/database/database',
			'/libraries/joomla/error',
			'/libraries/joomla/filesystem/archive',
			'/libraries/joomla/html/html',
			'/libraries/joomla/html/toolbar',
			'/libraries/joomla/html/toolbar/button',
			'/libraries/joomla/html/parameter',
			'/libraries/joomla/html/parameter/element',
			'/libraries/joomla/image/filters',
			'/libraries/joomla/log/loggers',
			// Joomla! 3.1
			'/libraries/joomla/form/rules',
			'/libraries/joomla/html/language/en-GB',
			'/libraries/joomla/html/language',
			'/libraries/joomla/html',
			'/libraries/joomla/installer/adapters',
			'/libraries/joomla/installer',
			'/libraries/joomla/pagination',
			'/libraries/legacy/html',
			'/libraries/legacy/menu',
			'/libraries/legacy/pathway',
			'/media/system/swf/',
			'/media/editors/tinymce/jscripts',
			// Joomla! 3.2
			'/libraries/joomla/plugin',
			'/libraries/legacy/component',
			'/libraries/legacy/module',
			'/administrator/components/com_weblinks/models/fields',
			'/plugins/user/joomla/postinstall',
			'/libraries/joomla/registry/format',
			'/libraries/joomla/registry',
			// Joomla! 3.3
			'/plugins/user/profile/fields',
			'/media/editors/tinymce/plugins/compat3x',
			// Joomla! 3.4
			'/administrator/components/com_tags/helpers/html',
			'/administrator/components/com_tags/models/fields',
			'/administrator/templates/hathor/html/com_finder/filter',
			'/administrator/templates/hathor/html/com_finder/statistics',
			'/libraries/compat/password/lib',
			'/libraries/compat/password',
			'/libraries/compat',
			'/libraries/framework/Joomla/Application/Cli/Output/Processor',
			'/libraries/framework/Joomla/Application/Cli/Output',
			'/libraries/framework/Joomla/Application/Cli',
			'/libraries/framework/Joomla/Application',
			'/libraries/framework/Joomla/DI/Exception',
			'/libraries/framework/Joomla/DI',
			'/libraries/framework/Joomla/Registry/Format',
			'/libraries/framework/Joomla/Registry',
			'/libraries/framework/Joomla',
			'/libraries/framework/Symfony/Component/Yaml/Exception',
			'/libraries/framework/Symfony/Component/Yaml',
			'/libraries/framework',
			'/libraries/phpmailer/language',
			'/libraries/phpmailer',
			'/media/editors/codemirror/css',
			'/media/editors/codemirror/js',
			'/media/com_banners',
			// Joomla! 3.4.1
			'/administrator/components/com_config/views',
			'/administrator/components/com_config/models/fields',
			'/administrator/components/com_config/models/forms',
			// Joomla! 3.4.2
			'/media/editors/codemirror/mode/smartymixed',
			// Joomla! 3.5
			'/libraries/vendor/symfony/yaml/Symfony/Component/Yaml/Exception',
			'/libraries/vendor/symfony/yaml/Symfony/Component/Yaml',
			'/libraries/vendor/symfony/yaml/Symfony/Component',
			'/libraries/vendor/symfony/yaml/Symfony',
			'/libraries/joomla/document/error',
			'/libraries/joomla/document/image',
			'/libraries/joomla/document/json',
			'/libraries/joomla/document/opensearch',
			'/libraries/joomla/document/raw',
			'/libraries/joomla/document/xml',
			'/administrator/components/com_media/models/forms',
			'/media/editors/codemirror/mode/kotlin',
			'/media/editors/tinymce/plugins/compat3x',
			'/plugins/editors/tinymce/fields',
			'/plugins/user/profile/fields',
			// Joomla 3.6
			'/libraries/simplepie/idn',
			'/libraries/simplepie',
			// Joomla! 3.6.3
			'/media/editors/codemirror/mode/jade',
			// Joomla! 3.7.0
			'/libraries/joomla/data',
			'/administrator/components/com_cache/layouts/joomla/searchtools/default',
			'/administrator/components/com_cache/layouts/joomla/searchtools',
			'/administrator/components/com_cache/layouts/joomla',
			'/administrator/components/com_cache/layouts',
			'/administrator/components/com_languages/layouts/joomla/searchtools/default',
			'/administrator/components/com_languages/layouts/joomla/searchtools',
			'/administrator/components/com_languages/layouts/joomla',
			'/administrator/components/com_languages/layouts',
			'/administrator/components/com_menus/layouts/joomla/searchtools/default',
			'/administrator/components/com_menus/layouts/joomla/searchtools',
			'/administrator/components/com_modules/layouts/joomla/searchtools/default',
			'/administrator/components/com_modules/layouts/joomla/searchtools',
			'/administrator/components/com_modules/layouts/joomla',
			'/administrator/components/com_templates/layouts/joomla/searchtools/default',
			'/administrator/components/com_templates/layouts/joomla/searchtools',
			'/administrator/components/com_templates/layouts/joomla',
			'/administrator/components/com_templates/layouts',
			// Joomla! __DEPLOY_VERSION__
			'/administrator/templates/hathor/html/mod_menu',
		);

		jimport('joomla.filesystem.file');

		foreach ($files as $file)
		{
			if (JFile::exists(JPATH_ROOT . $file) && !JFile::delete(JPATH_ROOT . $file))
			{
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file) . '<br />';
			}
		}

		jimport('joomla.filesystem.folder');

		foreach ($folders as $folder)
		{
			if (JFolder::exists(JPATH_ROOT . $folder) && !JFolder::delete(JPATH_ROOT . $folder))
			{
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder) . '<br />';
			}
		}

		/*
		 * Needed for updates post-3.4
		 * If com_weblinks doesn't exist then assume we can delete the weblinks package manifest (included in the update packages)
		 */
		if (!JFile::exists(JPATH_ROOT . '/administrator/components/com_weblinks/weblinks.php')
			&& JFile::exists(JPATH_ROOT . '/administrator/manifests/packages/pkg_weblinks.xml')
		)
		{
			JFile::delete(JPATH_ROOT . '/administrator/manifests/packages/pkg_weblinks.xml');
		}
	}

	/**
	 * Clears the RAD layer's table cache.
	 *
	 * The cache vastly improves performance but needs to be cleared every time you update the database schema.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function clearRadCache()
	{
		jimport('joomla.filesystem.file');

		if (JFile::exists(JPATH_ROOT . '/cache/fof/cache.php'))
		{
			JFile::delete(JPATH_ROOT . '/cache/fof/cache.php');
		}
	}

	/**
	 * Method to create assets for newly installed components
	 *
	 * @param   JInstaller $installer The class calling this method
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function updateAssets($installer)
	{
		// List all components added since 1.6
		$newComponents = array(
			'com_finder',
			'com_joomlaupdate',
			'com_tags',
			'com_contenthistory',
			'com_ajax',
			'com_postinstall',
			'com_fields',
			'com_associations',
		);

		foreach ($newComponents as $component)
		{
			/** @var JTableAsset $asset */
			$asset = JTable::getInstance('Asset');

			if ($asset->loadByName($component))
			{
				continue;
			}

			$asset->name      = $component;
			$asset->parent_id = 1;
			$asset->rules     = '{}';
			$asset->title     = $component;
			$asset->setLocation(1, 'last-child');

			if (!$asset->store())
			{
				// Install failed, roll back changes
				$installer->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $asset->stderr(true)));

				return false;
			}
		}

		return true;
	}

	/**
	 * If we migrated the session from the previous system, flush all the active sessions.
	 * Otherwise users will be logged in, but not able to do anything since they don't have
	 * a valid session
	 *
	 * @return  boolean
	 */
	public function flushSessions()
	{
		/**
		 * The session may have not been started yet (e.g. CLI-based Joomla! update scripts). Let's make sure we do
		 * have a valid session.
		 */
		$session = JFactory::getSession();

		/**
		 * Restarting the Session require a new login for the current user so lets check if we have an active session
		 * and only restart it if not.
		 * For B/C reasons we need to use getState as isActive is not available in 2.5
		 */
		if ($session->getState() !== 'active')
		{
			$session->restart();
		}

		// If $_SESSION['__default'] is no longer set we do not have a migrated session, therefore we can quit.
		if (!isset($_SESSION['__default']))
		{
			return true;
		}

		$db = JFactory::getDbo();

		try
		{
			switch ($db->getServerType())
			{
				// MySQL database, use TRUNCATE (faster, more resilient)
				case 'mysql':
					$db->truncateTable('#__session');
					break;

				// Non-MySQL databases, use a simple DELETE FROM query
				default:
					$query = $db->getQuery(true)
						->delete($db->qn('#__session'));
					$db->setQuery($query)->execute();
					break;
			}
		}
		catch (Exception $e)
		{
			echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br />';

			return false;
		}

		return true;
	}

	/**
	 * Converts the site's database tables to support UTF-8 Multibyte.
	 *
	 * @param   boolean $doDbFixMsg Flag if message to be shown to check db fix
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function convertTablesToUtf8mb4($doDbFixMsg = false)
	{
		$db = JFactory::getDbo();

		// This is only required for MySQL databases
		$serverType = $db->getServerType();

		if ($serverType != 'mysql')
		{
			return;
		}

		// Set required conversion status
		if ($db->hasUTF8mb4Support())
		{
			$converted = 2;
		}
		else
		{
			$converted = 1;
		}

		// Check conversion status in database
		$db->setQuery('SELECT ' . $db->quoteName('converted')
			. ' FROM ' . $db->quoteName('#__utf8_conversion')
		);

		try
		{
			$convertedDB = $db->loadResult();
		}
		catch (Exception $e)
		{
			// Render the error message from the Exception object
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			if ($doDbFixMsg)
			{
				// Show an error message telling to check database problems
				JFactory::getApplication()->enqueueMessage(JText::_('JLIB_DATABASE_ERROR_DATABASE_UPGRADE_FAILED'), 'error');
			}

			return;
		}

		// Nothing to do, saved conversion status from DB is equal to required
		if ($convertedDB == $converted)
		{
			return;
		}

		// Step 1: Drop indexes later to be added again with column lengths limitations at step 2
		$fileName1 = JPATH_ROOT . '/administrator/components/com_admin/sql/others/mysql/utf8mb4-conversion-01.sql';

		if (is_file($fileName1))
		{
			$fileContents1 = @file_get_contents($fileName1);
			$queries1      = $db->splitSql($fileContents1);

			if (!empty($queries1))
			{
				foreach ($queries1 as $query1)
				{
					try
					{
						$db->setQuery($query1)->execute();
					}
					catch (Exception $e)
					{
						// If the query fails we will go on. It just means the index to be dropped does not exist.
					}
				}
			}
		}

		// Step 2: Perform the index modifications and conversions
		$fileName2 = JPATH_ROOT . '/administrator/components/com_admin/sql/others/mysql/utf8mb4-conversion-02.sql';

		if (is_file($fileName2))
		{
			$fileContents2 = @file_get_contents($fileName2);
			$queries2      = $db->splitSql($fileContents2);

			if (!empty($queries2))
			{
				foreach ($queries2 as $query2)
				{
					try
					{
						$db->setQuery($db->convertUtf8mb4QueryToUtf8($query2))->execute();
					}
					catch (Exception $e)
					{
						$converted = 0;

						// Still render the error message from the Exception object
						JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
					}
				}
			}
		}

		if ($doDbFixMsg && $converted == 0)
		{
			// Show an error message telling to check database problems
			JFactory::getApplication()->enqueueMessage(JText::_('JLIB_DATABASE_ERROR_DATABASE_UPGRADE_FAILED'), 'error');
		}

		// Set flag in database if the update is done.
		$db->setQuery('UPDATE ' . $db->quoteName('#__utf8_conversion')
			. ' SET ' . $db->quoteName('converted') . ' = ' . $converted . ';')->execute();
	}

	/**
	 * This method clean the Joomla Cache using the method `clean` from the com_cache model
	 *
	 * @return  void
	 *
	 * @since   3.5.1
	 */
	private function cleanJoomlaCache()
	{
		JModelLegacy::addIncludePath(JPATH_ROOT . '/administrator/components/com_cache/models');
		$model = JModelLegacy::getInstance('cache', 'CacheModel');

		// Clean frontend cache
		$model->clean();

		// Clean admin cache
		$model->setState('client_id', 1);
		$model->clean();
	}
}
