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
 * Renders a editors element
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated  Use JForm instead
 */

class JElementEditors extends JElement
{
	/**
	* Element name
	*
	* @var    string
	*/
	protected $_name = 'Editors';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$db		= JFactory::getDbo();
		$user	= JFactory::getUser();

		// compile list of the editors
		$query = 'SELECT element AS value, name AS text'
		. ' FROM #__extensions'
		. ' WHERE folder = "editors"'
		. ' AND type = "plugin"'
		. ' AND enabled = 1'
		. ' ORDER BY ordering, name'
		;
		$db->setQuery($query);
		$editors = $db->loadObjectList();

		array_unshift($editors, JHtml::_('select.option', '', JText::_('JOPTION_SELECT_EDITOR')));

		return JHtml::_('select.genericlist', $editors, $control_name .'['. $name .']',
			array(
				'id' => $control_name.$name,
				'list.attr' => 'class="inputbox"',
				'list.select' => $value
			)
		);
	}
}
