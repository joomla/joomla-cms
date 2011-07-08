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
 * Renders a SQL element
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated  JParameter is deprecated and will be removed in a future version. Use JForm instead.
 */
class JElementSQL extends JElement
{
	/**
	 * Element name
	 *
	 * @var    string
	 */
	protected $_name = 'SQL';

	/**
	 *
	 * @since   11.1
	 *
	 * @deprecated
	 */
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
