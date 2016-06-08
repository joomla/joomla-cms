<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class for the back-end control panel screen.
 *
 * @since  Joomla 3.0
 */
class MenuEditPage extends AdminEditPage
{
	/**
	 * Associative array of expected input fields for the Menu Manager: Add / Edit Menu
	 *
	 * @var array
	 */
	public $inputFields = array(
		array('label' => 'Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'header'),
		array('label' => 'Menu Type', 'id' => 'jform_menutype', 'type' => 'input', 'tab' => 'header'),
		array('label' => 'Description', 'id' => 'jform_menudescription', 'type' => 'input', 'tab' => 'header'),
	);
	protected $waitForXpath = "//form[@id='item-form']";
	protected $url = 'administrator/index.php?option=com_menus&view=menu&layout=edit';
}
