<?php

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
