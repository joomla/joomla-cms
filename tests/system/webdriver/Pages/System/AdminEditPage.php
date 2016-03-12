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
abstract class AdminEditPage extends AdminPage
{

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $tabs = array();

	/**
	 * Array of tab labels for this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $tabLabels = array();

	/**
	 * Array of groups for this page. A group is a collapsable slider inside a tab.
	 * The format of this array is <tab id> => <array of group labels>.
	 * Note that each menu item type has its own options and its own groups.
	 * These are the common ones for almost all core menu item types.
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $groups = array();

	/**
	 * Array of expected id values for toolbar div elements
	 * @var array
	 */
	public $toolbar = array (
			'Save' => 'toolbar-apply',
			'Save & Close' => 'toolbar-save',
			'Save & New' => 'toolbar-save-new',
			'Cancel' => 'toolbar-cancel',
			'Help' => 'toolbar-help',
	);

	public $inputFields = array();

	public function __construct(Webdriver $driver, $test, $url = null)
	{
		$this->driver = $driver;
		$this->test = $test;
		$cfg = new SeleniumConfig();
		$this->cfg = $cfg; // save current configuration
		if ($url)
		{
			$this->driver->get($url);
		}
		$element = $driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath), 5);
		if (isset($this->url))
		{
			$test->assertTrue(strpos($driver->getCurrentPageUrl(), $this->url) >= 0, 'URL for page does not match expected value.');
		}
	}

	public function getAllInputFields($tabIds = array())
	{
		$return = array();
		if (count($tabIds) > 0)
		{
			// Get header fields
			$return = $this->getInputFieldsForHeader();
			foreach ($tabIds as $tabId)
			{

				$tabLink = $this->driver->findElement(By::xPath("//ul[@class='nav nav-tabs']//a[contains(@href, '" . $tabId . "')]"));
				$tabLink->click();

				// If there are accordian groups inside this tab, loop through each group
				if (isset($this->groups[$tabId]))
				{
					foreach ($this->groups[$tabId] as $groupLabel)
					{
						$this->expandAccordionGroup($groupLabel);
						$return = array_merge($return, $this->getInputFieldsForTab($tabId, $groupLabel));
					}
				}
				else
				{
					$return = array_merge($return, $this->getInputFieldsForTab($tabId));
				}

			}
		}
		else
		{
			$labels = $this->driver->findElements(By::xPath("//fieldset/div[@class='control-group']/div/label"));
			$tabId = 'header';
			foreach ($labels as $label)
			{
				$return[] = $this->getInputField($tabId, $label);
			}
		}
		return $return;
	}

	protected function getInputFieldsForTab($tabId, $groupLabel = null)
	{
		$labels = $this->driver->findElements(By::xPath("//div[@id='" . $tabId . "']//div/label"));
		return $this->getInputFieldObjects($labels, $tabId, $groupLabel);
	}

	protected function getInputFieldsForHeader()
	{
		$labels = $this->driver->findElements(By::xPath("//div[contains(@class, 'form-inline')]//div/label"));
		return $this->getInputFieldObjects($labels, 'header');
	}

	protected function getInputFieldObjects($labels, $tabId, $groupLabel = null)
	{
		$return = array();
		foreach ($labels as $label)
		{
			if ($object = $this->getInputField($tabId, $label))
			{
				if ($groupLabel)
				{
					$object->group = $groupLabel;
				}
				$return[] = $object;
			}
		}
		return $return;
	}

	protected function expandAccordionGroup($groupLabel)
	{
		$toggleSelector = "//a[@class='accordion-toggle'][contains(text(),'" . $groupLabel . "')]";
		$containerSelector = $toggleSelector . "/../../..//div[contains(@class, 'accordion-body')]";
		$toggleElement = $this->driver->findElement(By::xPath($toggleSelector));
		$containerElement = $this->driver->findElement(By::xPath($containerSelector));
		if ($containerElement->getAttribute('class') == 'accordion-body collapse')
		{
			try
			{
				$toggleElement->click();
			}
			catch (Exception $e)
			{
				$this->driver->executeScript("window.scrollBy(0,400)");
				$toggleElement->click();
				$this->driver->executeScript("window.scrollTo(0,0)");
			}

		}
		sleep(1);

	}

	protected function getInputField($tabId, $label)
	{
		$object = new stdClass();
		$object->tab = $tabId;
		$object->labelText = $label->getText();

		// Skip non-visible fields (affects permissions)
		if ($object->labelText == '')
		{
			return false;
		}
		$inputId = $label->getAttribute('for');
		$testInput = $this->driver->findElements(By::id($inputId));
		// If not found, check for user name field
		if (count($testInput) == 0)
		{
			// Check for user name
			$testInput = $this->driver->findElements(By::id($inputId . '_name'));
			if (count($testInput) == 1)
			{
				$inputId = $inputId . '_name';
			}
		}
		if (count($testInput) == 1)
		{
			$input = $testInput[0];
			$object->tag = $input->getTagName();
			$object->id = $inputId;
			$object->labelId = $label->getAttribute('id');
			$object->type = $input->getAttribute('type');
			$object->element = $input;
			return $object;
		}
		else
		{
			return false;
		}
	}

	public function getFieldValue($label)
	{
		if (($i = $this->getRowNumber($label)) !== false)
		{
			$fieldArray = $this->inputFields[$i];
			$fieldType = $fieldArray['type'];
			switch ($fieldType)
			{
				case 'select' :
					return $this->getSelectValues($fieldArray);
					break;

				case 'fieldset' :
					return $this->getRadioValues($fieldArray);
					break;

				case 'input' :
				case 'textarea' :
					return $this->getTextValues($fieldArray);
					break;

			}
		}

	}

	public function getOptionText(WebElement $el)
	{
		$optionText = array();
		$options = $el->findElements(By::tagName('li'));
		$i = 0;
		foreach ($options as $option)
		{
			$optionText[] = $option->getText();
			if ($i++ > 5)			{
				$optionText[] = '...';
				break;
			}
		}
		return $optionText;
	}

	protected function getRadioValues(array $values)
	{
		$this->selectTab($values['tab']);
		return $this->driver->findElement(By::xPath("//" . $values['type'] . "[@id='" . $values['id'] . "']/label[contains(@class, 'active')]"))->getText();
	}

	protected function getRowNumber($label)
	{
		$count = count($this->inputFields);
		for ($i = 0; $i < $count; $i++)
		{
			if (strtolower($this->inputFields[$i]['label']) == strtolower($label)) return $i;
		}
		return false;
	}

	protected function getSelectValues (array $values)
	{
		$this->selectTab($values['tab']);
		// Need to determine whether we are using Chosen JS for this select field
		$checkArray = $this->driver->findElements(By::xPath("//div[@id='" . $values['id'] . "_chzn']"));
		if (count($checkArray) == 1)
		{
			$container = $checkArray[0];
			return $this->driver->findElement(By::xPath("//div[@id='" . $values['id'] . "_chzn']/a/span"))->getText();
		}
		else
		{
			return $this->driver->findElement(By::xPath("//select[@id='jform_parent_id']/option[@selected='selected']"))->getText();
		}
	}

	public function getTabIds()
	{
		$tabs = $this->driver->findElements(By::xPath("//div[@class='tab-content']/div"));
		$return = array();
		foreach ($tabs as $tab)
		{
			$return[] = $tab->getAttribute('id');
		}
		return $return;
	}

	protected function getTextValues(array $values)
	{
		$this->selectTab($values['tab']);
		return $this->driver->findElement(By::id($values['id']))->getAttribute('value');
	}

	public function getToolbarElements()
	{
		return $this->driver->findElements(By::xPath("//div[@id='toolbar']/ul/li"));
	}

	public function getToolTip($tabText, $id)
	{
		$this->selectTab($tabText);
		$el = $this->driver->findElement(By::id($id));
		$test = $this->driver->executeScript("document.getElementById(arguments[0]).fireEvent('mouseenter');", array($id));
		sleep(1);
		$tip = $el->findElement(By::xPath("//div[@class='tip-text']"));
		$tipText = $tip->getText();
		return str_replace("\n", " ", $tipText);
	}

	public function printFieldArray($actualFields)
	{
		foreach ($actualFields as $field)
		{
			$field->labelText = (substr($field->labelText, -2) == ' *') ? substr($field->labelText, 0, -2) : $field->labelText;
			echo "array('label' => '" . $field->labelText . "', 'id' => '" . $field->id . "', 'type' => '" . $field->tag . "', 'tab' => '"
			. $field->tab . "'),\n";
		}
	}

	public function selectTab($label, $group = null)
	{
		if ($label == 'header')
		{
			return;
		}
		$this->driver->executeScript("window.scrollTo(0,0)");
		$el = $this->driver->findElement(By::xPath("//ul[@class='nav nav-tabs']//a[contains(@href, '" . strtolower($label) . "')]"));
		$el->click();
		sleep(1);
		$el->click();
		if ($group)
		{
			$this->expandAccordionGroup($group);
		}
	}

	public function setFieldValue($label, $value)
	{
		if (($i = $this->getRowNumber($label)) !== false)
		{
			$fieldArray = $this->inputFields[$i];
			$fieldArray['value'] = $value;
			$fieldType = $fieldArray['type'];
			$group = isset($fieldArray['group']) ? $fieldArray['group'] : null;
			$this->selectTab($fieldArray['tab'], $group);
			switch ($fieldType)
			{
				case 'select' :
					$this->setSelectValues($fieldArray);
					break;

				case 'fieldset' :
					$this->setRadioValues($fieldArray);
					break;

				case 'input' :
					$this->setTextValues($fieldArray);
					break;

				case 'textarea' :
					$this->setTextAreaValues($fieldArray);
					break;
			}
		}
	}

	public function setFieldValues(array $array)
	{
		foreach ($array as $label => $value)
		{
			$this->setFieldValue($label, $value);
		}
		return $this;
	}

	protected function setRadioValues(array $values)
	{
		$this->driver->findElement(By::xPath("//" . $values['type'] . "[@id='" . $values['id'] . "']/label[contains(text(), '" . $values['value'] . "')]"))->click();
	}

	protected function setSelectValues (array $values)
	{
		// Need to determine whether we are using Chosen JS for this select field
		$checkArray = $this->driver->findElements(By::xPath("//div[@id='" . $values['id'] . "_chzn']"));
		if (count($checkArray) == 1)
		{
			// Process a Chosen select field
			$container = $checkArray[0];

			$type = $container->getAttribute('class');
			if (strpos($type, 'chzn-container-single-nosearch') > 0)
			{
				$selectElement = $this->driver->findElement(By::xPath("//div[@id='" . $values['id'] . "_chzn']/a"));
				if (!$selectElement->isDisplayed())
				{
					$selectElement->getLocationOnScreenOnceScrolledIntoView();
				}
				$selectElement->click();

				// Click the last element in the list to make sure they are all in view
				$lastElement = $this->driver->findElement(By::xPath("//div[@id='" . $values['id'] . "_chzn']//ul[@class='chzn-results']/li[last()]"));
				if (!$lastElement->isDisplayed())
				{
					$lastElement->getLocationOnScreenOnceScrolledIntoView();
				}
				$this->driver->findElement(By::xPath("//div[@id='" . $values['id'] . "_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $values['value'] . "')]"))->click();
			}
			elseif (strpos($type, 'chzn-container-single') > 0)
			{
				$this->driver->findElement(By::xPath("//div[@id='" . $values['id'] . "_chzn']/a"))->click();
				$el = $this->driver->findElement(By::xPath("//div[@id='" . $values['id'] . "_chzn']//input"));
				$el->clear();
				$el->sendKeys($values['value']);
				$el->sendKeys(chr(9));
			}
		}
		else
		{
			// Process a standard Select field
			$this->driver->findElement(By::xPath("//select[@id='jform_parent_id']/option[contains(., '" . $values['value'] . "')]"))->click();
		}
	}

	protected function setTextValues(array $values)
	{
		$inputElement = $this->driver->findElement(By::id($values['id']));
		$inputElement->clear();
		$inputElement->sendKeys($values['value']);
	}

	protected function setTextAreaValues(array $values)
	{
		// Check whether this field uses a GUI editor
		// First see if we are inside a tab
		$tab = $this->driver->findElements(By::xPath("//div[@class='tab-pane active']"));
		if ((isset($tab) && is_array($tab) && count($tab) == 1))
		{
			$guiEditor = $tab[0]->findElements(By::xPath("//div[@class='tab-pane active']//a[contains(@onclick, 'mceToggleEditor')]"));
		}
		else
		{
			$guiEditor = $this->driver->findElements(By::xPath("//a[contains(@onclick, 'mceToggleEditor')]"));
		}
		if (isset($guiEditor) && is_array($guiEditor) && count($guiEditor) == 1 && $guiEditor[0]->isDisplayed())
		{
			$this->driver->executeScript("window.scrollBy(0,400)");
			$guiEditor[0]->click();
		}

		$inputElement = $this->driver->findElement(By::id($values['id']));
		$inputElement->clear();
		$inputElement->sendKeys($values['value']);

		if (isset($guiEditor) && is_array($guiEditor) && count($guiEditor) == 1 && $guiEditor[0]->isDisplayed())
		{
			$this->driver->executeScript("window.scrollBy(0,400)");
			$guiEditor[0]->click();
		}
		$this->driver->executeScript("window.scrollTo(0,0)");
	}

	/**
	 * Output help screen for the page.
	 */
	public function toWikiHelp()
	{
		$inputFields = $this->getAllInputFields($this->getTabIds());
		$tabs = $this->tabs;
		$helpText = array();
		foreach ($inputFields as $el)
		{
			$this->selectTab($el->tab);
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			if ($el->tag == 'fieldset')
			{
				$helpText[$el->tab][] = $this->toWikiHelpRadio($el);
			}
			elseif ($el->tag == 'select')
			{
				$helpText[$el->tab][] = $this->toWikiHelpSelect($el);
			}
			else
			{
				$helpText[$el->tab][] = "*'''" . $el->labelText . ":''' " . $this->getToolTip($el->tab, $el->id . '-lbl') . "\n";
			}
		}

		foreach ($tabs as $tab)
		{
			$tabText = $this->driver->findElement(By::xPath("//a[@href='#" . $tab . "']"))->getText();
			$result[] = '===' . $tabText . "===\n";
			if (isset($helpText[$tabText]))
			{
				$result = array_merge($result, $helpText[$tabText]);
			}
		}
		return implode("", $result);

	}

	/**
	 * Prepare wiki text for a radio button group
	 * Format is: *'''<label>:''' (<option1>/<option2/..) <tooltip text>
	 */
	public function toWikiHelpRadio(stdClass $object)
	{
		$optionText = array();
		$options = $object->element->findElements(By::tagName('label'));
		foreach ($options as $option)
		{
			$optionText[] = $option->getText();
		}
		return "*'''" . $object->labelText . ":''' (" . implode('/', $optionText) . "). " . $this->getToolTip($object->tab, $object->id . '-lbl'). "\n";
	}

	/**
	 * Prepare wiki text for an option group
	 * Format is: *'''<label>:''' (<option1>/<option2/..) <tooltip text>
	 */
	public function toWikiHelpSelect(stdClass $object)
	{
		$optionContainer = $this->driver->findElement(By::xPath("//div[@id='" . $object->id . "_chzn']"));
		$optionContainer->click();
		$optionList = $optionContainer->findElement(By::tagName('ul'));
		$optionText = $this->getOptionText($optionList);
		return "*'''" . $object->labelText . ":''' (" . implode('/', $optionText) . "). " . $this->getToolTip($object->tab, $object->id). "\n";
	}
}