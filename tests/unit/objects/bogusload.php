<?php
class BogusLoad
{
	public $someMethodCalled = false;

	public function someMethod ()
	{
		$this->someMethodCalled = true;
	}
}
?>