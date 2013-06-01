<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Script file of Joomla CMS
 *
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 * @since       1.6.4
 */
class JoomlaInstallerScript
{
	/**
	 * Method to update Joomla!
	 *
	 * @param   JInstallerFile    $installer    The class calling this method
	 *
	 * @return void
	 */
	public function update($installer)
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
				echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $db->getErrorNum(), $db->getErrorMsg()) . '<br />';
				return;
			}
			foreach ($results as $result)
			{
				if ($result->Support == 'DEFAULT')
				{
					$query = 'ALTER TABLE #__update_sites_extensions ENGINE = ' . $result->Engine;
					$db->setQuery($query);
					$db->execute();
					if ($db->getErrorNum())
					{
						echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $db->getErrorNum(), $db->getErrorMsg()) . '<br />';
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
		$extensions[] = array('component', 'com_tags', '', 1);

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
		$extensions[] = array('module', 'mod_tags_popular', '', 0);
		$extensions[] = array('module', 'mod_tags_similar', '', 0);

		// Administrator
		$extensions[] = array('module', 'mod_custom', '', 1);
		$extensions[] = array('module', 'mod_feed', '', 1);
		$extensions[] = array('module', 'mod_latest', '', 1);
		$extensions[] = array('module', 'mod_logged', '', 1);
		$extensions[] = array('module', 'mod_login', '', 1);
		$extensions[] = array('module', 'mod_menu', '', 1);
		$extensions[] = array('module', 'mod_popular', '', 1);
		$extensions[] = array('module', 'mod_quickicon', '', 1);
		$extensions[] = array('module', 'mod_stats_admin', '', 1);
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
		$extensions[] = array('plugin', 'categories', 'finder', 0);
		$extensions[] = array('plugin', 'contacts', 'finder', 0);
		$extensions[] = array('plugin', 'content', 'finder', 0);
		$extensions[] = array('plugin', 'newsfeeds', 'finder', 0);
		$extensions[] = array('plugin', 'weblinks', 'finder', 0);
		$extensions[] = array('plugin', 'tags', 'finder', 0);

		// Templates
		$extensions[] = array('template', 'beez3', '', 0);
		$extensions[] = array('template', 'hathor', '', 1);
		$extensions[] = array('template', 'protostar', '', 0);
		$extensions[] = array('template', 'isis', '', 1);

		// Languages
		$extensions[] = array('language', 'en-GB', '', 0);
		$extensions[] = array('language', 'en-GB', '', 1);

		// Files
		$extensions[] = array('file', 'joomla', '', 0);

		// Packages
		// None in core at this time

		// Attempt to refresh manifest caches
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__extensions');
		foreach ($extensions as $extension)
		{
			$query->where('type=' . $db->quote($extension[0]) . ' AND element=' . $db->quote($extension[1]) . ' AND folder=' . $db->quote($extension[2]) . ' AND client_id=' . $extension[3], 'OR');
		}
		$db->setQuery($query);
		$extensions = $db->loadObjectList();
		$installer = new JInstaller;
		// Check for a database error.
		if ($db->getErrorNum())
		{
			echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $db->getErrorNum(), $db->getErrorMsg()) . '<br />';
			return;
		}
		foreach ($extensions as $extension)
		{
			if (!$installer->refreshManifestCache($extension->extension_id))
			{
				echo JText::sprintf('FILES_JOOMLA_ERROR_MANIFEST', $extension->type, $extension->element, $extension->name, $extension->client_id) . '<br />';
			}
		}
	}

	public function deleteUnexistingFiles()
	{
		$files = array(
			'/libraries/cms/cmsloader.php',
			'/libraries/joomla/form/fields/templatestyle.php',
			'/libraries/joomla/form/fields/user.php',
			'/libraries/joomla/form/fields/menu.php',
			'/libraries/joomla/form/fields/helpsite.php',
			'/administrator/components/com_admin/sql/updates/mysql/1.7.0.sql',
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
			'/administrator/components/com_installer/models/fields/type.php',
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
			'/media/system/css/mooRainbow.css',
			'/media/system/js/mooRainbow-uncompressed.js',
			'/media/system/js/mooRainbow.js',
			'/media/system/js/swf-uncompressed.js',
			'/media/system/js/swf.js',
			'/media/system/js/uploader-uncompressed.js',
			'/media/system/js/uploader.js',
			'/media/system/swf/index.html',
			'/media/system/swf/uploader.swf',
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
			'/administrator/components/com_installer/models/fields',
			'/administrator/components/com_installer/models/forms',
			'/administrator/components/com_modules/models/fields',
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
			'/media/system/swf/',
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
	}
}
