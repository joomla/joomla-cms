<?php

/**
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 ***************************************************************************************
 * Warning: Some modifications and improved were made by the Community Juuntos for
 * the latinamerican Project Jokte! CMS
 ***************************************************************************************
 * 
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.database.table');

/**
 * Script file of joomla CMS
 */
class joomlaInstallerScript
{
	/**
	 * method to preflight the update of Joomla!
	 *
	 * @param	string          $route      'update' or 'install'
	 * @param	JInstallerFile  $installer  The class calling this method
	 *
	 * @return void
	 */
	public function preflight($route, $installer)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('version_id');
		$query->from('#__schemas');
		$query->where('extension_id=700');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query = $db->getQuery(true);
			$query->insert('#__schemas');
			$query->set('extension_id=700, version_id='.$db->quote('1.1.8'));
			$db->setQuery($query);
			$db->query();
		}
		return true;
	}
	
	/**
	 * method to update Joomla!
	 *
	 * @param	JInstallerFile	$installer	The class calling this method
	 *
	 * @return void
	 */
	function update($installer)
	{
		$this->deleteUnexistingFiles();
		$this->updateManifestCaches();
		$this->updateDatabase();
	}
	
	protected function updateDatabase()
	{
		// Borrar JoomlaUpdate
		$table = JTable::getInstance('Extension');
		if ($table->load(array('element'=> 'com_jokteupdate', 'type'=>'component', 'client_id'=>1))) {
			if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_jokteupdate')) {
				// Delete this extension
				if (!$table->delete()) {
					echo $table->getError().'<br />';
					return;
				}
			}
			else {
				// Mark this extension as unprotected
				$table->protected = 0;
				if (!$table->store()) {
					echo $table->getError().'<br />';
					return;
				}
			}
		}
		
		$db = JFactory::getDbo();
		if (substr($db->name, 0, 5) == 'mysql')
		{
			$query = 'SHOW ENGINES';
			$db->setQuery($query);
			$results = $db->loadObjectList();
			if ($db->getErrorNum())
			{
				echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $db->getErrorNum(), $db->getErrorMsg()).'<br />';
				return;
			}
			foreach ($results as $result)
			{
				if ($result->Support=='DEFAULT')
				{
					$query = 'ALTER TABLE #__update_sites_extensions ENGINE = ' . $result->Engine;
					$db->setQuery($query);
					$db->query();
					if ($db->getErrorNum())
					{
						echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $db->getErrorNum(), $db->getErrorMsg()).'<br />';
						return;
					}
					break;
				}
			}
		}
	}

	protected function updateManifestCaches()
	{
		
		$extensions = array();
		// Components

		//`type`, `element`, `folder`, `client_id`
		$extensions[] = array('component', 'com_mailto', '', 0);
		$extensions[] = array('component', 'com_wrapper', '', 0);
		$extensions[] = array('component', 'com_admin', '', 1);
		$extensions[] = array('component', 'com_banners', '', 1);
		$extensions[] = array('component', 'com_cache', '', 1);
		$extensions[] = array('component', 'com_categories', '', 1);
		$extensions[] = array('component', 'com_checkin', '', 1);
		$extensions[] = array('component', 'com_contact', '', 1);
		$extensions[] = array('component', 'com_cpanel', '', 1);
		$extensions[] = array('component', 'com_installer', '', 1);
		$extensions[] = array('component', 'com_languages', '', 1);
		$extensions[] = array('component', 'com_login', '', 1);
		$extensions[] = array('component', 'com_media', '', 1);
		$extensions[] = array('component', 'com_menus', '', 1);
		$extensions[] = array('component', 'com_messages', '', 1);
		$extensions[] = array('component', 'com_modules', '', 1);
		$extensions[] = array('component', 'com_newsfeeds', '', 1);
		$extensions[] = array('component', 'com_plugins', '', 1);
		$extensions[] = array('component', 'com_search', '', 1);
		$extensions[] = array('component', 'com_templates', '', 1);
		$extensions[] = array('component', 'com_weblinks', '', 1);
		$extensions[] = array('component', 'com_content', '', 1);
		$extensions[] = array('component', 'com_config', '', 1);
		$extensions[] = array('component', 'com_redirect', '', 1);
		$extensions[] = array('component', 'com_users', '', 1);
		$extensions[] = array('component', 'com_jokteupdate', '', 1);

		// Libraries
		$extensions[] = array('library', 'phpmailer', '', 0);
		$extensions[] = array('library', 'simplepie', '', 0);
		$extensions[] = array('library', 'phputf8', '', 0);
		$extensions[] = array('library', 'joomla', '', 0);
		$extensions[] = array('library', 'cms', '', 0);

		// Modules site
		// Site
		$extensions[] = array('module', 'mod_articles_archive', '', 0);
		$extensions[] = array('module', 'mod_articles_latest', '', 0);
		$extensions[] = array('module', 'mod_articles_popular', '', 0);
		$extensions[] = array('module', 'mod_banners', '', 0);
		$extensions[] = array('module', 'mod_breadcrumbs', '', 0);
		$extensions[] = array('module', 'mod_custom', '', 0);
		$extensions[] = array('module', 'mod_feed', '', 0);
		$extensions[] = array('module', 'mod_footer', '', 0);
		$extensions[] = array('module', 'mod_login', '', 0);
		$extensions[] = array('module', 'mod_menu', '', 0);
		$extensions[] = array('module', 'mod_articles_news', '', 0);
		$extensions[] = array('module', 'mod_random_image', '', 0);
		$extensions[] = array('module', 'mod_related_items', '', 0);
		$extensions[] = array('module', 'mod_search', '', 0);
		$extensions[] = array('module', 'mod_stats', '', 0);
		$extensions[] = array('module', 'mod_syndicate', '', 0);
		$extensions[] = array('module', 'mod_users_latest', '', 0);
		$extensions[] = array('module', 'mod_weblinks', '', 0);
		$extensions[] = array('module', 'mod_whosonline', '', 0);
		$extensions[] = array('module', 'mod_wrapper', '', 0);
		$extensions[] = array('module', 'mod_articles_category', '', 0);
		$extensions[] = array('module', 'mod_articles_categories', '', 0);
		$extensions[] = array('module', 'mod_languages', '', 0);
		$extensions[] = array('module', 'mod_librehtml', '', 0);
		$extensions[] = array('module', 'mod_hilandojuuntos', '', 0);
		$extensions[] = array('module', 'mod_juuntosasamblea', '', 0);
		
		
		// Administrator
		$extensions[] = array('module', 'mod_custom', '', 1);
		$extensions[] = array('module', 'mod_feed', '', 1);
		$extensions[] = array('module', 'mod_latest', '', 1);
		$extensions[] = array('module', 'mod_logged', '', 1);
		$extensions[] = array('module', 'mod_login', '', 1);
		$extensions[] = array('module', 'mod_menu', '', 1);
		$extensions[] = array('module', 'mod_popular', '', 1);
		$extensions[] = array('module', 'mod_quickicon', '', 1);
		$extensions[] = array('module', 'mod_status', '', 1);
		$extensions[] = array('module', 'mod_submenu', '', 1);
		$extensions[] = array('module', 'mod_title', '', 1);
		$extensions[] = array('module', 'mod_toolbar', '', 1);
		$extensions[] = array('module', 'mod_multilangstatus', '', 1);
		$extensions[] = array('module', 'mod_hilandojuuntos', '', 1);

		// Plug-ins
		$extensions[] = array('plugin', 'gmail', 'authentication', 0);
		$extensions[] = array('plugin', 'joomla', 'authentication', 0);
		$extensions[] = array('plugin', 'ldap', 'authentication', 0);
		$extensions[] = array('plugin', 'emailcloak', 'content', 0);
		$extensions[] = array('plugin', 'geshi', 'content', 0);
		$extensions[] = array('plugin', 'loadmodule', 'content', 0);
		$extensions[] = array('plugin', 'pagebreak', 'content', 0);
		$extensions[] = array('plugin', 'pagenavigation', 'content', 0);
		$extensions[] = array('plugin', 'vote', 'content', 0);
		$extensions[] = array('plugin', 'codemirror', 'editors', 0);
		$extensions[] = array('plugin', 'none', 'editors', 0);
		$extensions[] = array('plugin', 'tinymce', 'editors', 0);
		$extensions[] = array('plugin', 'article', 'editors-xtd', 0);
		$extensions[] = array('plugin', 'image', 'editors-xtd', 0);
		$extensions[] = array('plugin', 'pagebreak', 'editors-xtd', 0);
		$extensions[] = array('plugin', 'readmore', 'editors-xtd', 0);
		$extensions[] = array('plugin', 'categories', 'search', 0);
		$extensions[] = array('plugin', 'contacts', 'search', 0);
		$extensions[] = array('plugin', 'content', 'search', 0);
		$extensions[] = array('plugin', 'newsfeeds', 'search', 0);
		$extensions[] = array('plugin', 'weblinks', 'search', 0);
		$extensions[] = array('plugin', 'languagefilter', 'system', 0);
		$extensions[] = array('plugin', 'p3p', 'system', 0);
		$extensions[] = array('plugin', 'cache', 'system', 0);
		$extensions[] = array('plugin', 'debug', 'system', 0);
		$extensions[] = array('plugin', 'log', 'system', 0);
		$extensions[] = array('plugin', 'redirect', 'system', 0);
		$extensions[] = array('plugin', 'remember', 'system', 0);
		$extensions[] = array('plugin', 'sef', 'system', 0);
		$extensions[] = array('plugin', 'logout', 'system', 0);
		$extensions[] = array('plugin', 'contactcreator', 'user', 0);
		$extensions[] = array('plugin', 'joomla', 'user', 0);
		$extensions[] = array('plugin', 'profile', 'user', 0);
		$extensions[] = array('plugin', 'joomla', 'extension', 0);
		$extensions[] = array('plugin', 'joomla', 'content', 0);
		$extensions[] = array('plugin', 'languagecode', 'system', 0);
		$extensions[] = array('plugin', 'joomlaupdate', 'quickicon', 0);
		$extensions[] = array('plugin', 'extensionupdate', 'quickicon', 0);
		$extensions[] = array('plugin', 'recaptcha', 'captcha', 0);

		// Templates
		$extensions[] = array('template', 'storkantu', '', 1);
		$extensions[] = array('template', 'jokteantu', '', 0);

		// Languages
		$extensions[] = array('language', 'es-LA', '', 0);
		$extensions[] = array('language', 'es-LA', '', 1);

		// Files
		$extensions[] = array('file', 'jokte', '', 0);

		// Packages
		$extensions[] = array('package', 'pkg_jokte', '', 0);

		// Attempt to refresh manifest caches
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__extensions');
		foreach ($extensions as $extension) {
			$query->where('type='.$db->quote($extension[0]).' AND element='.$db->quote($extension[1]).' AND folder='.$db->quote($extension[2]).' AND client_id='.$extension[3], 'OR');
		}
		$db->setQuery($query);
		$extensions = $db->loadObjectList();
		$installer = new JInstaller();
		// Check for a database error.
		if ($db->getErrorNum())
		{
			echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $db->getErrorNum(), $db->getErrorMsg()).'<br />';
			return;
		}
		foreach ($extensions as $extension) {
			if (!$installer->refreshManifestCache($extension->extension_id)) {
				echo JText::sprintf('FILES_JOOMLA_ERROR_MANIFEST', $extension->type, $extension->element, $extension->name, $extension->client_id).'<br />';
			}
		}
	}
	public function deleteUnexistingFiles()
	{
		$files = array(
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
			'/administrator/components/com_admin/sql/updates/mysql/2.5.0-2011-12-06.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.0-2011-12-16.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.0-2011-12-19.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.0-2011-12-20.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.0-2011-12-21-1.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.0-2011-12-21-2.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.0-2011-12-22.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.0-2011-12-23.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.0-2011-12-24.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.0-2012-01-10.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.0-2012-01-14.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.1-2012-01-26.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.2-2012-03-05.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.3-2012-03-13.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.4-2012-03-18.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.4-2012-03-19.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.5.sql',
			'/administrator/components/com_admin/sql/updates/mysql/2.5.6.sql',
			'/administrator/components/com_admin/sql/updates/sqlazure/2.5.2-2012-03-05.sql',
			'/administrator/components/com_admin/sql/updates/sqlazure/2.5.3-2012-03-13.sql',
			'/administrator/components/com_admin/sql/updates/sqlazure/2.5.4-2012-03-18.sql',
			'/administrator/components/com_admin/sql/updates/sqlazure/2.5.4-2012-03-19.sql',
			'/administrator/components/com_admin/sql/updates/sqlazure/2.5.5.sql',
			'/administrator/components/com_admin/sql/updates/sqlazure/2.5.6.sql',
			'/administrator/components/com_admin/sql/updates/sqlsrv/2.5.2-2012-03-05.sql',
			'/includes/version.php',
			'/administrator/languages/es-LA.tpl_bluestork.ini',
			'/administrator/languages/es-LA.tpl_bluestork.sys.ini',
			'/administrator/languages/es-LA.tpl_hathor.ini',
			'/administrator/languages/es-LA.tpl_hathor.sys.ini',
			'/administrator/languages/es-LA.plg_system_nnframework.ini',
			'/administrator/languages/es-LA.plg_system_nnframework.sys.ini',
		);

		// TODO There is an issue while deleting folders using the ftp mode
		$folders = array(
			'/administrator/components/com_joomlaupdate',
			'/administrator/language/pt-BR',
			'/language/pt-BR',
			
		);

		foreach ($files as $file) {
			if (JFile::exists(JPATH_ROOT . $file) && !JFile::delete(JPATH_ROOT . $file)) {
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file).'<br />';
			}
		}

		foreach ($folders as $folder) {
			if (JFolder::exists(JPATH_ROOT . $folder) && !JFolder::delete(JPATH_ROOT . $folder)) {
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder).'<br />';
			}
		}
	}
}
