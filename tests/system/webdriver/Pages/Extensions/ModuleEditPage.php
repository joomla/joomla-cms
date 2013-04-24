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
class ModuleEditPage extends AdminEditPage
{
	protected $waitForXpath =  "//form[@id='module-form']";
	protected $url = 'administrator/index.php?option=com_users&view=module&layout=edit';

	/**
	 * Array of
	 * @var array expected id values for tab div elements
	 */
	public $tabs = array('details', 'options', 'assignment');

	public $tabLabels = array('Details', 'Options', 'Menu Assignment');

	/**
	 * Associative array of expected input fields for the Account Details and Basic Settings tabs
	 * Assigned User Groups tab is omitted because that depends on the groups set up in the sample data
	 * @var unknown_type
	 */
	public $inputFields = array (
			array('label' => 'Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Show Title', 'id' => 'jform_showtitle', 'type' => 'fieldset', 'tab' => 'details'),
			array('label' => 'Position', 'id' => 'jform_position', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Status', 'id' => 'jform_published', 'type' => 'fieldset', 'tab' => 'details'),
			array('label' => 'Access', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Ordering', 'id' => 'jform_ordering', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Start Publishing', 'id' => 'jform_publish_up', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Finish Publishing', 'id' => 'jform_publish_down', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Language', 'id' => 'jform_language', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Note', 'id' => 'jform_note', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Parent Category', 'id' => 'jform_params_parent', 'type' => 'select', 'tab' => 'options'),
			array('label' => 'Category Descriptions', 'id' => 'jform_params_show_description', 'type' => 'fieldset', 'tab' => 'options'),
			array('label' => 'Show Subcategories', 'id' => 'jform_params_show_children', 'type' => 'fieldset', 'tab' => 'options'),
			array('label' => '# First Subcategories', 'id' => 'jform_params_count', 'type' => 'select', 'tab' => 'options'),
			array('label' => 'Maximum Level Depth', 'id' => 'jform_params_maxlevel', 'type' => 'select', 'tab' => 'options'),
			array('label' => 'Alternative Layout', 'id' => 'jform_params_layout', 'type' => 'select', 'tab' => 'options'),
			array('label' => 'Heading style', 'id' => 'jform_params_item_heading', 'type' => 'select', 'tab' => 'options'),
			array('label' => 'Module Class Suffix', 'id' => 'jform_params_moduleclass_sfx', 'type' => 'textarea', 'tab' => 'options'),
			array('label' => 'Caching', 'id' => 'jform_params_owncache', 'type' => 'select', 'tab' => 'options'),
			array('label' => 'Cache Time', 'id' => 'jform_params_cache_time', 'type' => 'input', 'tab' => 'options'),
			array('label' => 'Module Tag', 'id' => 'jform_params_module_tag', 'type' => 'select', 'tab' => 'options'),
			array('label' => 'Bootstrap Size', 'id' => 'jform_params_bootstrap_size', 'type' => 'select', 'tab' => 'options'),
			array('label' => 'Header Tag', 'id' => 'jform_params_header_tag', 'type' => 'select', 'tab' => 'options'),
			array('label' => 'Header Class', 'id' => 'jform_params_header_class', 'type' => 'input', 'tab' => 'options'),
			array('label' => 'Module Style', 'id' => 'jform_params_style', 'type' => 'select', 'tab' => 'options'),
			array('label' => 'Module Assignment', 'id' => 'jform_menus', 'type' => 'div', 'tab' => 'assignment'),
			);

	/**
	 * Checks for Type and calls special method for this field.
	 * Otherwise, just calls parent::getFieldValue()
	 *
	 * @see AdminEditPage::getFieldValue()
	 */
	public function getFieldValue($label)
	{
		if ($label == 'Type')
		{
			return $this->getModuleType();
		}
		else
		{
			return parent::getFieldValue($label);
		}
	}

	protected function getModuleType()
	{
		$elements = $this->driver->findElements(By::xPath("//span[@class = 'label']"));
		if (count($elements >= 2))
		{
			return $elements[1]->getText();
		}
		else
		{
			return false;
		}
	}

}