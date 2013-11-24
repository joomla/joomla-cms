<?php

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

class WikihelpTest extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var GlobalConfigurationPage
	 */
	protected $testPage = null; // Page under test

	/**
	 *
	 * @var array of all menu links and corresponding page class names.
	 */

	public  $allMenuLinks = array(
		'Control Panel' 		=> array('ControlPanelPage', 'administrator/index.php', 'system'),
		'Global Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config', 'configuration'),
// 		'Banners Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_banners', 'configuration'),
// 		'Cache Manager Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_cache', 'configuration'),
// 		'Check-in Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_checkin', 'configuration'),
// 		'Contacts Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_contact', 'configuration'),
// 		'Articles Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_content', 'configuration'),
// 		'Smart Search Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_finder', 'configuration'),
// 		'Installation Manager Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_installer', 'configuration'),
// 		'Joomla! Update Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_joomlaupdate', 'configuration'),
// 		'Language Manager Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_languages', 'configuration'),
// 		'Media Manager Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_media', 'configuration'),
// 		'Menus Manager Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_menus', 'configuration'),
// 		'Messaging Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_messages', 'configuration'),
// 		'Module Manager Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_modules', 'configuration'),
// 		'Newsfeeds Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_newsfeeds', 'configuration'),
// 		'Plugin Manager Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_plugins', 'configuration'),
// 		'Post-installation Messages Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_postinstall', 'configuration'),
// 		'Redirect Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_redirect', 'configuration'),
// 		'Search Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_search', 'configuration'),
// 		'Tags Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_tags', 'configuration'),
// 		'Template Manager Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_templates', 'configuration'),
// 		'Users Manager Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_users', 'configuration'),
// 		'Weblinks Configuration'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_config&view=component&component=com_weblinks', 'configuration'),

