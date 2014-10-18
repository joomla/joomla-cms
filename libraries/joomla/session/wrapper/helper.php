<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for JSessionHelper
 *
 * @package     Joomla.Platform
 * @subpackage  Session
 * @since       3.4
 */
class JSessionWrapperHelper
{
	/**
	 * Helper wrapper method for unserialize
	 *
	 * @param   string  $session_data  The session data to process.
	 *
	 * @return  mixed
	 *
	 * @see     JSessionHelper::unserialize()
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function unserialize($session_data)
	{
		return JSessionHelper::unserialize($session_data);
	}
}
