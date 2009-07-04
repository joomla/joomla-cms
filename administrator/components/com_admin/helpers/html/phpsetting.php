<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Utility class working with phpsetting
 *
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @since		1.6
 */
abstract class JHtmlPhpSetting
{
	/**
	 * method to generate a boolean message for a value
	 *
	 * @param boolean $val is the value set?
	 *
	 * @return string html code
	 */
	public static function boolean($val)
	{
		if ($val) {
			return JText::_('Admin_On');
		}
		else {
			return JText::_('Admin_Off');
		}
	}

	/**
	 * method to generate a boolean message for a value
	 *
	 * @param boolean $val is the value set?
	 *
	 * @return string html code
	 */
	public static function set($val)
	{
		if ($val) {
			return JText::_('Admin_Yes');
		} else {
			return JText::_('Admin_No');
		}
	}

	/**
	 * method to generate a string message for a value
	 *
	 * @param string $val a php ini value
	 *
	 * @return string html code
	 */
	public static function string($val)
	{
		if (empty($val)) {
			return JText::_('Admin_None');
		} else {
			return $val;
		}
	}

	/**
	 * method to generate an integer from a value
	 *
	 * @param string $val a php ini value
	 *
	 * @return string html code
	 */
	public static function integer($val)
	{
		return intval($val);
	}
}

