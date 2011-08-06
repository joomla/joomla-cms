<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a menu element
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated  Use JFormMenu instead
 */
class JElementMenu extends JElement
{
	/**
	 * Element name
	 *
	 * @var    string
	 */
	protected $_name = 'Menu';

	/**
	 *
	 * @since   11.1
	 *
	 * @deprecated    12.1  Use JFormFieldMenu::getOptions instead
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		// Deprecation warning.
		JLog::add('JElementMenu::fetchElement() is deprecated.', JLog::WARNING, 'deprecated');

		require_once JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php';
		$menuTypes = MenusHelper::getMenuTypes();

		foreach ($menuTypes as $menutype)
		{
			$options[] = JHtml::_('select.option', $menutype, $menutype);
		}
		array_unshift($options, JHtml::_('select.option', '', JText::_('JOPTION_SELECT_MENU')));

		return JHtml::_('select.genericlist', $options, $control_name . '[' . $name . ']',
			array('id' => $control_name . $name, 'list.attr' => 'class="inputbox"', 'list.select' => $value));
	}
}
