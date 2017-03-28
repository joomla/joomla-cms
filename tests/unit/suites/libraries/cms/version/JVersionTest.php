<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Version
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JVersion.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Version
 * @since       3.0
 */
class JVersionTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Object under test
	 *
	 * @var    JVersion
	 * @since  3.0
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function setUp()
	{
		$this->object = new JVersion;
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Tests the isCompatible method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testIsCompatible()
	{
		$this->assertTrue($this->object->isCompatible('2.5'));
	}

	/**
	 * Tests the getHelpVersion method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetHelpVersion()
	{
		$this->assertInternalType('string', $this->object->getHelpVersion());
	}

	/**
	 * Tests the getShortVersion method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetShortVersion()
	{
		$this->assertEquals($this->object->RELEASE . '.' . $this->object->DEV_LEVEL, $this->object->getShortVersion());
	}

	/**
	 * Tests the getLongVersion method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetLongVersion()
	{
		$this->assertInternalType('string', $this->object->getLongVersion());
	}

	/**
	 * Tests the getUserAgent method for a mask not containing the Mozilla version string
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetUserAgent_maskFalse()
	{
		$this->assertNotContains('Mozilla/5.0 ', $this->object->getUserAgent(null, false, true));
	}

	/**
	 * Tests the getUserAgent method for a mask containing the Mozilla version string
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetUserAgent_maskTrue()
	{
		$this->assertContains('Mozilla/5.0 ', $this->object->getUserAgent(null, true, true));
	}

	/**
	 * Tests the getUserAgent method for a null component string
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetUserAgent_ComponentNull()
	{
		$this->assertContains('Framework', $this->object->getUserAgent(null, false, true));
	}

	/**
	 * Tests the getUserAgent method for a component string matching the specified option
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetUserAgent_ComponentNotNull()
	{
		$this->assertContains('Component_test', $this->object->getUserAgent('Component_test', false, true));
	}
}
