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
class MenuEditPage extends AdminEditPage
{
	protected $waitForXpath = "//form[@id='item-form']";
	protected $url = 'administrator/index.php?option=com_menus&view=menu&layout=edit';

	/**
	 * Associative array of expected input fields for the Menu Manager: Add / Edit Menu
	 * @var array
	 */
	public $inputFields = array (
		array('label' => 'Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'header'),
		array('label' => 'Menu type', 'id' => 'jform_menutype', 'type' => 'input', 'tab' => 'header'),
		array('label' => 'Description', 'id' => 'jform_menudescription', 'type' => 'input', 'tab' => 'header'),
	);
}