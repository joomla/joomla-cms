<?php

/**
 * @version		$Id: category.php 13825 2009-12-23 01:03:06Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

// Import html library
jimport('joomla.html.html');

// Import joomla field list class
require_once dirname(__FILE__) . DS . 'list.php';

/**
 * Supports an HTML select list of menu
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldMenu extends JFormFieldList
{

	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Menu';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		require_once realpath(JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
		$menuTypes = MenusHelper::getMenuTypes();

		// Prepare return value
		$options = array();

		// Add basic option
		// TODO: would be better to put this basic option in the xml file ?

		$options[] = JHtml::_('select.option', '', JText::_('JOption_Select_Menu'));

		// Iterate over menus
		foreach($menuTypes as $menutype)
		{
			$options[] = JHtml::_('select.option', $menutype, $menutype);
		}
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::_getOptions(), $options);
		return $options;
	}
}

