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
class LevelEditPage extends AdminEditPage
{
	protected $waitForXpath = "//form[@id='level-form']";
	protected $url = 'administrator/index.php?option=com_users&view=level&layout=edit';

	/**
	 * Associative array of expected input fields for the Account Details and Basic Settings tabs
	 * Assigned User Acesss tab is omitted because that depends on the levels set up in the sample data
	 * @var unknown_type
	 */
	public $inputFields = array (
			array('label' => 'Level Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'header')
	);

	public function getAllInputFields($tabIds = array())
	{
		$return = array();
		$labels = $this->driver->findElements(By::xPath("//fieldset/div[@class='control-group']/div/label"));
		$tabId = 'header';
		foreach ($labels as $label)
		{
			if ($label->getAttribute('class') == 'checkbox')
			{
				continue;
			}
			$return[] = $this->getInputField($tabId, $label);
		}
		return $return;
	}

	public function getGroups()
	{
		$result = array();
		$elements = $this->driver->findElements(By::xPath("//input[@checked='checked']/../../label"));
		foreach ($elements as $el)
		{
			$result[] = str_replace(array('|','—'), '', $el->getText());
		}
		return $result;
	}

	public function setGroups(array $groupNames)
	{
		if (count($groupNames) == 0)
		{
			return;
		}
		// Uncheck any checked boxes
		$elements = $this->driver->findElements(By::xPath("//input[@checked='checked']"));
		foreach ($elements as $el)
		{
			$el->click();
		}
		foreach ($groupNames as $name)
		{
			$this->driver->findElement(By::xPath("//label[contains(., '$name')]"))->click();
		}
	}

}