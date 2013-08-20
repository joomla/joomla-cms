<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Object
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Joomla Platform Capitaliser Object Class
 *
 * @package     Joomla.UnitTest
 * @subpackage  Object
 * @since       12.1
 */
class JDataCapitaliser extends JData
{
	/**
	 * Set an object property.
	 *
	 * @param   string  $property  The property name.
	 * @param   mixed   $value     The property value.
	 *
	 * @return  mixed  The property value.
	 *
	 * @since   12.3
	 */
	protected function setProperty($property, $value)
	{
		return parent::setProperty($property, strtoupper($value));
	}
}
