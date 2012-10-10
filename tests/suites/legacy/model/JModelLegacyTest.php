<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

class TestModelLead extends JModelLegacy
{
}

class RemodelModelRoom extends JModelLegacy
{
}

/**
 * Test class for JModelLegacy.
 */
class JModelLegacyTest extends TestCase
{	
	/**
	 * @todo Implement testGetInstance().
	 */
	public function testGetInstance()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testSetState().
	 */
	public function testSetState()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testGetState().
	 */
	public function testGetState()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testGetDbo().
	 */
	public function testGetDbo()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testSetDbo().
	 */
	public function testSetDbo()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testGetName().
	 */
	public function testGetName()
	{
		$class = JModelLegacy::getInstance('Lead', 'TestModel');
		$this->assertEquals('lead', $class->getName());
		$this->assertEquals('com_test', TestReflection::getValue($class, 'option'));

		$class = JModelLegacy::getInstance('Room', 'RemodelModel');
		$this->assertEquals('room', $class->getName());
		$this->assertEquals('com_remodel', TestReflection::getValue($class, 'option'));

		TestReflection::setValue($class, 'name', 'foo');
		$this->assertEquals('foo', $class->getName());
		$this->assertEquals('com_remodel', TestReflection::getValue($class, 'option'));
	}

	/**
	 * @todo Implement testGetTable().
	 */
	public function testGetTable()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testAddIncludePath().
	 */
	public function testAddIncludePath()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testAddTablePath().
	 */
	public function testAddTablePath()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
