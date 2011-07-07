<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
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
	}
	protected function updateManifestCaches()
	{
		// TODO Remove this for 1.8
		if (!JTable::getInstance('Extension')->load(array('element'=> 'pkg_joomla', 'type'=>'package'))) {
			// Create the package pkg_joomla
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->insert('#__extensions');
			$query->set('name='.$db->quote('joomla'));
			$query->set('type='.$db->quote('package'));
			$query->set('element='.$db->quote('pkg_joomla'));
			$query->set('enabled=1');
			$query->set('access=1');
			// !!! Mark the package as protected !!!
			$query->set('protected=1');
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum()) 
			{
				echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $db->getErrorNum(), $db->getErrorMsg()).'<br />';
				return;
			}
		}

		// TODO Remove this for 1.8
		$table = JTable::getInstance('Extension');
		if ($table->load(array('element'=> 'mod_online', 'type'=>'module', 'client_id'=>1))) {
			// Mark this extension as unprotected
			$table->protected = 0;
			if (!$table->store()) {
				echo $table->getError().'<br />';
				return;
			}
		}

		// TODO Remove this for 1.8
		$table = JTable::getInstance('Extension');
		if ($table->load(array('element'=> 'mod_unread', 'type'=>'module', 'client_id'=>1))) {
			// Mark this extension as unprotected
			$table->protected = 0;
			if (!$table->store()) {
				echo $table->getError().'<br />';
				return;
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
			$query->where('`type`='.$db->quote($extension[0]).' AND `element`='.$db->quote($extension[1]).' AND `folder`='.$db->quote($extension[2]).' AND `client_id`='.$extension[3], 'OR'); 
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
	protected function deleteUnexistingFiles()
	{
		$files = array(
			'/templates/atomic/css/blueprint/src/blueprintcss-0-9-1-cheatsheet-3-5-3-gjms.pdf',
			'/plugins/authentication/example/example.php',
			'/plugins/authentication/example/index.html',
			'/plugins/authentication/example/example.xml',
			'/plugins/user/example/example.php',
			'/plugins/user/example/index.html',
			'/plugins/user/example/example.xml',
			'/plugins/content/example/language/en-GB/en-GB.plg_content_example.ini',
			'/plugins/content/example/language/en-GB/en-GB.plg_content_example.sys.ini',
			'/plugins/content/example/language/en-GB/index.html',
			'/plugins/content/example/language/index.html',
			'/plugins/content/example/example.php',
			'/plugins/content/example/index.html',
			'/plugins/content/example/example.xml',
			'/plugins/extension/example/example.php',
			'/plugins/extension/example/index.html',
			'/plugins/extension/example/example.xml',
			'/administrator/manifests/packages/joomla.xml',
			'/administrator/templates/bluestork/css/rounded.css',
			'/administrator/templates/bluestork/css/norounded.css',
			'/administrator/templates/bluestork/images/j_corner_bl.png',
			'/administrator/templates/bluestork/images/j_header_right_rtl.png',
			'/administrator/templates/bluestork/images/j_crn_br_dark.png',
			'/administrator/templates/bluestork/images/j_crn_br_black.png',
			'/administrator/templates/bluestork/images/j_crn_tr_black.png',
			'/administrator/templates/bluestork/images/j_crn_bl_dark.png',
			'/administrator/templates/bluestork/images/j_crn_tr_med.png',
			'/administrator/templates/bluestork/images/j_crn_bl_light.png',
			'/administrator/templates/bluestork/images/j_header_right.png',
			'/administrator/templates/bluestork/images/j_crn_br_light.png',
			'/administrator/templates/bluestork/images/j_crn_tl_black.png',
			'/administrator/templates/bluestork/images/j_crn_bl_black.png',
			'/administrator/templates/bluestork/images/j_crn_tr_dark.png',
			'/administrator/templates/bluestork/images/j_crn_bl_med.png',
			'/administrator/templates/bluestork/images/j_header_left.png',
			'/administrator/templates/bluestork/images/j_crn_tl_med.png',
			'/administrator/templates/bluestork/images/j_crn_tl_dark.png',
			'/administrator/templates/bluestork/images/j_crn_br_med.png',
			'/administrator/templates/bluestork/images/j_crn_tl_light.png',
			'/administrator/templates/bluestork/images/j_crn_tr_light.png',
			'/administrator/templates/bluestork/images/j_corner_br.png',
			'/administrator/templates/bluestork/images/j_header_left_rtl.png',
			'/administrator/templates/hathor/html/com_modules/module/modal.php',
			'/administrator/templates/hathor/html/com_modules/module/edit_assignment.php',
			'/administrator/templates/hathor/html/com_modules/select/default.php',
			'/administrator/templates/hathor/html/com_modules/select/index.html',
			'/administrator/templates/hathor/html/com_menus/item/edit_modules.php',
			'/administrator/templates/hathor/html/com_menus/items/default_batch.php',
			'/administrator/templates/hathor/html/com_media/index.html',
			'/administrator/templates/hathor/html/com_media/media/default_navigation.php',
			'/administrator/templates/hathor/html/com_media/media/index.html',
			'/administrator/templates/hathor/html/com_categories/category/edit_metadata.php',
			'/administrator/templates/hathor/html/com_categories/categories/default_batch.php',
			'/administrator/templates/hathor/html/mod_popular/default.php',
			'/administrator/templates/hathor/html/mod_popular/index.html',
			'/administrator/templates/hathor/html/mod_status/default.php',
			'/administrator/templates/hathor/html/mod_status/index.html',
			'/administrator/templates/hathor/html/mod_latest/default.php',
			'/administrator/templates/hathor/html/mod_latest/index.html',
			'/administrator/components/com_menus/models/forms/item_options.xml',
			'/administrator/components/com_menus/models/fields/menutype.php',
			'/administrator/components/com_weblinks/helpers/html/weblink.php',
			'/administrator/components/com_weblinks/helpers/html/index.html',
			'/administrator/language/overrides/xx-XX.override.ini',
			'/administrator/help/helpsites-16.xml',
			'/administrator/help/en-GB/Components_Content_Categories_Edit.html',
			'/administrator/help/en-GB/Components_Weblinks_Categories_Edit.html',
			'/administrator/help/en-GB/Components_Newsfeeds_Categories_Edit.html',
			'/administrator/help/en-GB/Components_Banners_Categories_Edit.html',
			'/administrator/help/en-GB/Components_Contact_Categories_Edit.html',
			'/media/editors/codemirror/css/docs.css',
			'/media/editors/tinymce/jscripts/tiny_mce/tiny_mce_gzip.js',
			'/media/editors/tinymce/jscripts/tiny_mce/tiny_mce_gzip.php',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/pagebreak/css/content.css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/pagebreak/css/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/pagebreak/img/trans.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/pagebreak/img/pagebreak.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/pagebreak/img/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/langs/en.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/langs/en_dlg.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/langs/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/img/example.gif',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/img/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/editor_plugin.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/js/dialog.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/js/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/editor_plugin_src.js',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/index.html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/dialog.htm',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/media/css/content.css',
			'/media/mod_languages/images/ta.gif',
			'/libraries/joomla/config.php',
			'/libraries/joomla/import.php',
			'/libraries/joomla/version.php',
		);

		$folders = array(
			'/plugins/authentication/example',
			'/plugins/user/example',
			'/plugins/content/example',
			'/plugins/content/example/language',
			'/plugins/content/example/language/en-GB',
			'/plugins/extension/example',
			'/administrator/templates/hathor/html/com_modules/select',
			'/administrator/templates/hathor/html/com_media',
			'/administrator/templates/hathor/html/com_media/media',
			'/administrator/templates/hathor/html/mod_popular',
			'/administrator/templates/hathor/html/mod_status',
			'/administrator/templates/hathor/html/mod_latest',
			'/administrator/components/com_weblinks/helpers/html',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/pagebreak/css',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/pagebreak/img',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/langs',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/img',
			'/media/editors/tinymce/jscripts/tiny_mce/plugins/example/js'
		);

		foreach ($files as $file) {
			if (JFile::exists(JPATH_ROOT . $file)) {
				JFile::delete(JPATH_ROOT . $file);
			}
			else {
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file).'<br />';
			}
		}

		foreach ($folders as $folder) {
			if (JFolder::exists(JPATH_ROOT . $folder)) {
				JFolder::delete(JPATH_ROOT . $folder);
			}
			else {
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder).'<br />';
			}
		}
	}
}

