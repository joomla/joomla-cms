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
abstract class AdminEditPage extends AdminPage
{

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $tabs = null;

	/**
	 * Array of tab labels for this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $tabLabels = null;

	/**
	 * Name for header fields
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $headingLabel = 'Heading';

	/**
	 * Array of groups for this page. A group is a collapsable slider inside a tab.
	 * The format of this array is <tab id> => <array of group labels>.
	 * Note that each menu item type has its own options and its own groups.
	 * These are the common ones for almost all core menu item types.
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $groups = null;

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

	/**
	 * Array of associative arrays of input fields for this screen
	 * 	label => label for the field (string)
	 * 	id => id of the input field
	 * 	type => type of field (select, input, textarea, fieldset)
	 * 	tab => the id of the tab field (or 'heading' if no tab).
	 *
	 * @var array
	 */
	public $inputFields = null;

	/**
	 * Array of permissions for this component options screen.
	 *
	 * @var array
	 */
	public $permissions = null;

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

	/**
	 *
	 * @param array $tabIds
	 *        	array of tab ids
	 *
	 * @return array of std objects for each field
	 */
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

	public function formatImageElement($imageName)
	{
		return "[[Image:$imageName|670px|none]]\n";
	}

	/**
	 *
	 * @param string  $tabId id of tab that contains the field
	 * @param string  	$label label of the field
	 * @return mixed    false if no field found, otherwise std object with these fields:
	 *                       tag = element tag name
	 *                       id = element id attribute
	 *                       labelId = id attribute of label for this field
	 *                       type = type attribute of element
	 *                       element = WebElement object for the element
	 */
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

	public function getHelpFileName($componentName)
	{
		$name = 'help-' . $this->version . '-' . $componentName . '.txt';
		return strtolower(str_replace(array('\'', ' / ', ' - ', ' ', '/', ':'), array('', '-', '-','-', '', ''), $name));
	}

