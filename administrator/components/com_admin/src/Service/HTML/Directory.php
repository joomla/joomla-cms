<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Service\HTML;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Utility class working with directory
 *
 * @since  1.6
 */
class Directory
{
	/**
	 * Method to generate a (un)writable message for directory
	 *
	 * @param   boolean  $writable  is the directory writable?
	 *
	 * @return  string	html code
	 */
	public function writable($writable)
	{
		if ($writable)
		{
			return '<span class="badge badge-success">' . Text::_('COM_ADMIN_WRITABLE') . '</span>';
		}

		return '<span class="badge badge-danger">' . Text::_('COM_ADMIN_UNWRITABLE') . '</span>';
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
	public function message($dir, $message, $visible = true)
	{
		$output = $visible ? $dir : '';

		if (empty($message))
		{
			return $output;
		}

		return $output . ' <strong>' . Text::_($message) . '</strong>';
	}
}