// 		'Global Checkin' 		=> array('GenericAdminPage', 'administrator/index.php?option=com_checkin', 'system'),
// 		'Clear Cache' 			=> array('GenericAdminPage', 'administrator/index.php?option=com_cache', 'system'),
// 		'Purge Expired Cache' 	=> array('GenericAdminPage', 'administrator/index.php?option=com_cache&view=purge', 'system'),
// 		'System Information'	=> array('GenericAdminPage', 'administrator/index.php?option=com_admin&view=sysinfo', 'system'),
// 		'User Manager'			=> array('UserManagerPage', 'administrator/index.php?option=com_users&view=users', 'users'),
// 		'Add New User'			=> array('UserEditPage', 'administrator/index.php?option=com_users&task=user.add', 'users'),
// 		'Groups'				=> array('GroupManagerPage', 'administrator/index.php?option=com_users&view=groups', 'users'),
// 		'Add New Group'			=> array('GroupEditPage', 'administrator/index.php?option=com_users&task=group.add', 'users'),
// 		'Access Levels'			=> array('LevelManagerPage', 'administrator/index.php?option=com_users&view=levels', 'users'),
// 		'Add New Access Level'	=> array('LevelEditPage', 'administrator/index.php?option=com_users&task=level.add', 'users'),
// 		'User Notes'			=> array('UserNotesManagerPage', 'administrator/index.php?option=com_users&view=notes', 'users'),
// 		'Add User Note'			=> array('UserNotesEditPage', 'administrator/index.php?option=com_users&task=note.add', 'users'),
// 		'User Notes Categories'	=> array('CategoryManagerPage', 'administrator/index.php?option=com_categories&view=categories&extension=com_users', 'users'),
// 		'Add User Note Category'	=> array('CategoryEditPage', 'administrator/index.php?option=com_categories&task=category.add&extension=com_users', 'users'),
// 		'Mass Mail Users'		=> array('GenericAdminPage', 'administrator/index.php?option=com_users&view=mail', 'users'),
// 		'Menu Manager'			=> array('MenuManagerPage', 'administrator/index.php?option=com_menus&view=menus', 'menus'),
// 		'Add New Menu'			=> array('MenuEditPage', 'administrator/index.php?option=com_menus&view=menu&layout=edit', 'menus'),
// 		'Main Menu'				=> array('MenuItemsManagerPage', 'administrator/index.php?option=com_menus&view=items&menutype=mainmenu', 'menus'),
// 		'Main Menu Add New Menu Item'	=> array('MenuItemEditPage', 'administrator/index.php?option=com_menus&view=item&layout=edit&menutype=mainmenu', 'menus'),
// 		'Article Manager'		=> array('ArticleManagerPage', 'administrator/index.php?option=com_content', 'content'),
// 		'Add New Article'		=> array('ArticleEditPage', 'administrator/index.php?option=com_content&task=article.add', 'content'),
// 		'Category Manager'		=> array('CategoryManagerPage', 'administrator/index.php?option=com_categories&extension=com_content', 'content'),
// 		'Add New Category'		=> array('CategoryEditPage', 'administrator/index.php?option=com_categories&task=category.add&extension=com_content', 'content'),
// 		'Featured Articles'		=> array('GenericAdminPage', 'administrator/index.php?option=com_content&view=featured', 'content'),
// 		'Media Manager'			=> array('GenericAdminPage', 'administrator/index.php?option=com_media', 'content'),
// 		'Banners'				=> array('BannerManagerPage', 'administrator/index.php?option=com_banners', 'components'),
// 		'Add New Banner'		=> array('BannerEditPage', 'administrator/index.php?option=com_banners&view=banner&layout=edit', 'components'),
// 		'Banners Clients'		=> array('GenericAdminPage', 'administrator/index.php?option=com_banners&view=clients', 'components'),
// 		'Add New Banner Client'	=> array('GenericAdminEditPage', 'administrator/index.php?option=com_banners&view=client&layout=edit', 'components'),
// 		'Banners Tracks'		=> array('GenericAdminPage', 'administrator/index.php?option=com_banners&view=tracks', 'components'),
// 		'Contacts'				=> array('ContactManagerPage', 'administrator/index.php?option=com_contact', 'components'),
// 		'Add New Contact'		=> array('ContactEditPage', 'administrator/index.php?option=com_contact&task=contact.add', 'components'),
// 		'Joomla! Update'		=> array('GenericAdminPage', 'administrator/index.php?option=com_joomlaupdate', 'components'),
// 		'Messaging'				=> array('GenericAdminPage', 'administrator/index.php?option=com_messages', 'components'),
// 		'New Private Message'	=> array('GenericAdminPage', 'administrator/index.php?option=com_messages&task=message.add', 'components'),
// 		'Read Private Messages'	=> array('GenericAdminPage', 'administrator/index.php?option=com_messages', 'components'),
// 		'Newsfeeds'				=> array('NewsFeedManagerPage', 'administrator/index.php?option=com_newsfeeds', 'components'),
// 		'Add New Newsfeed'		=> array('NewsFeedEditPage', 'administrator/index.php?option=com_newsfeeds&task=newsfeed.add', 'components'),
// 		'Post-installation Messages'	=> array('PostinstallPage', 'administrator/index.php?option=com_postinstall', 'components'),
// 		'Redirect'				=> array('RedirectManagerPage', 'administrator/index.php?option=com_redirect', 'components'),
// 		'Add New Redirect'		=> array('RedirectEditPage', 'administrator/index.php?option=com_redirect&view=link&layout=edit', 'components'),
// 		'Search'				=> array('GenericAdminPage', 'administrator/index.php?option=com_search', 'components'),
// 		'Smart Search'			=> array('GenericAdminPage', 'administrator/index.php?option=com_finder', 'components'),
// 		'Tags'					=> array('TagManagerPage', 'administrator/index.php?option=com_tags', 'components'),
// 		'Add New Tag'			=> array('TagEditPage', 'administrator/index.php?option=com_tags&task=tag.add', 'components'),
// 		'Weblinks'				=> array('WeblinkManagerPage', 'administrator/index.php?option=com_weblinks', 'components'),
// 		'Add New Weblink'		=> array('WeblinkEditPage', 'administrator/index.php?option=com_weblinks&task=weblink.add', 'components'),
// 		'Extension Manager Install'		=> array('GenericAdminPage', 'administrator/index.php?option=com_installer', 'extensions'),
// 		'Extension Manager Update'		=> array('GenericAdminPage', 'administrator/index.php?option=com_installer&view=update', 'extensions'),
// 		'Extension Manager Manage'		=> array('GenericAdminPage', 'administrator/index.php?option=com_installer&view=manage', 'extensions'),
// 		'Extension Manager Discover'	=> array('GenericAdminPage', 'administrator/index.php?option=com_installer&view=discover', 'extensions'),
// 		'Extension Manager Database'	=> array('GenericAdminPage', 'administrator/index.php?option=com_installer&view=database', 'extensions'),
// 		'Extension Manager Warnings'	=> array('GenericAdminPage', 'administrator/index.php?option=com_installer&view=warnings', 'extensions'),
// 		'Extension Manager Install languages'	=> array('GenericAdminPage', 'administrator/index.php?option=com_installer&view=languages', 'extensions'),
// 		'Module Manager'		=> array('ModuleManagerPage', 'administrator/index.php?option=com_modules', 'extensions'),
// 		'Plugin Manager'		=> array('PluginManagerPage', 'administrator/index.php?option=com_plugins', 'extensions'),
// 		'Template Manager'		=> array('TemplateManagerPage', 'administrator/index.php?option=com_templates', 'extensions'),
// 		'Language Manager Installed'	=> array('LanguageManagerPage', 'administrator/index.php?option=com_languages', 'extensions'),
// 		'Language Manager Content'		=> array('LanguageManagerPage', 'administrator/index.php?option=com_languages&view=languages', 'extensions'),
// 		'Add New Langauge'		=> array('LanguageEditPage', 'administrator/index.php?option=com_languages&view=language&layout=edit', 'extensions'),
// 		'Language Manager Overrides'		=> array('LanguageManagerPage', 'administrator/index.php?option=com_languages&view=overrides', 'extensions'),
	);

	public function setUp()
	{
		parent::setUp();
		$this->testPage = $this->doAdminLogin();
	}

	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}


	/**
	 * @xtest
	 */
	public function takeScreenShotsAllMenuLinks()
	{
		$testPage = $this->testPage;
		$gcPage = $testPage->clickMenu('Global Configuration', 'GlobalConfigurationPage');
		$gcPage->setFieldValue('Default List Limit', '5');
		$gcPage->saveAndClose('ControlPanelPage');

		foreach ($this->allMenuLinks as $menuText => $linkArray)
		{
			if (strpos($linkArray[1], 'http') !== 0)
			{
				$testPage = $testPage->clickMenuByUrl($linkArray[1], $linkArray[0]);
				$name = $testPage->getHelpScreenshotName(null, $linkArray[2]);

				// process additional tabs if available
				if (method_exists($testPage, 'getTabIds'))
				{
					$tabs = $testPage->getTabIds();
					$limit = count($tabs);
					for ($i = 0; $i < $limit; $i++)
					{
						$testPage->selectTab($tabs[$i]);
						if ($i > 0)
						{
							$name = $testPage->getHelpScreenshotName($tabs[$i], $linkArray[2]);
						}
						$this->helpScreenshot($name, $this->cfg->baseURI . "/tests/system/tmp/basic-screens");
					}
				}
				else
				{
					$this->helpScreenshot($name, $this->cfg->baseURI . "/tests/system/tmp/basic-screens");
				}
			}
		}

	}

	/**
	 * @test
	 */
	public function takeScreenShotsMenuItemTypesAllLanguages()
	{
		/* @var $menuItemEditPage MenuItemEditPage */

		// First get a list of all menu item types (array like group => 'Articles', type => 'Archived Articles')
		$menuItemsManagerPage = $this->testPage->clickMenu('Main Menu', 'MenuItemsManagerPage');
		$menuItemsManagerPage->clickButton('toolbar-new');
		$menuItemEditPage = $this->getPageObject('MenuItemEditPage');
		$menuItemTypes = $menuItemEditPage->getMenuItemTypes();
		$menuItemEditPage->menuItemTypes = $menuItemTypes;
		$menuItemsManagerPage = $this->testPage->clickMenu('Main Menu', 'MenuItemsManagerPage');
		foreach ($menuItemTypes as $type)
		{
			$menuItemsManagerPage->clickButton('toolbar-new');
			$menuItemEditPage = $this->getPageObject('MenuItemEditPage');
			$menuItemEditPage->menuItemTypes = $menuItemTypes;
			$menuItemEditPage->setMenuItemType($type['type']);
			$menuItemEditPage->setFieldValue('Menu Title', $type['group'] . ' - ' . $type['type']);
			$menuItemEditPage->tabs = null;
			$menuItemEditPage->tabLabels = null;
			$tabs = $menuItemEditPage->getTabIds();
			$limit = count($tabs);
			for ($i = 0; $i < $limit; $i ++)
			{
				// Skip tabs that are common to all menu item types.
				if (in_array($tabs[$i], array('details', 'attrib-menu-options', 'attrib-page-options', 'attrib-metadata', 'modules')))
				{
					continue;
				}
				$menuItemEditPage->selectTab($tabs[$i]);
				if ($i > 0)
				{
					$name = $menuItemEditPage->getHelpScreenshotNameAllLanguages($tabs[$i], 'menus-menu-manager-new-menu-item');
				}
				else
				{
					$name = $menuItemEditPage->getHelpScreenshotNameAllLanguages(null, 'menus-menu-manager-new-menu-item');
				}
				$this->helpScreenshot($name, $this->cfg->baseURI . "/tests/system/tmp/menu-item-screens");
			}
			$menuItemEditPage->clickButton('Cancel');
			$menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		}

	}

	/**
	 * @xtest
	 */
	public function takeScreenShotsForModuleTypes()
	{
		/* @var $moduleEditPage ModuleEditPage */
		/* @var $moduleManagerPage ModuleManagerPage */

		// First get a list of all menu item types (array like group => 'Articles', type => 'Archived Articles')
		$moduleManagerPage = $this->testPage->clickMenu('Module Manager', 'ModuleManagerPage');
		foreach ($moduleManagerPage->moduleTypes as $type)
		{
			if ($type['client'] != 'site')
			{
				$moduleManagerPage->setFilter('filter_client_id', ucfirst($type['client']));
			}
			$moduleManagerPage->clickButton('toolbar-new');
			$el = $this->driver->waitForElementUntilIsPresent(By::xPath('//ul[@id=\'new-modules-list\']//a[contains(., "' . $type["name"] . '")]'));
			$coordinates = $el->getCoordinates();
			if ($coordinates['y'] > 600)
			{
				$this->driver->executeScript("window.scrollBy(0,400)");
			}
			$el->click();
			$moduleEditPage = $this->getPageObject('ModuleEditPage');
			$moduleEditPage->setFieldValues(array('Title' => $type['name']));

			$moduleEditPage->tabs = null;
			$moduleEditPage->tabLabels = null;
			$tabs = $moduleEditPage->getTabIds();
			$limit = count($tabs);
			for ($i = 0; $i < $limit; $i ++)
			{
					// Skip tabs that are common to all menu item types.
				if (in_array($tabs[$i], array('assignment','permissions','attrib-advanced')))
				{
					continue;
				}
				$moduleEditPage->selectTab($tabs[$i]);
				if ($i > 0)
				{
					$name = $moduleEditPage->getHelpScreenshotName($tabs[$i], 'modules ' . $type['client']);
				}
				else
				{
					$name = $moduleEditPage->getHelpScreenshotName(null, 'modules ' . $type['client']);
				}
				$this->helpScreenshot($name, $this->cfg->baseURI . "/tests/system/tmp/module-screens");
			}

			$moduleEditPage->clickButton('toolbar-cancel');
			$this->getPageObject('ModuleManagerPage');
	}

	}

	/**
	 * @xtest
	 */
	public function writeWikiFilesForBasicScreens()
	{
		$folder = 'tests/system/tmp/wiki-basic-files';
		$basePath = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
		$fullPath = $basePath . '/' . $folder;
		if (!file_exists($fullPath))
		{
			mkdir($fullPath);
		}

		foreach ($this->allMenuLinks as $menuText => $linkArray)
		{
			if (strpos($linkArray[1], 'http') !== 0)
			{
				$testPage = $this->testPage->clickMenuByUrl($linkArray[1], $linkArray[0]);
				if (method_exists($testPage, 'toWikiHelp'))
				{
					$text = $testPage->toWikiHelp($linkArray[2]);
					$fileName = $testPage->getHelpFileName($menuText);
					file_put_contents($fullPath . '/' . $fileName, $text);
				}
			}
		}

	}

	/**
	 * @xtest
	 */
	public function writeWikiFilesForMenuItemTypes()
	{
		$folder = 'tests/system/tmp/wiki-menu-item-files';
		$basePath = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
		$fullPath = $basePath . '/' . $folder;
		if (! file_exists($fullPath))
		{
			mkdir($fullPath);
		}

		/* @var $menuItemEditPage MenuItemEditPage */

		$menuItemsManagerPage = $this->testPage->clickMenu('Main Menu', 'MenuItemsManagerPage');
		$menuItemsManagerPage->clickButton('toolbar-new');
		$menuItemEditPage = $this->getPageObject('MenuItemEditPage');
		$menuItemTypes = $menuItemEditPage->menuItemTypes;
		$menuItemsManagerPage = $this->testPage->clickMenu('Main Menu', 'MenuItemsManagerPage');
		foreach ($menuItemTypes as $type)
		{
			$menuItemsManagerPage->clickButton('toolbar-new');
			$menuItemEditPage = $this->getPageObject('MenuItemEditPage');
			$menuItemEditPage->setMenuItemType($type['type']);
			$menuItemEditPage->tabs = null;
			$menuItemEditPage->tabLabels = null;
			$text = $menuItemEditPage->toWikiHelp('menus', array('header', 'details', 'attrib-menu-options', 'attrib-page-options', 'attrib-metadata', 'modules'));
			if ($text)
			{
				$fileName = $menuItemEditPage->getHelpFileName(trim('menu-item-type-' . $type['group'] . ' ' . $type['type']));
				file_put_contents($fullPath . '/' . $fileName, $text);
			}
			$menuItemEditPage->clickButton('Cancel');
			$menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		}
	}

	/**
	 * @xtest
	 */
	public function writeWikiFilesForModuleTypes()
	{
		$folder = 'tests/system/tmp/wiki-module-type-files';
		$basePath = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
		$fullPath = $basePath . '/' . $folder;
		if (! file_exists($fullPath))
		{
			mkdir($fullPath);
		}

		/* @var $moduleEditPage ModuleEditPage */
		/* @var $moduleManagerPage ModuleManagerPage */
		// First get a list of all module types
		$moduleManagerPage = $this->testPage->clickMenu('Module Manager', 'ModuleManagerPage');
		foreach ($moduleManagerPage->moduleTypes as $type)
		{
			if ($type['client'] != 'site')
			{
				$moduleManagerPage->setFilter('filter_client_id', ucfirst($type['client']));
			}
			$moduleManagerPage->clickButton('toolbar-new');
			$el = $this->driver->waitForElementUntilIsPresent(By::xPath('//ul[@id=\'new-modules-list\']//a[contains(., "' . $type["name"] . '")]'));
			$coordinates = $el->getCoordinates();
			if ($coordinates['y'] > 600)
			{
				$this->driver->executeScript("window.scrollBy(0,400)");
			}
			$el->click();
			$moduleEditPage = $this->getPageObject('ModuleEditPage');
			$moduleEditPage->setFieldValues(array('Title' => $type['name']));
			$moduleEditPage->tabs = null;
			$moduleEditPage->tabLabels = null;
			$excludedTabs = array('assignment','permissions','attrib-advanced');
			$excludedFields = array('Show Title', 'Position', 'Status', 'Start Publishing', 'Finish Publishing', 'Access', 'Ordering', 'Language', 'Note');
			$text = $moduleEditPage->toWikiHelp('modules-' . $type['client'], $excludedTabs, $excludedFields);
			if ($text)
			{
				$fileName = $moduleEditPage->getHelpFileName(trim('module-' . $type['client'] . '-' . $type['name']));
				file_put_contents($fullPath . '/' . $fileName, $text);
			}
			$moduleEditPage->clickButton('toolbar-cancel');
			$this->getPageObject('ModuleManagerPage');
		}

	}


}