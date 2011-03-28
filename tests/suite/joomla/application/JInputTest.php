<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once JPATH_PLATFORM.'/joomla/application/input.php';

/**
 * Test class for JInput.
 */
class JInputTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @access protected
	 */
	protected $inspector;

	/**
	 * Setup for testing.
	 *
	 * @return void
	 */
	public function setUp()
	{
		include_once JPATH_TESTS.'/suite/joomla/application/TestStubs/JInput_Inspector.php';
		include_once JPATH_TESTS.'/suite/joomla/application/TestStubs/JFilterInput_Mock.php';
	}

	/**
	 * Test the JInput::get method.
	 */
	public function testGet()
	{
		$this->inspector = new JInputInspector(null, array('filter' => new JFilterInputMock()));
		
		$_REQUEST['foo'] = 'bar';
		
		// Test the get method.
		$this->assertThat(
			$this->inspector->get('foo'),
			$this->equalTo('bar'),
			'Line: '.__LINE__.'.'
		);
		
		$_GET['foo'] = 'bar2';
		
		// Test the get method.
		$this->assertThat(
			$this->inspector->get->get('foo'),
			$this->equalTo('bar2'),
			'Line: '.__LINE__.'.'
		);
		
	}

	/**
	 * Test the JInput::get method.
	 */
	public function testGetFromCookie()
	{
		$this->inspector = new JInputInspector(null, array('filter' => new JFilterInputMock()));

		// Check the object type.
		$this->assertThat(
			$this->inspector->cookie instanceof JInputCookie,
			$this->isTrue(),
			'Line: '.__LINE__.'.'
		);
		
		$_COOKIE['foo'] = 'bar';
		
		// Test the get method.
		$this->assertThat(
			$this->inspector->cookie->get('foo'),
			$this->equalTo('bar'),
			'Line: '.__LINE__.'.'
		);
	}
}
