<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end control panel screen.
 *
 */
class ControlPanelPage extends AdminPage
{
	protected $waitForXpath = "//h1[contains(., 'Control Panel')]";
	protected $url = 'administrator/index.php';

	/**
	 *
	 * @var  array  Array of Control Panel icon text and links
	 */
	public $expectedIconArray = array(
			'Add New Article' => 'administrator/index.php?option=com_content&task=article.add',
			'Article Manager' => 'administrator/index.php?option=com_content',
			'Category Manager' => 'administrator/index.php?option=com_categories&extension=com_content',
			'Media Manager' => 'administrator/index.php?option=com_media',
			'Menu Manager' => 'administrator/index.php?option=com_menus',
			'User Manager' => 'administrator/index.php?option=com_users',
			'Module Manager' => 'administrator/index.php?option=com_modules',
			'User Manager' => 'administrator/index.php?option=com_users',
			'Global Configuration' => 'administrator/index.php?option=com_config',
			'Template Manager' => 'administrator/index.php?option=com_templates',
			'Language Manager' => 'administrator/index.php?option=com_languages',
			'Install Extensions' => 'administrator/index.php?option=com_installer',
	);

	/**
	 * Gets information about all control panel icons on the screen
	 *
	 * @return  array of stdClass objects
	 */
	public function getControlPanelIcons()
	{
		$container = $this->driver->findElement(By::xPath("//div[contains(@class, 'quick-icons')]"));
		$elements = $container->findElements(By::tagName('a'));
		$return = array();
		foreach ($elements as $element)
		{
			$object = new stdClass();
			$object->text = $element->getText();
			$object->href = $element->getAttribute('href');
			$return[] = $object;
		}
		return $return;
	}

	/**
	 * Gets the titles of modules in sliders
	 *
	 * @return  array of stdClass objects
	 */
	public function getModuleTitles()
	{
		$container = $this->driver->findElement(By::Id('panel-sliders'));
		$elements = $container->findElements(By::tagName('h3'));
		$return = array();
		foreach ($elements as $element)
		{
			$object = new stdClass();
			$object->text = $element->getText();

		}
	}

	/**
	 * Clears post-installation messages by navigating to that screen and back
	 *
	 * @return  null
	 */
	public function clearInstallMessages()
	{
		$installPage = $this->clickMenu('Post-installation Messages', 'PostinstallPage');
		$installPage->clearInstallMessages();
		$cpPage = $installPage->clickMenu('Control Panel', 'ControlPanelPage');
	}

}
