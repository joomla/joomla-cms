<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for the back-end menu items manager screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class CategoryEditPage extends AdminEditPage
{
  /**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath =  "//form[@id='item-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabs = array('general', 'publishing', 'options', 'metadata','rules');

	/**
	 * Array of tab labels for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabLabels = array('Category Details','Publishing Options', 'Options', 'Metadata Options', 'Category Permissions');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.0
	 */
	public $inputFields = array (
			array('label' => 'Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Alias', 'id' => 'jform_alias', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Description', 'id' => 'jform_description', 'type' => 'textarea', 'tab' => 'general'),

			array('label' => 'ID', 'id' => 'jform_id', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Hits', 'id' => 'jform_hits', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created by', 'id' => 'jform_created_user_id', 'type' => 'input', 'tab' => 'publishing'),

			array('label' => 'Alternative Layout', 'id' => 'jform_params_category_layout', 'type' => 'select', 'tab' => 'options'),
			array('label' => 'Image', 'id' => 'jform_params_image', 'type' => 'input', 'tab' => 'options'),
			array('label' => 'Note', 'id' => 'jform_note', 'type' => 'input', 'tab' => 'options'),

			array('label' => 'Meta Description', 'id' => 'jform_metadesc', 'type' => 'textarea', 'tab' => 'metadata'),
			array('label' => 'Meta Keywords', 'id' => 'jform_metakey', 'type' => 'textarea', 'tab' => 'metadata'),
			array('label' => 'Author', 'id' => 'jform_metadata_author', 'type' => 'input', 'tab' => 'metadata'),
			array('label' => 'Robots', 'id' => 'jform_metadata_robots', 'type' => 'select', 'tab' => 'metadata'),
	);


}
