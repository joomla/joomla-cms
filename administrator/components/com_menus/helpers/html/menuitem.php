<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Menus component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
abstract class JHtmlMenuItem
{
	/**
	 * @param	int $value	The state value.
	 * @param	int $i
	 * @param	boolean		An optional setting for access control on the action.
	 */
	public static function home($value = 0, $i, $canChange = true)
	{
		// Array of image, task, title, action
		$states	= array(
			1	=> array('icon-16-default.png',	'items.unsetHome',	'JDEFAULT', 'COM_MENUS_HTML_UNSET_HOME_ITEM'),
			0	=> array('icon-16-default-grayed.png', 'items.setHome', '',	'COM_MENUS_HTML_SET_HOME_ITEM'),
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[0]);
		$html	= JHTML::_('image','menu/'.$state[0], JText::_($state[2]), NULL, true);
		if ($canChange) {
			$html	= '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.JText::_($state[3]).'">'
					. $html.'</a>';
		}

		return $html;
	}
}
