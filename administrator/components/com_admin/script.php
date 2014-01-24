<?php

/**
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
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
			$query->set('extension_id=700, version_id='.$db->quote('1.6.0-2011-01-10'));
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
		// TODO Remove this for 2.5
		if (!JTable::getInstance('Extension')->load(array('element'=> 'pkg_joomla', 'type'=>'package'))) {
			// Create the package pkg_joomla
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->insert('#__extensions');
			$query->columns(array($db->quoteName('name'), $db->quoteName('type'),
								$db->quoteName('element'), $db->quoteName('enabled'), $db->quoteName('access'),
								$db->quoteName('protected')));
			$query->values($db->quote('joomla'). ', '. $db->quote('package').', '.$db->quote('pkg_joomla') . ', 1, 1, 1');

			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum())
			{
				echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $db->getErrorNum(), $db->getErrorMsg()).'<br />';
				return;
			}
		}

		// TODO Remove this for 2.5
		$table = JTable::getInstance('Extension');
		if ($table->load(array('element'=> 'mod_online', 'type'=>'module', 'client_id'=>1))) {
			if (!file_exists(JPATH_ADMINISTRATOR . '/modules/mod_online')) {
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

		// TODO Remove this for 2.5
		$table = JTable::getInstance('Extension');
		if ($table->load(array('element'=> 'mod_unread', 'type'=>'module', 'client_id'=>1))) {
			if (!file_exists(JPATH_ADMINISTRATOR . '/modules/mod_unread')) {
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

		$extensions[] = array('template', 'atomic', '', 0);
		$extensions[] = array('template', 'bluestork', '', 1);
		$extensions[] = array('template', 'beez_20', '', 0);
		$extensions[] = array('template', 'hathor', '', 1);
		$extensions[] = array('template', 'beez5', '', 0);

		// Languages
		$extensions[] = array('language', 'en-GB', '', 0);
		$extensions[] = array('language', 'en-GB', '', 1);

		// Files
		$extensions[] = array('file', 'joomla', '', 0);

		// Packages
		$extensions[] = array('package', 'pkg_joomla', '', 0);

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
			'/includes/version.php',
			'/installation/sql/mysql/joomla_update_170to171.sql',
			'/installation/sql/mysql/joomla_update_172to173.sql',
			'/installation/sql/mysql/joomla_update_17ga.sql',
			'/libraries/cms/cmsloader.php',
			'/libraries/joomla/application/applicationexception.php',
			'/libraries/joomla/client/http.php',
			'/libraries/joomla/filter/filterinput.php',
			'/libraries/joomla/filter/filteroutput.php',
			'/libraries/joomla/form/fields/templatestyle.php',
			'/libraries/joomla/form/fields/user.php',
			'/libraries/joomla/form/fields/menu.php',
			'/libraries/joomla/form/fields/helpsite.php',
			'/libraries/joomla/form/formfield.php',
			'/libraries/joomla/form/formrule.php',
			'/libraries/joomla/utilities/garbagecron.txt',
			'/libraries/phpmailer/language/phpmailer.lang-en.php',
			'/media/system/css/modal_msie.css',
			'/media/system/images/modal/closebox.gif',
			'/administrator/components/com_admin/sql/updates/sqlsrv/2.5.2-2012-03-05.sql',
			'/administrator/components/com_admin/sql/updates/sqlsrv/2.5.3-2012-03-13.sql',
			'/administrator/components/com_admin/sql/updates/sqlsrv/index.html',
			'/administrator/components/com_users/controllers/config.php',
			'/administrator/language/en-GB/en-GB.plg_system_finder.ini',
			'/administrator/language/en-GB/en-GB.plg_system_finder.sys.ini',
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
			'/media/system/js/swf-uncompressed.js',
			'/media/system/js/swf.js',
			'/media/system/js/uploader-uncompressed.js',
			'/media/system/js/uploader.js',
			'/media/system/swf/index.html',
			'/media/system/swf/uploader.swf',
		);

		// TODO There is an issue while deleting folders using the ftp mode
		$folders = array(
			'/libraries/joomlacms',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/img',
			'/media/plg_highlight',
			'/media/mod_finder_status',
			'/administrator/components/com_admin/sql/updates/sqlsrv',
			'/media/com_finder/images/mime',
			'/media/com_finder/images',
			'/media/system/swf/',
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
