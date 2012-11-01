<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Input
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JInput.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Media
 * @since       11.1
 */
class JInputCLITest extends PHPUnit_Framework_TestCase
{
	/**
	 */
	protected $inspector;

	/**
	 * Setup for testing.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		include_once __DIR__ . '/stubs/JInputCliInspector.php';
		include_once __DIR__ . '/stubs/JFilterInputMock.php';
	}

	/**
	 * Test the JInput::get method.
	 *
	 * @return void
	 */
	public function testGet()
	{
		$_SERVER['argv'] = array('/dev/null', '--foo=bar', '-ab', 'blah');
		$this->inspector = new JInputCLIInspector(null, array('filter' => new JFilterInputMock));

		$this->assertThat(
			$this->inspector->get('foo'),
			$this->identicalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$this->inspector->get('a'),
			$this->identicalTo(true),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$this->inspector->get('b'),
			$this->identicalTo(true),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$this->inspector->args,
			$this->equalTo(array('blah')),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the JInput::get method.
	 *
	 * @return void
	 */
	public function testParseLongArguments()
	{
		$_SERVER['argv'] = array('/dev/null', '--ab', 'cd', '--ef', '--gh=bam');
		$this->inspector = new JInputCLIInspector(null, array('filter' => new JFilterInputMock));

		$this->assertThat(
			$this->inspector->get('ab'),
			$this->identicalTo('cd'),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$this->inspector->get('ef'),
			$this->identicalTo(true),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$this->inspector->get('gh'),
			$this->identicalTo('bam'),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$this->inspector->args,
			$this->equalTo(array()),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the JInput::get method.
	 *
	 * @return void
	 */
	public function testParseShortArguments()
	{
		$_SERVER['argv'] = array('/dev/null', '-ab', '-c', '-e', 'f', 'foobar', 'ghijk');
		$this->inspector = new JInputCLIInspector(null, array('filter' => new JFilterInputMock));

		$this->assertThat(
			$this->inspector->get('a'),
			$this->identicalTo(true),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$this->inspector->get('b'),
			$this->identicalTo(true),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$this->inspector->get('c'),
			$this->identicalTo(true),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$this->inspector->get('e'),
			$this->identicalTo('f'),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$this->inspector->args,
			$this->equalTo(array('foobar', 'ghijk')),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the JInput::get method.
	 *
	 * @return void
	 */
	public function testGetFromServer()
	{
		$this->inspector = new JInputCLIInspector(null, array('filter' => new JFilterInputMock));

		// Check the object type.
		$this->assertThat(
			$this->inspector->server instanceof JInput,
			$this->isTrue(),
			'Line: ' . __LINE__ . '.'
		);

		// Test the get method.
		$this->assertThat(
			$this->inspector->server->get('PHP_SELF'),
			$this->identicalTo($_SERVER['PHP_SELF']),
			'Line: ' . __LINE__ . '.'
		);
	}
}
