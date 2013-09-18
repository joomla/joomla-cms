<?php

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
			'Extension Manager' => 'administrator/index.php?option=com_installer',
			'Language Manager' => 'administrator/index.php?option=com_languages',
			'Global Configuration' => 'administrator/index.php?option=com_config',
			'Template Manager' => 'administrator/index.php?option=com_templates',
			'Edit Profile' => 'administrator/index.php?option=com_admin&task=profile.edit&id=',
			'All extensions are up-to-date' => 'administrator/index.php?option=com_installer&view=update',
	);

	/**
	 * Gets information about all control panel icons on the screen
	 *
	 * @return  array of stdClass objects
	 */
	public function getControlPanelIcons()
	{
		$container = $this->driver->findElement(By::xPath("//div[contains(., 'Quick Icons')]/../div[@class='row-striped']"));
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

}