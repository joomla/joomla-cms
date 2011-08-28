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
 * @deprecated  12.1  Use JFormFieldUserGroup instead.
 */
class JElementUserGroup extends JElement
{
	/**
	 * Element name
	 *
	 * @var    string
	 */
	protected $_name = 'UserGroup';

	/**
	/**
	 * Fetch the timezones element
	 *
	 * @param   string  $name          Element name
	 * @param   string  $value         Element value
	 * @param   object  &$node         The current JSimpleXMLElement node.
	 * @param   string  $control_name  Control name
	 *
	 * @return  string
	 *
	 * @deprecated  12.1  Use JFormFieldUserGroup::getInput instead.
	 * @since   11.1
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		// Deprecation warning.
		JLog::add('JElementUserGroup::_fetchElement() is deprecated.', JLog::WARNING, 'deprecated');

		$ctrl = $control_name . '[' . $name . ']';
		$attribs = ' ';

		if ($v = $node->attributes('size'))
		{
			$attribs .= 'size="' . $v . '"';
		}
		if ($v = $node->attributes('class'))
		{
			$attribs .= 'class="' . $v . '"';
		}
		else
		{
			$attribs .= 'class="inputbox"';
		}
		if ($m = $node->attributes('multiple'))
		{
			$attribs .= 'multiple="multiple"';
			$ctrl .= '[]';
			//$value		= implode('|',)
		}
		//array_unshift($editors, JHtml::_('select.option',  '', '- '. JText::_('SELECT_EDITOR') .' -'));

		return JHtml::_('access.usergroup', $ctrl, $value, $attribs, false);
	}
}
