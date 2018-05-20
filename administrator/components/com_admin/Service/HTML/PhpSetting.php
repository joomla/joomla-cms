<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Service\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Utility class working with phpsetting
 *
 * @since  1.6
 */
class PhpSetting
{
	/**
	 * Method to generate a boolean message for a value
	 *
	 * @param   boolean  $val  is the value set?
	 *
	 * @return  string html code
	 */
	public function boolean($val)
	{
		return Text::_($val ? 'JON' : 'JOFF');
	}

	/**
	 * Method to generate a boolean message for a value
	 *
	 * @param   boolean  $val  is the value set?
	 *
	 * @return  string html code
	 */
	public function set($val)
	{
		return Text::_($val ? 'JYES' : 'JNO');
	}

	/**
	 * Method to generate a string message for a value
	 *
	 * @param   string  $val  a php ini value
	 *
	 * @return  string html code
	 */
	public function string($val)
	{
		return !empty($val) ? $val : Text::_('JNONE');
	}

	/**
	 * Method to generate an integer from a value
	 *
	 * @param   string  $val  a php ini value
	 *
	 * @return  string html code
	 *
	 * @deprecated  4.0  Use intval() or casting instead.
	 */
	public function integer($val)
	{
		try
		{
			Log::add(sprintf('%s() is deprecated. Use intval() or casting instead.', __METHOD__), Log::WARNING, 'deprecated');
		}
		catch (\RuntimeException $exception)
		{
			// Informational log only
		}

		return (int) $val;
	}
}
