<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.application.input');

/**
 * Joomla! Input Files Class
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       11.1
 */
class JInputFiles extends JInput
{
	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 * @param   string  $filter   Filter to apply to the value.
	 * 
	 * @return  mixed  The filtered input value.
	 * 
	 * @since   11.1
	 */
	public function get($name, $default, $filter = 'cmd')
	{
		if (isset ($this->data[$name])) {
			return $this->filter->clean($this->data[$name], $filter);
		}

		foreach ($this->inputs AS $input)
		{
			$return = $input->get($name, $default, $filter);

			if ($return != null) {
				return $return;
			}
		}
		
		return $default;
	}

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
		$this->data[$name] = $value;
	}
}
