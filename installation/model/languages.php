<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.updater.update');

/**
 * Language Installer model for the Joomla Core Installer.
 *
 * @since  3.1
 */
class InstallationModelLanguages extends JModelBase
{
	/**
	 * @var    object  Client object.
	 * @since  3.1
	 */
	protected $client;

	/**
	 * @var    array  Languages description.
	 * @since  3.1
	 */
	protected $data;

	/**
	 * @var    string  Language path.
	 * @since  3.1
	 */
	protected $path;

	/**
	 * @var    integer  Total number of languages installed.
	 * @since  3.1
	 */
	protected $langlist;

	/**
	 * @var    Admin Id, author of all generated content.
	 * @since  3.1
	 */
	protected $adminId;

	/**
	 * Constructor: Deletes the default installation config file and recreates it with the good config file.
	 *
	 * @since  3.1
	 */
	public function __construct()
	{
		// Overrides application config and set the configuration.php file so tokens and database works.
		JFactory::$config = null;
		JFactory::getConfig(JPATH_SITE . '/configuration.php');

		/*
		 * JFactory::getDbo() gets called during app bootup, and because of the "uniqueness" of the install app, the config doesn't get read
		 * correctly at that point.  So, we have to reset the factory database object here so that we can get a valid database configuration.
		 * The day we have proper dependency injection will be a glorious one.
		 */
		JFactory::$database = null;

		parent::__construct();
	}

	/**
	 * Generate a list of language choices to install in the Joomla CMS.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @since   3.1
	 */
	public function getItems()
	{
		// Get the extension_id of the en-GB package.
		$db        = JFactory::getDbo();
		$extQuery  = $db->getQuery(true);

		$extQuery->select($db->qn('extension_id'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('package'))
			->where($db->qn('element') . ' = ' . $db->q('pkg_en-GB'))
			->where($db->qn('client_id') . ' = 0');

		$db->setQuery($extQuery);

		$extId = (int) $db->loadResult();

		if ($extId)
		{
			$updater = JUpdater::getInstance();

			/*
			 * The following function call uses the extension_id of the en-GB package.
			 * In #__update_sites_extensions you should have this extension_id linked
			 * to the Accredited Translations Repo.
			 */
			$updater->findUpdates(array($extId), 0);

			$query = $db->getQuery(true);

			// Select the required fields from the updates table.
			$query->select($db->qn(array('update_id', 'name', 'element', 'version')))
				->from($db->qn('#__updates'))
				->order($db->qn('name'));

			$db->setQuery($query);
			$list = $db->loadObjectList();

			if (!$list || $list instanceof Exception)
			{
				$list = array();
			}
		}
		else
		{
			$list = array();
		}

		return $list;
	}

	/**
	 * Method that installs in Joomla! the selected languages in the Languages View of the installer.
	 *
	 * @param   array  $lids  List of the update_id value of the languages to install.
	 *
	 * @return  boolean True if successful
	 */
	public function install($lids)
	{
		$installerBase = new JInstaller;

		// Loop through every selected language.
		foreach ($lids as $id)
		{
			$installer = clone $installerBase;

			// Loads the update database object that represents the language.
			$language = JTable::getInstance('update');
			$language->load($id);

			// Get the URL to the XML manifest file of the selected language.
			$remote_manifest = $this->getLanguageManifest($id);

			if (!$remote_manifest)
			{
				// Could not find the url, the information in the update server may be corrupt.
				$message = JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_INSTALL_LANGUAGE', $language->name);
				$message .= ' ' . JText::_('INSTL_DEFAULTLANGUAGE_TRY_LATER');

				JFactory::getApplication()->enqueueMessage($message, 'warning');

				continue;
			}

			// Based on the language XML manifest get the URL of the package to download.
			$package_url = $this->getPackageUrl($remote_manifest);

			if (!$package_url)
			{
				// Could not find the URL, maybe the URL is wrong in the update server, or there is no internet access.
				$message = JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_INSTALL_LANGUAGE', $language->name);
				$message .= ' ' . JText::_('INSTL_DEFAULTLANGUAGE_TRY_LATER');

				JFactory::getApplication()->enqueueMessage($message, 'warning');

				continue;
			}

			// Download the package to the tmp folder.
			$package = $this->downloadPackage($package_url);

			// Install the package.
			if (!$installer->install($package['dir']))
			{
				// There was an error installing the package.
				$message = JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_INSTALL_LANGUAGE', $language->name);
				$message .= ' ' . JText::_('INSTL_DEFAULTLANGUAGE_TRY_LATER');

				JFactory::getApplication()->enqueueMessage($message, 'warning');

				continue;
			}

			// Cleanup the install files in tmp folder.
			if (!is_file($package['packagefile']))
			{
				$config                 = JFactory::getConfig();
				$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
			}

			JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

			// Delete the installed language from the list.
			$language->delete($id);
		}

		return true;
	}

	/**
	 * Gets the manifest file of a selected language from a the language list in an update server.
	 *
	 * @param   integer  $uid  The id of the language in the #__updates table.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	protected function getLanguageManifest($uid)
	{
		$instance = JTable::getInstance('update');
		$instance->load($uid);

		return trim($instance->detailsurl);
	}

	/**
	 * Finds the URL of the package to download.
	 *
	 * @param   string  $remote_manifest  URL to the manifest XML file of the remote package.
	 *
	 * @return  string|bool
	 *
	 * @since   3.1
	 */
	protected function getPackageUrl($remote_manifest)
	{
		$update = new JUpdate;
		$update->loadFromXml($remote_manifest);

		return trim($update->get('downloadurl', false)->_data);
	}

	/**
	 * Download a language package from a URL and unpack it in the tmp folder.
	 *
	 * @param   string  $url  URL of the package.
	 *
	 * @return  array|bool Package details or false on failure.
	 *
	 * @since   3.1
	 */
	protected function downloadPackage($url)
	{
		// Download the package from the given URL.
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'warning');

			return false;
		}

