<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Object
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Derived JData class for testing.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Object
 * @since       12.1
 */
class JDataBuran extends JData
{
	public $rocket = false;

	/**
	 * Method to set the test_value.
	 *
	 * @param   string  $value  The test value.
	 *
	 * @return  JData  Chainable.
	 *
	 * @since   12.3
	 */
	protected function setTestValue($value)
	{
		// Set the property as uppercase.
		return strtoupper($value);
	}
}
