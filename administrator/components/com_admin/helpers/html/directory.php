<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Utility class working with directory
 *
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 * @since       1.6
 */
abstract class JHtmlDirectory
{
	/**
	 * Method to generate a (un)writable message for directory
	 *
	 * @param   boolean  $writable  True if the directory is writable.
	 *
	 * @return  string  HTML translatable message surrounded by a <span>.
	 */
	public static function writable($writable)
	{
		if ($writable)
		{
			return '<span class="badge badge-success">'. JText::_('COM_ADMIN_WRITABLE') .'</span>';
		}
		else
		{
			return '<span class="badge badge-important">'. JText::_('COM_ADMIN_UNWRITABLE') .'</span>';
		}
	}

	/**
	 * Method to generate a message for a directory
	 *
	 * @param   string   $dir      The directory
	 * @param   boolean  $message  The text message
	 * @param   boolean  $visible  True if the $dir is visible
	 *
	 * @return  string  Translatable HTML message surrounded by a <strong>.
	 */
	public static function message($dir, $message, $visible=true)
	{
		if ($visible)
		{
			$output = $dir;
		}
		else
		{
			$output = '';
		}
		if (empty($message))
		{
			return $output;
		}
		else
		{
			return $output.' <strong>'.JText::_($message).'</strong>';
		}
	}
}
