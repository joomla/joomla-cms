<?php

class JEventStub extends JEvent
{
	public $calls = array();

	public function myEvent()
	{
		$this->calls[] = array('method' => 'myEvent', 'args' => func_get_args());
		return true;
	}
}
