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
 * @package		Joomla.Platform
 * @subpackage		Parameter
 * @since		11.1
 * @deprecated	JParameter is deprecated and will be removed in a future version. Use JForm instead.
 */

class JElementUserGroup extends JElement
{
	/**
	* Element name
	*
	* @var		string
	*/
	protected $_name = 'UserGroup';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$ctrl	= $control_name .'['. $name .']';
		$attribs	= ' ';

		if ($v = $node->attributes('size')) {
			$attribs	.= 'size="'.$v.'"';
		}
		if ($v = $node->attributes('class')) {
			$attribs	.= 'class="'.$v.'"';
		} else {
			$attribs	.= 'class="inputbox"';
		}
		if ($m = $node->attributes('multiple'))
		{
			$attribs	.= 'multiple="multiple"';
			$ctrl		.= '[]';
			//$value		= implode('|',)
		}
		//array_unshift($editors, JHtml::_('select.option',  '', '- '. JText::_('SELECT_EDITOR') .' -'));

		return JHtml::_('access.usergroup', $ctrl, $value, $attribs, false);
	}
}
