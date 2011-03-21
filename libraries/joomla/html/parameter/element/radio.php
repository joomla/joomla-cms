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
 * Renders a radio element
 *
 * @package		Joomla.Platform
 * @subpackage		Parameter
 * @since		11.1
 */

class JElementRadio extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $_name = 'Radio';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$options = array ();
		foreach ($node->children() as $option)
		{
			$val	= $option->attributes('value');
			$text	= $option->data();
			$options[] = JHtml::_('select.option', $val, $text);
		}

		return JHtml::_('select.radiolist', $options, ''.$control_name.'['.$name.']', '', 'value', 'text', $value, $control_name.$name, true);
	}
}
