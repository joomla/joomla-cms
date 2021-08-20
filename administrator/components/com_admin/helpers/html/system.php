<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Utility class working with system
 *
 * @since  1.6
 */
abstract class JHtmlSystem
{
	/**
	 * Method to generate a string message for a value
	 *
	 * @param   string  $val  a php ini value
	 *
	 * @return  string html code
	 */
	public static function server($val)
	{
		return !empty($val) ? $val : JText::_('COM_ADMIN_NA');
	}
}
