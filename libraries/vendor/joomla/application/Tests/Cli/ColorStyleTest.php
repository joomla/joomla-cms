<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Cli\Tests;

use Joomla\Application\Cli\ColorStyle;

/**
 * Test class.
 *
 * @since  1.0
 */
class ColorStyleTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var ColorStyle
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->object = new ColorStyle('red', 'white', array('blink'));
	}

	/**
	 * Test the GetStyle method.
	 *
	 * @covers Joomla\Application\Cli\ColorStyle::getStyle
	 *
	 * @return void
	 */
	public function testGetStyle()
	{
		$this->assertThat(
			$this->object->getStyle(),
			$this->equalTo('31;47;5')
		);
	}

	/**
	 * Test the ToString method.
	 *
	 * @return void
	 */
	public function testToString()
	{
		$this->assertThat(
			$this->object->__toString(),
			$this->equalTo('31;47;5')
		);
	}

	/**
	 * Test the __construct method.
	 *
	 * @return void
	 */
	public function fromString()
	{
		$style = new ColorStyle('white', 'red', array('blink', 'bold'));

		$this->assertThat(
			$this->object->fromString('fg=white;bg=red;options=blink,bold'),
			$this->equalTo($style)
		);
	}

	/**
	 * Test the fromString method.
	 *
	 * @expectedException \RuntimeException
	 *
	 * @return void
	 */
	public function testFromStringInvalid()
	{
		$this->object->fromString('XXX;XX=YY');
	}

	/**
	 * Test the __construct method.
	 *
	 * @expectedException \InvalidArgumentException
	 *
	 * @return void
	 */
	public function testConstructInvalid1()
	{
		new ColorStyle('INVALID');
	}

	/**
	 * Test the __construct method.
	 *
	 * @expectedException \InvalidArgumentException
	 *
	 * @return void
	 */
	public function testConstructInvalid2()
	{
		new ColorStyle('', 'INVALID');
	}

	/**
	 * Test the __construct method.
	 *
	 * @expectedException \InvalidArgumentException
	 *
	 * @return void
	 */
	public function testConstructInvalid3()
	{
		new ColorStyle('', '', array('INVALID'));
	}
}