	public function getHelpScreenshotName($tabId = null, $prefix = null)
	{
		$screenName = $this->driver->findElement(By::className('page-title'))->getText();
		if ($prefix)
		{
			$screenName = $prefix . '-' . $screenName;
		}
		if ($tabId && ($label = $this->getTabLabel($tabId)))
		{
			$name = 'help-' . $this->version . '-' . $screenName . '-' . $label . '.png';
		}
		else
		{
			$name = 'help-' . $this->version . '-' . $screenName . '.png';
		}
		return strtolower(str_replace(array('\'', ' / ', ' - ', ' ', '/', ':'), array('', '-', '-','-', '', ''), $name));
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
			$class = $option->getAttribute('class');
			if ($text = $option->getText() && strpos($class, 'active-result') !== false)
			{
				$optionText[] = "''" . trim($option->getText(), ' -') . "''";
			}

			if ($i++ > 5)			{
				$optionText[] = '...';
				break;
			}
		}
		return $optionText;
	}

	public function getPermissions($groupId, $permissionsId)
	{
		if (!isset($this->permissions))
		{
			$result = array();
			$this->selectTab($permissionsId);
			$this->driver->findElement(By::xPath("//a[@href='#permission-" . $groupId . "']"))->click();
			$elements = $this->driver->findElements(By::xPath("//div[@id='permission-" . $groupId . "']//label[@class='hasTooltip']"));
			foreach ($elements as $element)
			{
				$labelId = $element->getAttribute('for');
				$permission = str_ireplace(array('jform_rules_', '_' . $groupId), '', $labelId);
				$result[] = $permission;
			}
			$this->permissions = $result;
		}
		return $this->permissions;
	}

	public function getPermissionInputFields($groupId, $permissionsId)
	{
		$permissions = $this->getPermissions($groupId, $permissionsId);
		$this->driver->findElement(By::xPath("//a[@href='#permission-" . $groupId . "']"))->click();
		foreach ($this->permissions as $permission)
		{
			$id = 'jform_rules_' . $permission . '_' . $groupId;
			$label = $this->driver->findElement(By::xPath("//label[@for='" . $id . "']"));
			$input = $this->driver->findElement(By::id($id));
			$tip = $label->findElement(By::xPath("//label[@class='hasTooltip'][@for='" . $id . "']"));
			$tipText = $tip->getAttribute('data-original-title');
			$object = new stdClass();
// 			$object->tab = $this->driver->findElement(By::id($permissionsId))->getText();
			$object->labelText = $label->getText();
			$object->tipText = $tipText;
			$object->tag = $input->getTagName();
			$object->id = $id;
			$object->type = $input->getAttribute('type');

			$object->element = $input;
			$result[] = $object;
		}
		return $result;
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

	public function getTabDescription($tabId)
	{
		$this->selectTab($tabId);
		$tabTextElementArray = $this->driver->findElements(By::xPath("//div[contains(@class,'tab-pane active')]//p"));

		if (count($tabTextElementArray) > 0 && ($tabDescription = $tabTextElementArray[0]->getText()))
		{
			return $tabDescription;
		}
		else
		{
			return false;
		}
	}

	public function getTabIds()
	{
		if (!isset($this->tabs))
		{
			$tabs = $this->driver->findElements(By::xPath("//div[contains(@class,'tab-content')]/div[contains(@class,'tab-pane')][not(contains(@id,'permission-'))]"));
			$return = array();
			foreach ($tabs as $tab)
			{
				$return[] = $tab->getAttribute('id');
			}
			$this->tabs = $return;
		}
		return $this->tabs;
	}

	public function getTabLabels()
	{
		if (!isset($this->tabLabels))
		{
			$tabs = $this->driver->findElements(By::xPath("//ul[contains(@class, 'nav-tabs')]/li/a[not(contains(@href,'#permission-'))]"));
			$return = array();
			foreach ($tabs as $tab)
			{
				if ($text = $tab->getText())
				{
					$return[] = $tab->getText('');
				}
			}
			$this->tabLabels = $return;
		}
		return $this->tabLabels;
	}

	public function getTabLabel($tabId)
	{
		$result = false;
		if ($tabId == 'header')
		{
			$result = $this->headingLabel;
		}
		$tabIds = $this->getTabIds();
		$index = count($tabIds);
		$tabLabels = $this->getTabLabels();
		for ($i = 0; $i < $index; $i++)
		{
			if ($tabIds[$i] == $tabId)
			{
				$result = $tabLabels[$i];
			}
		}
		return $result;
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
		$result = false;
		$elmentArray = $this->driver->findElements(By::id($id));
		if (count($elmentArray) == 1)
		{
			$tipText = $elmentArray[0]->getAttribute('data-original-title');
			if ($tipText)
			{
				$result = str_replace("\n", " ", $tipText);
			}
		}
		return $result;
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

	protected function removeLabel($label, $string)
	{
		return str_ireplace(array('<strong>' . $label . '</strong>', '<strong> ' . $label . '</strong>','<br />'), array('','',''), $string);
	}

	public function selectTab($label, $group = null)
	{
		if ($label == 'header')
		{
			return;
		}
		$this->driver->executeScript("window.scrollTo(0,0)");
		$el = $this->driver->findElement(By::xPath("//ul[@class='nav nav-tabs']//a[contains(translate(@href, '" . strtoupper($label) . "', '" . strtolower($label) . "'), '" . strtolower($label) . "')]"));
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
	public function toWikiHelp($linkArray)
	{
		$tabs = $this->getTabIds();
		$inputFields = $this->getAllInputFields($tabs);

		$helpText = array();
		foreach ($inputFields as $el)
		{
			$this->selectTab($el->tab);
			$el->tabLabel = $this->getTabLabel($el->tab);
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			if ($el->tag == 'fieldset')
			{
				 $elHelpText = $this->toWikiHelpRadio($el);
			}
			elseif ($el->tag == 'select')
			{
				$elHelpText = $this->toWikiHelpSelect($el);
			}
			else
			{
				$elHelpText = $this->toWikiHelpInput($el);
			}

			if ($elHelpText)
			{
				$helpText[$el->tabLabel][] = $elHelpText;
			}
		}

		$permissionsTextArray = $this->toWikiHelpPermissions();
		if (is_array($permissionsTextArray))
		{
			$helpText[$permissionsTextArray[1]] = $permissionsTextArray[0];
		}

		$tabCount = count($tabs);
		$result = array();
		for ($i = 0; $i < $tabCount; $i++)
		{
			$tab = $tabs[$i];
			$tabText = $this->driver->findElement(By::xPath("//a[@href='#" . $tab . "']"))->getText();
			$result[] = '===' . $tabText . "===\n";

			// Don't do screenshot for first tab, since this is in the main screenshot
			if ($i > 0)
			{
				$result[] = $this->formatImageElement($this->getHelpScreenshotName($tab, $linkArray[2]));
			}

			// Get any description for tab
			if ($tabDescription = $this->getTabDescription($tab))
			{
				$result[] = $tabDescription . "\n";
			}

			if (isset($helpText[$tabText]))
			{
				$result = array_merge($result, $helpText[$tabText]);
			}
		}

		$screenshot = array("==Screenshot==\n");
		$screenshotName = $this->getHelpScreenshotName(null, $linkArray[2]);
		$screenshot[] = $this->formatImageElement($screenshotName);
		$screenshot[] = "==Details==\n";

		if (isset($helpText[$this->headingLabel]))
		{
			$screenshot = array_merge($screenshot, $helpText[$this->headingLabel]);
		}
		$result = array_merge($screenshot, $result);

		return implode("", $result);

	}

	/**
	 * Prepare wiki text for an input element
	 * Format is: *'''<label>:''' <tooltip text>
	 */
	public function toWikiHelpInput(stdClass $el)
	{
		$result = false;
		if ($toolTipRaw = $this->getToolTip($el->tab, $el->id . '-lbl'))
		{
			$toolTip = $this->removeLabel($el->labelText, $toolTipRaw);
			$result = "*'''" . $el->labelText . ":''' " . $toolTip . "\n";
		}
		return $result;
	}

	/**
	 * Prepare wiki text for permissions tab
	 */
	public function toWikiHelpPermissions()
	{
		$result = false;
		$elArray = $this->driver->findElements(By::xPath("//ul//a[@href='#page-permissions' or @href='#permissions' or @href='#rules']"));
		if (count($elArray) == 1)
		{
			$el = $elArray[0];
			$el->click();
			$permissionsText = $el->getText();
			$permissionsId = $this->driver->findElement(By::xPath("//div[@class = 'tab-pane active']"))->getAttribute('id');
			$objects = $this->getPermissionInputFields('1', $permissionsId);
			$helpText = array();
			foreach ($objects as $object)
			{
				$listElement = str_replace('.', '_', $object->id);
				$optionContainer = $this->driver->findElement(By::xPath("//div[@id='" . $listElement . "_chzn']"));
				$optionContainer->findElement(By::tagName('a'))->click();
				$optionList = $optionContainer->findElement(By::tagName('ul'));
				$optionText = $this->getOptionText($optionList);
				$toolTip = $object->element->getAttribute('title') . ". " . $object->tipText;
				$helpText[] = "*'''" . $object->labelText . ":''' (" . implode('/', $optionText) . "). " . $toolTip . "\n";
				$optionContainer->findElement(By::tagName('a'))->click();
			}
			$result = array(
				$helpText,
				$permissionsText
			);
		}
		return $result;
	}


	/**
	 * Prepare wiki text for a radio button group
	 * Format is: *'''<label>:''' (<option1>/<option2/..) <tooltip text>
	 */
	public function toWikiHelpRadio(stdClass $object)
	{
		$toolTip = $this->removeLabel($object->labelText, $this->getToolTip($object->tab, $object->id . '-lbl'));
		$optionText = array();
		$options = $object->element->findElements(By::tagName('label'));
		foreach ($options as $option)
		{
			$optionText[] = "''" . $option->getText() . "''";
		}
		return "*'''" . $object->labelText . ":''' (" . implode('/', $optionText) . "). " . $toolTip . "\n";
	}

	/**
	 * Prepare wiki text for an option group
	 * Format is: *'''<label>:''' (<option1>/<option2/..) <tooltip text>
	 */
	public function toWikiHelpSelect(stdClass $object)
	{
		$toolTip = $this->removeLabel($object->labelText, $this->getToolTip($object->tab, $object->labelId));
		$optionContainer = $this->driver->findElement(By::xPath("//div[@id='" . $object->id . "_chzn']"));
		$optionContainer->click();
		$optionList = $optionContainer->findElement(By::tagName('ul'));
		$optionText = $this->getOptionText($optionList);
		$optionContainer->click();
		if (count($optionText) > 0)
		{
			$result = "*'''" . $object->labelText . ":''' (" . implode('/', $optionText) . "). " . $toolTip . "\n";
		}
		else
		{
			$result = "*'''" . $object->labelText . ":''' " . $toolTip . "\n";
		}
		return $result;
	}
}
