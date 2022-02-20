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
 * Utility class working with directory
 *
 * @since  1.6
 */
abstract class JHtmlDirectory
{
	/**
	 * Method to generate a (un)writable message for directory
	 *
	 * @param   boolean  $writable  is the directory writable?
	 *
	 * @return  string	html code
	 */
	public static function writable($writable)
	{
		if ($writable)
		{
			return '<span class="badge badge-success">' . JText::_('COM_ADMIN_WRITABLE') . '</span>';
		}

		return '<span class="badge badge-important">' . JText::_('COM_ADMIN_UNWRITABLE') . '</span>';
	}

	/**
	 * Method to generate a message for a directory
	 *
	 * @param   string   $dir      the directory
	 * @param   boolean  $message  the message
	 * @param   boolean  $visible  is the $dir visible?
	 *
	 * @return  string	html code
	 */
	public static function message($dir, $message, $visible = true)
	{
		$output = $visible ? $dir : '';

		if (empty($message))
		{
			return $output;
		}

		return $output . ' <strong>' . JText::_($message) . '</strong>';
	}
}
