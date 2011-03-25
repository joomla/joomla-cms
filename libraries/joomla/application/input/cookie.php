<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Input Base Class
 *
 * This is an abstracted input class used to manage retrieving data from the application environment.
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       11.1
 */
class JInputCookie
{
	/**
	 * Sets a value
	 *
	 * @param   string  $name   Name of the value to set.
	 * @param   mixed   $value  Value to assign to the input.
	 * 
	 * @return  void
	 * 
	 * @since   11.1
	 */
	public function set($name, $value)
	{
		$this->_data[$name] = $value;
	}
}
