<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * JFormFieldCalendarInspector
 *
 * @package     Joomla.UnitTest
 * @subpackage  Filter
 *
 * @since       11.1
 */
class JFormFieldCalendarInspector extends JFormFieldCalendar
{
	/**
	 * Test...
	 *
	 * @param   string  $property  The property to retrieve.
	 *
	 * @return mixed
	 */
	public function getProtectedProperty($property)
	{
		return $this->{$property};
	}

	/**
	 * Test...
	 *
	 * @param   string  $property  The property to set.
	 * @param   mixed   $value     The value.
	 *
	 * @return void
	 */
	public function setProtectedProperty($property, $value)
	{
		$this->{$property} = $value;
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function getInput()
	{
		parent::getInput();
	}
}
