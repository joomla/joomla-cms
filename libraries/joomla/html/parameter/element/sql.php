<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  HTML
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a SQL element
 *
 * @package		Joomla.Platform
 * @subpackage		Parameter
 * @since		11.1
 */

class JElementSQL extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $_name = 'SQL';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$db			= JFactory::getDbo();
		$db->setQuery($node->attributes('query'));
		$key = ($node->attributes('key_field') ? $node->attributes('key_field') : 'value');
		$val = ($node->attributes('value_field') ? $node->attributes('value_field') : $name);

		$options = $db->loadObjectlist();

		// Check for an error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
			return false;
		}

		if (!$options) {
			$options = array();
		}

		return JHtml::_('select.genericlist', $options, $control_name.'['.$name.']',
			array(
				'id' => $control_name.$name,
				'list.attr' => 'class="inputbox"',
				'list.select' => $value,
				'option.key' => $key,
				'option.text' => $val
			)
		);
	}
}
