<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Input
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JInputCli.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Input
 * @since       11.1
 */
class JInputCliTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test object
	 *
	 * @var    JInputCli
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

		include_once __DIR__ . '/stubs/JFilterInputMock.php';
	}

	/**
	 * Test the JInput::parseArguments method.
	 *
	 * @dataProvider provider_parseArguments
	 */
	public function test_parseArguments($inputArgv, $expectedData, $expectedArgs)
	{
		$_SERVER['argv'] = $inputArgv;
		$this->inspector = new JInputCli(null, array('filter' => new JFilterInputMock));

		$this->assertThat(
			TestReflection::getValue($this->inspector, 'data'),
			$this->identicalTo($expectedData)
		);

		$this->assertThat(
			TestReflection::getValue($this->inspector, 'args'),
			$this->identicalTo($expectedArgs)
		);
	}

	/**
	 * Test inputs:
	 *
	 * php test.php --foo --bar=baz
	 * php test.php -abc
	 * php test.php arg1 arg2 arg3
	 * php test.php plain-arg --foo --bar=baz --funny="spam=eggs" --also-funny=spam=eggs \
	 *     'plain arg 2' -abc -k=value "plain arg 3" --s="original" --s='overwrite' --s
	 * php test.php --key value -abc not-c-value
	 * php test.php --key1 value1 -a --key2 -b b-value --c
	 *
	 * Note that this pattern is not supported: -abc c-value
	 */
	public function provider_parseArguments()
	{
		return array(

			// Represents: php test.php --foo --bar=baz
			array(
				array('test.php', '--foo', '--bar=baz'),
				array(
					'foo' => true,
					'bar' => 'baz'
				),
				array()
			),

			// Represents: php test.php -abc
			array(
				array('test.php', '-abc'),
				array(
					'a' => true,
					'b' => true,
					'c' => true
				),
				array()
			),

			// Represents: php test.php arg1 arg2 arg3
			array(
				array('test.php', 'arg1', 'arg2', 'arg3'),
				array(),
				array(
					'arg1',
					'arg2',
					'arg3'
				)
			),

			// Represents: php test.php plain-arg --foo --bar=baz --funny="spam=eggs" --also-funny=spam=eggs \
			//      'plain arg 2' -abc -k=value "plain arg 3" --s="original" --s='overwrite' --s
			array(
				array('test.php', 'plain-arg', '--foo', '--bar=baz', '--funny=spam=eggs', '--also-funny=spam=eggs',
					'plain arg 2', '-abc', '-k=value', 'plain arg 3', '--s=original', '--s=overwrite', '--s'),
				array(
					'foo' => true,
					'bar' => 'baz',
					'funny' => 'spam=eggs',
					'also-funny' => 'spam=eggs',
					'a' => true,
					'b' => true,
					'c' => true,
					'k' => 'value',
					's' => 'overwrite'
				),
				array(
					'plain-arg',
					'plain arg 2',
					'plain arg 3'
				)
			),

			// Represents: php test.php --key value -abc not-c-value
			array(
				array('test.php', '--key', 'value', '-abc', 'not-c-value'),
				array(
					'key' => 'value',
					'a' => true,
					'b' => true,
					'c' => true
				),
				array(
					'not-c-value'
				)
			),

			// Represents: php test.php --key1 value1 -a --key2 -b b-value --c
			array(
				array('test.php', '--key1', 'value1', '-a', '--key2', '-b', 'b-value', '--c'),
				array(
					'key1' => 'value1',
					'a' => true,
					'key2' => true,
					'b' => 'b-value',
					'c' => true
				),
				array()
			)
		);
	}

	/**
	 * Test the JInput::get method.
	 *
	 * @return void
	 */
	public function testGet()
	{
		$_SERVER['argv'] = array('/dev/null', '--foo=bar', '-ab', 'blah');
		$this->inspector = new JInputCli(null, array('filter' => new JFilterInputMock));

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
		$this->inspector = new JInputCli(null, array('filter' => new JFilterInputMock));

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
		$this->inspector = new JInputCli(null, array('filter' => new JFilterInputMock));

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
		$this->inspector = new JInputCli(null, array('filter' => new JFilterInputMock));

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
