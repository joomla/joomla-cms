<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.UnitTest
 */

defined('JPATH_PLATFORM') or die;

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
