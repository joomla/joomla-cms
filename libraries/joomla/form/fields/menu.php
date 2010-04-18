<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
JLoader::register('JFormFieldList', dirname(__FILE__).'/list.php');

// Import the com_menus helper.
require_once realpath(JPATH_ADMINISTRATOR.'/components/com_menus/helpers/menus.php');

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
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Menu';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// TODO: would be better to put this basic option in the xml file.
		$options[] = JHtml::_('select.option', '', JText::_('JOPTION_SELECT_MENU'));

		// Build the menu options.
		$menuTypes = MenusHelper::getMenuTypes();
		foreach($menuTypes as $menutype) {
			$options[] = JHtml::_('select.option', $menutype, $menutype);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}