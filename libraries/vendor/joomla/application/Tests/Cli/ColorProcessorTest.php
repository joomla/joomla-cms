<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Tests;

use Joomla\Application\Cli\ColorProcessor;
use Joomla\Application\Cli\ColorStyle;

/**
 * Test class.
 *
 * @since  1.0
 */
class ColorProcessorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Object under test
	 *
	 * @var    ColorProcessor
	 * @since  1.0
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		$this->object = new ColorProcessor;
	}

	/**
	 * Tests the process method for adding a style
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\Cli\ColorProcessor::addStyle
	 * @since   1.0
	 */
	public function testAddStyle()
	{
		$style = new ColorStyle('red');
		$this->object->addStyle('foo', $style);

		$this->assertThat(
			$this->object->process('<foo>foo</foo>'),
			$this->equalTo('[31mfoo[0m')
		);
	}

	/**
	 * Tests the stripColors method
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\Cli\ColorProcessor::stripColors
	 * @since   1.0
	 */
	public function testStripColors()
	{
		$this->assertThat(
			$this->object->stripColors('<foo>foo</foo>'),
			$this->equalTo('foo')
		);
	}

	/**
	 * Tests the process method for replacing colors
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\Cli\ColorProcessor::process
	 * @since   1.0
	 */
	public function testProcess()
	{
		$this->assertThat(
			$this->object->process('<fg=red>foo</fg=red>'),
			$this->equalTo('[31mfoo[0m')
		);
	}

	/**
	 * Tests the process method for replacing colors
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\Cli\ColorProcessor::process
	 * @since   1.0
	 */
	public function testProcessNamed()
	{
		$style = new ColorStyle('red');
		$this->object->addStyle('foo', $style);

		$this->assertThat(
			$this->object->process('<foo>foo</foo>'),
			$this->equalTo('[31mfoo[0m')
		);
	}

	/**
	 * Tests the process method for replacing colors
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\Cli\ColorProcessor::replaceColors
	 * @since   1.0
	 */
	public function testProcessReplace()
	{
		$this->assertThat(
			$this->object->process('<fg=red>foo</fg=red>'),
			$this->equalTo('[31mfoo[0m')
		);
	}
}
