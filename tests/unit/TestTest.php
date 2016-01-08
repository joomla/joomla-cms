<?php

class TestTest extends PHPUnit_Framework_TestCase
{
	public function testA()
	{
		$this->assertTrue(true);
	}

	public function testB()
	{
		$this->assertTrue(false);
	}

	public function testC()
	{
		$this->markTestIncomplete('Incomplete');
	}

	public function testD()
	{
		$this->assertTrue($foo);
	}

	public function testE()
	{
		$this->markTestSkipped('Skipped');
	}
}
