<?php
/**
 * @package    Joomla.UnitTest
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

/**
 * Tests for the JCmsCompatibility class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Utilities
 * @since       3.0.3
 */
class compatibilityTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JCmsCompatibility
	 * @since  3.0.3
	 */
	protected $_instance;

	/**
	 * Checks the __construct method.
	 *
	 * @return  void
	 *
	 * @covers  JCompatibility::__construct
	 * @since   3.0.3
	 */
	public function test__construct()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Checks the check method.
	 *
	 * @return  void
	 *
	 * @covers  JCompatibility::check
	 * @since   3.0.3
	 */
	public function testCheck()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Checks the checkRules method.
	 *
	 * @return  void
	 *
	 * @covers  JCompatibility::checkRules
	 * @since   3.0.3
	 */
	public function testCheckRules()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Sets up the fixture. This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.0.3
	 */
	protected function setUp()
	{
		$compatibilities = new SimpleXMLElement('<compatibilities />');
		$this->_instance = new JCompatibility($compatibilities);
	}
}
