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
require_once JPATH_PLATFORM.'/joomla/application/input/cli.php';

/**
 * Test class for JInput.
 */
class JInputCLITest extends PHPUnit_Framework_TestCase
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
		include_once JPATH_TESTS.'/suite/joomla/application/input/TestStubs/JInputCLI_Inspector.php';
		include_once JPATH_TESTS.'/suite/joomla/application/TestStubs/JFilterInput_Mock.php';
	}

	/**
	 * Test the JInput::get method.
	 */
	public function testGet()
	{
		$_SERVER['argv'] = array('/dev/null', '--foo=bar', '-ab', 'blah');
		$this->inspector = new JInputCLIInspector(null, array('filter' => new JFilterInputMock()));

		$this->assertThat(
			$this->inspector->get('foo'),
			$this->identicalTo('bar'),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->get('a'),
			$this->identicalTo(true),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->get('b'),
			$this->identicalTo(true),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->args,
			$this->equalTo(array('blah')),
			'Line: '.__LINE__.'.'
		);
	}

	/**
	 * Test the JInput::get method.
	 */
	public function testParseLongArguments()
	{
		$_SERVER['argv'] = array('/dev/null', '--ab', 'cd', '--ef', '--gh=bam');
		$this->inspector = new JInputCLIInspector(null, array('filter' => new JFilterInputMock()));

		$this->assertThat(
			$this->inspector->get('ab'),
			$this->identicalTo('cd'),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->get('ef'),
			$this->identicalTo(true),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->get('gh'),
			$this->identicalTo('bam'),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->args,
			$this->equalTo(array()),
			'Line: '.__LINE__.'.'
		);
	}

	/**
	 * Test the JInput::get method.
	 */
	public function testParseShortArguments()
	{
		$_SERVER['argv'] = array('/dev/null', '-ab', '-c', '-e', 'f', 'foobar', 'ghijk');
		$this->inspector = new JInputCLIInspector(null, array('filter' => new JFilterInputMock()));

		$this->assertThat(
			$this->inspector->get('a'),
			$this->identicalTo(true),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->get('b'),
			$this->identicalTo(true),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->get('c'),
			$this->identicalTo(true),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->get('e'),
			$this->identicalTo('f'),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->args,
			$this->equalTo(array('foobar', 'ghijk')),
			'Line: '.__LINE__.'.'
		);
	}

	/**
	 * Test the JInput::get method.
	 */
	public function testGetFromServer()
	{
		$this->inspector = new JInputCLIInspector(null, array('filter' => new JFilterInputMock()));

		// Check the object type.
		$this->assertThat(
			$this->inspector->server instanceof JInput,
			$this->isTrue(),
			'Line: '.__LINE__.'.'
		);

		// Test the get method.
		$this->assertThat(
			$this->inspector->server->get('PWD'),
			$this->identicalTo($_SERVER['PWD']),
			'Line: '.__LINE__.'.'
		);
	}
}
