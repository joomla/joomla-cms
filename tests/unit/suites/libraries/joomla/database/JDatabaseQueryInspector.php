<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class to expose protected properties and methods in JDatabaseQueryExporter for testing purposes.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       1.7.0
 */
class JDatabaseQueryInspector extends JDatabaseQuery
{
	/**
	 * Sets any property from the class.
	 *
	 * @param   string  $property  The name of the class property.
	 * @param   string  $value     The value of the class property.
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function __set($property, $value)
	{
		return $this->$property = $value;
	}

	/**
	 * Gets any property from the class.
	 *
	 * @param   string  $property  The name of the class property.
	 *
	 * @return  mixed   The value of the class property.
	 *
	 * @since   1.7.0
	 */
	public function get($property)
	{
		return $this->$property;
	}
}
