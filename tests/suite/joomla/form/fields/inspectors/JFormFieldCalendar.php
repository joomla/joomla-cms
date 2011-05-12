<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

class JFormFieldCalendarInspector extends JFormFieldCalendar
{
	public function getProtectedProperty($property)
	{
		return $this->{$property};
	}

	public function setProtectedProperty($property, $value)
	{
		$this->{$property} = $value;
	}

	public function getInput()
	{
		parent::getInput();
	}
}