		$config   = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');

		// Unpack the downloaded package file.
		return JInstallerHelper::unpack($tmp_dest . '/' . $p_file);
	}

	/**
	 * Get Languages item data for the Administrator.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getInstalledlangsAdministrator()
	{
		return $this->getInstalledlangs('administrator');
	}

	/**
	 * Get Languages item data for the Frontend.
	 *
	 * @return  array  List of installed languages in the frontend application.
	 *
	 * @since   3.1
	 */
	public function getInstalledlangsFrontend()
	{
		return $this->getInstalledlangs('site');
	}

	/**
	 * Get Languages item data.
	 *
	 * @param   string  $cms_client  Name of the cms client.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getInstalledlangs($cms_client = 'administrator')
	{
		// Get information.
		$path     = $this->getPath();
		$client   = $this->getClient($cms_client);
		$langlist = $this->getLanguageList($client->id);

		// Compute all the languages.
		$data = array();

		foreach ($langlist as $lang)
		{
			$file          = $path . '/' . $lang . '/' . $lang . '.xml';
			$info          = JInstaller::parseXMLInstallFile($file);
			$row           = new stdClass;
			$row->language = $lang;

			if (!is_array($info))
			{
				continue;
			}

			foreach ($info as $key => $value)
			{
				$row->$key = $value;
			}

			// If current then set published.
			$params = JComponentHelper::getParams('com_languages');

			if ($params->get($client->name, 'en-GB') == $row->language)
			{
				$row->published = 1;
			}
			else
			{
				$row->published = 0;
			}

			$row->checked_out = 0;
			$data[]           = $row;
		}

		usort($data, array($this, 'compareLanguages'));

		return $data;
	}

	/**
	 * Get installed languages data.
	 *
	 * @param   integer  $client_id  The client ID to retrieve data for.
	 *
	 * @return  object  The language data.
	 *
	 * @since   3.1
	 */
	protected function getLanguageList($client_id = 1)
	{
		// Create a new db object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Select field element from the extensions table.
		$query->select($db->qn(array('element', 'name')))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('language'))
			->where($db->qn('state') . ' = 0')
			->where($db->qn('enabled') . ' = 1')
			->where($db->qn('client_id') . ' = ' . (int) $client_id);

		$db->setQuery($query);

		$this->langlist = $db->loadColumn();

		return $this->langlist;
	}

	/**
	 * Compare two languages in order to sort them.
	 *
	 * @param   object  $lang1  The first language.
	 * @param   object  $lang2  The second language.
	 *
	 * @return  integer
	 *
	 * @since   3.1
	 */
	protected function compareLanguages($lang1, $lang2)
	{
		return strcmp($lang1->name, $lang2->name);
	}

	/**
	 * Get the languages folder path.
	 *
	 * @return  string  The path to the languages folders.
	 *
	 * @since   3.1
	 */
	protected function getPath()
	{
		if ($this->path === null)
		{
			$client     = $this->getClient();
			$this->path = JLanguageHelper::getLanguagePath($client->path);
		}

		return $this->path;
	}

	/**
	 * Get the client object of Administrator or Frontend.
	 *
	 * @param   string  $client  Name of the client object.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	protected function getClient($client = 'administrator')
	{
		$this->client = JApplicationHelper::getClientInfo($client, true);

		return $this->client;
	}

	/**
	 * Set the default language.
	 *
	 * @param   string  $language    The language to be set as default.
	 * @param   string  $cms_client  The name of the CMS client.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function setDefault($language, $cms_client = 'administrator')
	{
		$client = $this->getClient($cms_client);

		$params = JComponentHelper::getParams('com_languages');
		$params->set($client->name, $language);

		$table = JTable::getInstance('extension');
		$id    = $table->find(array('element' => 'com_languages'));

		// Load
		if (!$table->load($id))
		{
			JFactory::getApplication()->enqueueMessage($table->getError(), 'warning');

			return false;
		}

		$table->params = (string) $params;

		// Pre-save checks.
		if (!$table->check())
		{
			JFactory::getApplication()->enqueueMessage($table->getError(), 'warning');

			return false;
		}

		// Save the changes.
		if (!$table->store())
		{
			JFactory::getApplication()->enqueueMessage($table->getError(), 'warning');

			return false;
		}

		return true;
	}

	/**
	 * Get the current setup options from the session.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getOptions()
	{
		return JFactory::getSession()->get('setup.options', array());
	}

	/**
	 * Get the model form.
	 *
	 * @param   string  $view  The view being processed.
	 *
	 * @return  mixed  JForm object on success, false on failure.
	 *
	 * @since   3.1
	 */
	public function getForm($view = null)
	{
		if (!$view)
		{
			$view = JFactory::getApplication()->input->getWord('view', 'defaultlanguage');
		}

		// Get the form.
		JForm::addFormPath(JPATH_COMPONENT . '/model/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/model/fields');
		JForm::addRulePath(JPATH_COMPONENT . '/model/rules');

		try
		{
			$form = JForm::getInstance('jform', $view, array('control' => 'jform'));
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		// Check the session for previously entered form data.
		$data = (array) $this->getOptions();

		// Bind the form data if present.
		if (!empty($data))
		{
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Enable a Joomla plugin.
	 *
	 * @param   string  $pluginName  The name of plugin.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function enablePlugin($pluginName)
	{
		// Create a new db object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->clear()
			->update($db->qn('#__extensions'))
			->set($db->qn('enabled') . ' = 1')
			->where($db->qn('name') . ' = ' . $db->q($pluginName))
			->where($db->qn('type') . ' = ' . $db->q('plugin'));

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			return false;
		}

		// Store language filter plugin parameters.
		if ($pluginName == 'plg_system_languagefilter')
		{
			$params = '{'
					. '"detect_browser":"0",'
					. '"automatic_change":"1",'
					. '"item_associations":"1",'
					. '"remove_default_prefix":"0",'
					. '"lang_cookie":"0",'
					. '"alternate_meta":"1"'
				. '}';
			$query
				->clear()
				->update($db->qn('#__extensions'))
				->set($db->qn('params') . ' = ' . $db->q($params))
				->where($db->qn('name') . ' = ' . $db->q('plg_system_languagefilter'))
				->where($db->qn('type') . ' = ' . $db->q('plugin'));

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Enable the Language Switcher Module.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function addModuleLanguageSwitcher()
	{
		JTable::addIncludePath(JPATH_LIBRARIES . '/legacy/table/');
		$tableModule = JTable::getInstance('Module', 'JTable');

		$moduleData  = array(
			'id'        => 0,
			'title'     => 'Language Switcher',
			'note'      => '',
			'content'   => '',
			'position'  => 'position-0',
			'module'    => 'mod_languages',
			'access'    => 1,
			'showtitle' => 0,
			'params'    =>
				'{"header_text":"","footer_text":"","dropdown":"0","image":"1","inline":"1","show_active":"1",'
				. '"full_name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0","cache_time":"900","cachemode":"itemid",'
				. '"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',
			'client_id' => 0,
			'language'  => '*',
			'published' => 1,
			'rules'     => array(),
		);

		// Bind the data.
		if (!$tableModule->bind($moduleData))
		{
			return false;
		}

		// Check the data.
		if (!$tableModule->check())
		{
			return false;
		}

		// Store the data.
		if (!$tableModule->store())
		{
			return false;
		}

		return $this->addModuleInModuleMenu((int) $tableModule->id);
	}

	/**
	 * Add a Module in Module menus.
	 *
	 * @param   integer  $moduleId  The Id of module.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function addModuleInModuleMenu($moduleId)
	{
		// Create a new db object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Add Module in Module menus.
		$query->clear()
			->insert($db->qn('#__modules_menu'))
			->columns(array($db->qn('moduleid'), $db->qn('menuid')))
			->values($moduleId . ', 0');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Publish the Installed Content Languages.
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	public function publishContentLanguages()
	{
		$app = JFactory::getApplication();

		// Publish the Content Languages.
		$tableLanguage = JTable::getInstance('Language');

		$siteLanguages = $this->getInstalledlangs('site');

		// For each content language.
		foreach ($siteLanguages as $siteLang)
		{
			if ($tableLanguage->load(array('lang_code' => $siteLang->language, 'published' => 0)) && !$tableLanguage->publish())
			{
				$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_CONTENT_LANGUAGE', $siteLang->name), 'warning');

				continue;
			}
		}

		return true;
	}

	/**
	 * Gets a unique language SEF string.
	 *
	 * This function checks other existing language with the same code, if they exist provides a unique SEF name.
	 * For instance: en-GB, en-US and en-AU will share the same SEF code by default: www.mywebsite.com/en/
	 * To avoid this conflict, this function creates a specific SEF in case of existing conflict:
	 * For example: www.mywebsite.com/en-au/
	 *
	 * @param   stdClass    $itemLanguage   Language Object.
	 * @param   stdClass[]  $siteLanguages  All Language Objects.
	 *
	 * @return  string
	 *
	 * @since   3.2
	 * @depreacted   4.0 Not used anymore.
	 */
	public function getSefString($itemLanguage, $siteLanguages)
	{
		$langs = explode('-', $itemLanguage->language);
		$prefixToFind = $langs[0];

		$numberPrefixesFound = 0;

		foreach ($siteLanguages as $siteLang)
		{
			$langs = explode('-', $siteLang->language);
			$lang  = $langs[0];

			if ($lang == $prefixToFind)
			{
				++$numberPrefixesFound;
			}
		}

		if ($numberPrefixesFound == 1)
		{
			return $prefixToFind;
		}

		return strtolower($itemLanguage->language);
	}

	/**
	 * Add a Content Language.
	 *
	 * @param   stdClass  $itemLanguage   Language Object.
	 * @param   string    $sefLangString  String to use for SEF so it doesn't conflict.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 * @depreacted   4.0 Not used anymore.
	 */
	public function addLanguage($itemLanguage, $sefLangString)
	{
		$tableLanguage = JTable::getInstance('Language');

		$flag = strtolower(str_replace('-', '_',  $itemLanguage->language));

		// Load the native language name.
		$installationLocalisedIni = new JLanguage($itemLanguage->language, false);
		$nativeLanguageName       = $installationLocalisedIni->_('INSTL_DEFAULTLANGUAGE_NATIVE_LANGUAGE_NAME');

		// If the local name do not exist in the translation file we use the international standard name.
		if ($nativeLanguageName == 'INSTL_DEFAULTLANGUAGE_NATIVE_LANGUAGE_NAME')
		{
			$nativeLanguageName = $itemLanguage->name;
		}

		$langData = array(
			'lang_id'      => 0,
			'lang_code'    => $itemLanguage->language,
			'title'        => $itemLanguage->name,
			'title_native' => $nativeLanguageName,
			'sef'          => $sefLangString,
			'image'        => $flag,
			'published'    => 1,
			'ordering'     => 0,
			'description'  => '',
			'metakey'      => '',
			'metadesc'     => '',
		);

		// Bind the data.
		if (!$tableLanguage->bind($langData))
		{
			return false;
		}

		// Check the data.
		if (!$tableLanguage->check())
		{
			return false;
		}

		// Store the data.
		if (!$tableLanguage->store())
		{
			return false;
		}

		// Reorder the data.
		if (!$tableLanguage->reorder())
		{
			return false;
		}

		return true;
	}

	/**
	 * Add Menu Group.
	 *
	 * @param   stdClass  $itemLanguage  Language Object.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function addMenuGroup($itemLanguage)
	{
		// Add menus.
		JLoader::registerPrefix('J', JPATH_PLATFORM . '/legacy');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables/');

		// Add Menu Group.
		$tableMenu = JTable::getInstance('Type', 'JTableMenu');

		$menuData = array(
			'id'          => 0,
			'menutype'    => 'mainmenu-' . strtolower($itemLanguage->language),
			'title'       => 'Main Menu (' . $itemLanguage->language . ')',
			'description' => 'The main menu for the site in language ' . $itemLanguage->name,
		);

		// Bind the data.
		if (!$tableMenu->bind($menuData))
		{
			return false;
		}

		// Check the data.
		if (!$tableMenu->check())
		{
			return false;
		}

		// Store the data.
		if (!$tableMenu->store())
		{
			return false;
		}

		return true;
	}

	/**
	 * Add Featured Menu Item.
	 *
	 * @param   stdClass  $itemLanguage  Language Object.
	 *
	 * @return  JTable|boolean Menu Item Object. False otherwise.
	 *
	 * @since   3.2
	 */
	public function addFeaturedMenuItem($itemLanguage)
	{
		// Add Menu Item.
		$tableItem = JTable::getInstance('Menu', 'MenusTable');

		$newlanguage = new JLanguage($itemLanguage->language, false);
		$newlanguage->load('com_languages', JPATH_ADMINISTRATOR, $itemLanguage->language, true);
		$title = $newlanguage->_('COM_LANGUAGES_HOMEPAGE');
		$alias = 'home_' . $itemLanguage->language;

		$menuItem = array(
			'title'        => $title,
			'alias'        => $alias,
			'menutype'     => 'mainmenu-' . strtolower($itemLanguage->language),
			'type'         => 'component',
			'link'         => 'index.php?option=com_content&view=featured',
			'component_id' => 22,
			'published'    => 1,
			'parent_id'    => 1,
			'level'        => 1,
			'home'         => 1,
			'params'       => '{"featured_categories":[""],"layout_type":"blog","num_leading_articles":"1",'
				. '"num_intro_articles":"3","num_columns":"3","num_links":"0","orderby_pri":"","orderby_sec":"front",'
				. '"order_date":"","multi_column_order":"1","show_pagination":"2","show_pagination_results":"1","show_noauth":"",'
				. '"article-allow_ratings":"","article-allow_comments":"","show_feed_link":"1","feed_summary":"",'
				. '"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"",'
				. '"show_parent_category":"","link_parent_category":"","show_author":"","show_create_date":"",'
				. '"show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_readmore":"",'
				. '"show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","menu-anchor_title":"",'
				. '"menu-anchor_css":"","menu_image":"","show_page_heading":1,"page_title":"","page_heading":"",'
				. '"pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',
			'language'     => $itemLanguage->language,
		);

		// Bind the data.
		if (!$tableItem->bind($menuItem))
		{
			return false;
		}

		$tableItem->setLocation($menuItem['parent_id'], 'last-child');

		// Check the data.
		if (!$tableItem->check())
		{
			return false;
		}

		// Store the data.
		if (!$tableItem->store())
		{
			return false;
		}

		// Rebuild the tree path.
		if (!$tableItem->rebuildPath($tableItem->id))
		{
			return false;
		}

		return $tableItem;
	}

	/**
	 * Add AllCategories Menu Item for new router.
	 *
	 * @param   stdClass  $itemLanguage  Language Object.
	 *
	 * @return  JTable|boolean Menu Item Object. False otherwise.
	 *
	 * @since   3.8.0
	 */

	public function addAllCategoriesMenuItem($itemLanguage)
	{
		// Add Menu Item.
		$tableItem = JTable::getInstance('Menu', 'MenusTable');

		$newlanguage = new JLanguage($itemLanguage->language, false);
		$newlanguage->load('joomla', JPATH_ADMINISTRATOR, $itemLanguage->language, true);
		$title = $newlanguage->_('JCATEGORIES');
		$alias = 'allcategories_' . $itemLanguage->language;

		$menuItem = array(
			'title'        => $title,
			'alias'        => $alias,
			'menutype'     => 'mainmenu-' . strtolower($itemLanguage->language),
			'type'         => 'component',
			'link'         => 'index.php?option=com_content&view=categories&id=0',
			'component_id' => 22,
			'published'    => 1,
			'parent_id'    => 1,
			'level'        => 1,
			'home'         => 0,
			'params'       => '{"show_base_description":"","categories_description":"","maxLevelcat":"",'
				. '"show_empty_categories_cat":"","show_subcat_desc_cat":"","show_cat_num_articles_cat":"",'
				. '"show_category_title":"","show_description":"","show_description_image":"","maxLevel":"",'
				. '"show_empty_categories":"","show_no_articles":"","show_subcat_desc":"","show_cat_num_articles":"",'
				. '"num_leading_articles":"","num_intro_articles":"","num_columns":"","num_links":"",'
				. '"multi_column_order":"","show_subcategory_content":"","orderby_pri":"","orderby_sec":"",'
				. '"order_date":"","show_pagination_limit":"","filter_field":"","show_headings":"",'
				. '"list_show_date":"","date_format":"","list_show_hits":"","list_show_author":"","display_num":"10",'
				. '"show_pagination":"","show_pagination_results":"","show_title":"","link_titles":"",'
				. '"show_intro":"","show_category":"","link_category":"","show_parent_category":"",'
				. '"link_parent_category":"","show_author":"","link_author":"","show_create_date":"",'
				. '"show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"",'
				. '"show_readmore":"","show_readmore_title":"","show_icons":"","show_print_icon":"",'
				. '"show_email_icon":"","show_hits":"","show_noauth":"","show_feed_link":"","feed_summary":"",'
				. '"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":1,'
				. '"menu_show":0,"page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"",'
				. '"menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',
			'language'     => $itemLanguage->language,
		);

		// Bind the data.
		if (!$tableItem->bind($menuItem))
		{
			return false;
		}

		$tableItem->setLocation($menuItem['parent_id'], 'last-child');

		// Check the data.
		if (!$tableItem->check())
		{
			return false;
		}

		// Store the data.
		if (!$tableItem->store())
		{
			return false;
		}

		// Rebuild the tree path.
		if (!$tableItem->rebuildPath($tableItem->id))
		{
			return false;
		}

		return $tableItem;
	}

	/**
	 * Add Module Menu.
	 *
	 * @param   stdClass  $itemLanguage  Language Object.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function addModuleMenu($itemLanguage)
	{
		$tableModule = JTable::getInstance('Module', 'JTable');
		$title = 'Main menu ' . $itemLanguage->language;

		$moduleData = array(
			'id'        => 0,
			'title'     => $title,
			'note'      => '',
			'content'   => '',
			'position'  => 'position-7',
			'module'    => 'mod_menu',
			'access'    => 1,
			'showtitle' => 1,
			'params'    => '{"menutype":"mainmenu-' . strtolower($itemLanguage->language)
				. '","startLevel":"0","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"",'
				. '"layout":"","moduleclass_sfx":"_menu","cache":"1","cache_time":"900","cachemode":"itemid"}',
			'client_id' => 0,
			'language'  => $itemLanguage->language,
			'published' => 1,
			'rules' => array(),
		);

		// Bind the data.
		if (!$tableModule->bind($moduleData))
		{
			return false;
		}

		// Check the data.
		if (!$tableModule->check())
		{
			return false;
		}

		// Store the data.
		if (!$tableModule->store())
		{
			return false;
		}

		return $this->addModuleInModuleMenu((int) $tableModule->id);
	}

	/**
	 * Disable Default Main Menu Module.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function disableModuleMainMenu()
	{
		// Create a new db object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Add Module in Module menus.
		$query
			->clear()
			->update($db->qn('#__modules'))
			->set($db->qn('published') . ' = 0')
			->where($db->qn('module') . ' = ' . $db->q('mod_menu'))
			->where($db->qn('language') . ' = ' . $db->q('*'))
			->where($db->qn('client_id') . ' = ' . $db->q('0'))
			->where($db->qn('position') . ' = ' . $db->q('position-7'));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Enable a module.
	 *
	 * @param   string  $moduleName  The Name of the module to activate.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function enableModule($moduleName)
	{
		// Create a new db object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->clear()
			->update($db->qn('#__modules'))
			->set($db->qn('published') . ' = 1')
			->where($db->qn('module') . ' = ' . $db->q($moduleName));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Method create a category for a specific language.
	 *
	 * @param   stdClass  $itemLanguage  Language Object.
	 *
	 * @return  JTable|boolean Category Object. False otherwise.
	 *
	 * @since   3.2
	 */
	public function addCategory($itemLanguage)
	{
		$newlanguage = new JLanguage($itemLanguage->language, false);
		$newlanguage->load('joomla', JPATH_ADMINISTRATOR, $itemLanguage->language, true);
		$title = $newlanguage->_('JCATEGORY');

		// Initialize a new category.
		$category = JTable::getInstance('Category');

		$data = array(
			'extension'       => 'com_content',
			'title'           => $title . ' (' . strtolower($itemLanguage->language) . ')',
			'description'     => '',
			'published'       => 1,
			'access'          => 1,
			'params'          => '{"target":"","image":""}',
			'metadesc'        => '',
			'metakey'         => '',
			'metadata'        => '{"page_title":"","author":"","robots":""}',
			'created_time'    => JFactory::getDate()->toSql(),
			'created_user_id' => (int) $this->getAdminId(),
			'language'        => $itemLanguage->language,
			'rules'           => array(),
			'parent_id'       => 1,
		);

		// Set the location in the tree.
		$category->setLocation(1, 'last-child');

		// Bind the data to the table
		if (!$category->bind($data))
		{
			return false;
		}

		// Check to make sure our data is valid.
		if (!$category->check())
		{
			return false;
		}

		// Store the category.
		if (!$category->store(true))
		{
			return false;
		}

		// Build the path for our category.
		$category->rebuildPath($category->id);

		return $category;
	}

	/**
	 * Create an article in a specific language.
	 *
	 * @param   stdClass  $itemLanguage  Language Object.
	 * @param   int       $categoryId    The id of the category where we want to add the article.
	 *
	 * @return  JTable|boolean Article Object. False otherwise.
	 *
	 * @since   3.2
	 */
	public function addArticle($itemLanguage, $categoryId)
	{
		$db = JFactory::getDbo();

		$newlanguage = new JLanguage($itemLanguage->language, false);
		$newlanguage->load('com_content.sys', JPATH_ADMINISTRATOR, $itemLanguage->language, true);
		$title       = $newlanguage->_('COM_CONTENT_CONTENT_TYPE_ARTICLE');
		$currentDate = JFactory::getDate()->toSql();

		// Initialize a new article.
		$article = JTable::getInstance('Content');

		$data = array(
			'title'            => $title . ' (' . strtolower($itemLanguage->language) . ')',
			'introtext'        => '<p>Lorem ipsum ad his scripta blandit partiendo, eum fastidii accumsan euripidis'
										. ' in, eum liber hendrerit an. Qui ut wisi vocibus suscipiantur, quo dicit'
										. ' ridens inciderint id. Quo mundi lobortis reformidans eu, legimus senserit'
										. 'definiebas an eos. Eu sit tincidunt incorrupte definitionem, vis mutat'
										. ' affert percipit cu, eirmod consectetuer signiferumque eu per. In usu latine'
										. 'equidem dolores. Quo no falli viris intellegam, ut fugit veritus placerat'
										. 'per. Ius id vidit volumus mandamus, vide veritus democritum te nec, ei eos'
										. 'debet libris consulatu.</p>',
			'images'           => json_encode(array()),
			'urls'             => json_encode(array()),
			'state'            => 1,
			'created'          => $currentDate,
			'created_by'       => (int) $this->getAdminId(),
			'created_by_alias' => 'Joomla',
			'publish_up'       => $currentDate,
			'publish_down'     => $db->getNullDate(),
			'version'          => 1,
			'catid'            => $categoryId,
			'metadata'         => '{"robots":"","author":"","rights":"","xreference":"","tags":null}',
			'metakey'          => '',
			'metadesc'         => '',
			'language'         => $itemLanguage->language,
			'featured'         => 1,
			'attribs'          => array(),
			'rules'            => array(),
		);

		// Bind the data to the table
		if (!$article->bind($data))
		{
			return false;
		}

		// Check to make sure our data is valid.
		if (!$article->check())
		{
			return false;
		}

		// Now store the category.
		if (!$article->store(true))
		{
			return false;
		}

		// Get the new item ID.
		$newId = $article->get('id');

		$query = $db->getQuery(true)
			->insert($db->qn('#__content_frontpage'))
			->values($newId . ', 0');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			return false;
		}

		return $article;
	}

	/**
	 * Add Blog Menu Item.
	 *
	 * @param   stdClass  $itemLanguage  Language Object.
	 * @param   int       $categoryId    The id of the category displayed by the blog.
	 *
	 * @return  JTable|boolean Menu Item Object. False otherwise.
	 *
	 * @since   3.8.0
	 */
	public function addBlogMenuItem($itemLanguage, $categoryId)
	{
		// Add Menu Item.
		$tableItem = JTable::getInstance('Menu', 'MenusTable');

		$newlanguage = new JLanguage($itemLanguage->language, false);
		$newlanguage->load('com_languages', JPATH_ADMINISTRATOR, $itemLanguage->language, true);
		$title = $newlanguage->_('COM_LANGUAGES_HOMEPAGE');
		$alias = 'home_' . $itemLanguage->language;

		$menuItem = array(
			'title'        => $title,
			'alias'        => $alias,
			'menutype'     => 'mainmenu-' . strtolower($itemLanguage->language),
			'type'         => 'component',
			'link'         => 'index.php?option=com_content&view=category&layout=blog&id=' . $categoryId,
			'component_id' => 22,
			'published'    => 1,
			'parent_id'    => 1,
			'level'        => 1,
			'home'         => 1,
			'params'       => '{"layout_type":"blog","show_category_heading_title_text":"","show_category_title":"",'
				. '"show_description":"","show_description_image":"","maxLevel":"","show_empty_categories":"",'
				. '"show_no_articles":"","show_subcat_desc":"","show_cat_num_articles":"","show_cat_tags":"",'
				. '"page_subheading":"","num_leading_articles":"1","num_intro_articles":"3","num_columns":"3",'
				. '"num_links":"0","multi_column_order":"1","show_subcategory_content":"","orderby_pri":"",'
				. '"orderby_sec":"front","order_date":"","show_pagination":"2","show_pagination_results":"1",'
				. '"show_featured":"","show_title":"","link_titles":"","show_intro":"","info_block_position":"",'
				. '"info_block_show_title":"","show_category":"","link_category":"","show_parent_category":"",'
				. '"link_parent_category":"","show_associations":"","show_author":"","link_author":"",'
				. '"show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"",'
				. '"show_vote":"","show_readmore":"","show_readmore_title":"","show_icons":"","show_print_icon":"",'
				. '"show_email_icon":"","show_hits":"","show_tags":"","show_noauth":"","show_feed_link":"1",'
				. '"feed_summary":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"",'
				. '"menu_image_css":"","menu_text":1,"menu_show":1,"page_title":"","show_page_heading":"1",'
				. '"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":""}',
			'language'     => $itemLanguage->language,
		);

		// Bind the data.
		if (!$tableItem->bind($menuItem))
		{
			return false;
		}

		$tableItem->setLocation($menuItem['parent_id'], 'last-child');

		// Check the data.
		if (!$tableItem->check())
		{
			return false;
		}

		// Store the data.
		if (!$tableItem->store())
		{
			return false;
		}

		// Rebuild the tree path.
		if (!$tableItem->rebuildPath($tableItem->id))
		{
			return false;
		}

		return $tableItem;
	}

	/**
	 * Create the language associations.
	 *
	 * @param   array  $groupedAssociations  Array of language associations for all items.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.6.1
	 */
	public function addAssociations($groupedAssociations)
	{
		$db = JFactory::getDbo();

		foreach ($groupedAssociations as $context => $associations)
		{
			$key   = md5(json_encode($associations));
			$query = $db->getQuery(true)
				->insert('#__associations');

			foreach ($associations as $language => $id)
			{
				$query->values(((int) $id) . ',' . $db->quote($context) . ',' . $db->quote($key));
			}

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieve the admin user id.
	 *
	 * @return  int|bool One Administrator ID.
	 *
	 * @since   3.2
	 */
	private function getAdminId()
	{
		if ($this->adminId)
		{
			// Return local cached admin ID.
			return $this->adminId;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Select the admin user ID
		$query
			->clear()
			->select($db->qn('u') . '.' . $db->qn('id'))
			->from($db->qn('#__users', 'u'))
			->join(
				'LEFT',
				$db->qn('#__user_usergroup_map', 'map')
				. ' ON ' . $db->qn('map') . '.' . $db->qn('user_id')
				. ' = ' . $db->qn('u') . '.' . $db->qn('id')
			)
			->join(
				'LEFT',
				$db->qn('#__usergroups', 'g')
				. ' ON ' . $db->qn('map') . '.' . $db->qn('group_id')
				. ' = ' . $db->qn('g') . '.' . $db->qn('id')
			)
			->where(
				$db->qn('g') . '.' . $db->qn('title')
				. ' = ' . $db->q('Super Users')
			);

		$db->setQuery($query);
		$id = $db->loadResult();

		if (!$id || $id instanceof Exception)
		{
			return false;
		}

		return $id;
	}
}
