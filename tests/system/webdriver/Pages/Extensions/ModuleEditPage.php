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
 * @since  joomla 3.0
 */
class ModuleEditPage extends AdminEditPage
{
	protected $waitForXpath = "//form[@id='module-form']";

	protected $url = 'administrator/index.php?option=com_users&view=module&layout=edit';

	/**
	 * Array of
	 * @var array expected id values for tab div elements
	 */
	public $tabs = array('general', 'assignment', 'permissions', 'attrib-advanced');

	/**
	 * Array of groups for this page. A group is a collapsable slider inside a tab.
	 * The format of this array is <tab id> => <group label>. Note that each menu item type has its own options and its own groups.
	 * These are the common ones for almost all core menu item types.
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $groups = array(
			'options' => array('Basic Options', 'Advanced Options'),
	);

	/**
	 * Associative array of expected input fields for the Account Details and Basic Settings tabs
	 * Assigned User Groups tab is omitted because that depends on the groups set up in the sample data
	 * @var unknown_type
	 */
	public $inputFields = array (
			array('label' => 'Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Parent Category', 'id' => 'jform_params_parent', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Category Descriptions', 'id' => 'jform_params_show_description', 'type' => 'fieldset', 'tab' => 'general'),
			array('label' => 'Show Number of Articles', 'id' => 'jform_params_numitems', 'type' => 'fieldset', 'tab' => 'general'),
			array('label' => 'Show Subcategories', 'id' => 'jform_params_show_children', 'type' => 'fieldset', 'tab' => 'general'),
			array('label' => '# First Subcategories', 'id' => 'jform_params_count', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Maximum Level Depth', 'id' => 'jform_params_maxlevel', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Show Title', 'id' => 'jform_showtitle', 'type' => 'fieldset', 'tab' => 'general'),
			array('label' => 'Position', 'id' => 'jform_position', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Status', 'id' => 'jform_published', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Start Publishing', 'id' => 'jform_publish_up', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Finish Publishing', 'id' => 'jform_publish_down', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Access', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Ordering', 'id' => 'jform_ordering', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Language', 'id' => 'jform_language', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Note', 'id' => 'jform_note', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Module Assignment', 'id' => 'jform_menus', 'type' => 'div', 'tab' => 'assignment'),
			array('label' => 'Alternative Layout', 'id' => 'jform_params_layout', 'type' => 'select', 'tab' => 'attrib-advanced'),
			array('label' => 'Heading style', 'id' => 'jform_params_item_heading', 'type' => 'select', 'tab' => 'attrib-advanced'),
			array('label' => 'Module Class Suffix', 'id' => 'jform_params_moduleclass_sfx', 'type' => 'textarea', 'tab' => 'attrib-advanced'),
			array('label' => 'Caching', 'id' => 'jform_params_owncache', 'type' => 'select', 'tab' => 'attrib-advanced'),
			array('label' => 'Cache Time', 'id' => 'jform_params_cache_time', 'type' => 'input', 'tab' => 'attrib-advanced'),
			array('label' => 'Module Tag', 'id' => 'jform_params_module_tag', 'type' => 'select', 'tab' => 'attrib-advanced'),
			array('label' => 'Bootstrap Size', 'id' => 'jform_params_bootstrap_size', 'type' => 'select', 'tab' => 'attrib-advanced'),
			array('label' => 'Header Tag', 'id' => 'jform_params_header_tag', 'type' => 'select', 'tab' => 'attrib-advanced'),
			array('label' => 'Header Class', 'id' => 'jform_params_header_class', 'type' => 'input', 'tab' => 'attrib-advanced'),
			array('label' => 'Module Style', 'id' => 'jform_params_style', 'type' => 'select', 'tab' => 'attrib-advanced'),
			array('label' => 'Select Menu', 'id' => 'jform_params_menutype', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Base Item', 'id' => 'jform_params_base', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Start Level', 'id' => 'jform_params_startLevel', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'End Level', 'id' => 'jform_params_endLevel', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Show Sub-menu Items', 'id' => 'jform_params_showAllChildren', 'type' => 'fieldset', 'tab' => 'general'),
			array('label' => 'Menu Tag ID', 'id' => 'jform_params_tag_id', 'type' => 'input', 'tab' => 'attrib-advanced'),
			array('label' => 'Menu Class Suffix', 'id' => 'jform_params_class_sfx', 'type' => 'input', 'tab' => 'attrib-advanced'),
			array('label' => 'Target Position', 'id' => 'jform_params_window_open', 'type' => 'input', 'tab' => 'attrib-advanced'),
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

	/**
	 * function to get module type
	 *
	 * @return bool
	 */
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

	/**
	 * function to get tab IDs
	 *
	 * @return array
	 */
	public function getTabIds()
	{
		$tabs = $this->driver->findElements(By::xPath("//div[@class='tab-content'][@id='myTabContent']/div"));
		$return = array();

		foreach ($tabs as $tab)
		{
			$return[] = $tab->getAttribute('id');
		}

		return $return;
	}
}
