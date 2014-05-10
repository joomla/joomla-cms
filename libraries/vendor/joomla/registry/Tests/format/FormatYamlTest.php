<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Registry\Tests\Format;

use Joomla\Registry\AbstractRegistryFormat;

/**
 * Test class for Yaml.
 *
 * @since  1.0
 */
class FormatYamlTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Object being tested
	 *
	 * @var    Joomla\Registry\Format\Yaml
	 * @since  1.0
	 */
	protected $fixture;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setUp()
	{
		$this->fixture = AbstractRegistryFormat::getInstance('Yaml');
	}

	/**
	 * Test the __construct method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testConstruct()
	{
		$this->assertAttributeInstanceOf('Symfony\Component\Yaml\Parser', 'parser', $this->fixture);
		$this->assertAttributeInstanceOf('Symfony\Component\Yaml\Dumper', 'dumper', $this->fixture);
	}

	/**
	 * Test the objectToString method with an object as input.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testObjectToStringWithObject()
	{
		$object = (object) array(
			'foo' => 'bar',
			'quoted' => '"stringwithquotes"',
			'booleantrue' => true,
			'booleanfalse' => false,
			'numericint' => 42,
			'numericfloat' => 3.1415,
			'section' => (object) array('key' => 'value'),
			'array' => (object) array('nestedarray' => (object) array('test1' => 'value1'))
		);

		$yaml = 'foo: bar
quoted: \'"stringwithquotes"\'
booleantrue: true
booleanfalse: false
numericint: 42
numericfloat: 3.1415
section:
    key: value
array:
    nestedarray: { test1: value1 }
';

		$this->assertEquals(
			str_replace(array("\n", "\r"), '', trim($this->fixture->objectToString($object))),
			str_replace(array("\n", "\r"), '', trim($yaml))
		);
	}

	/**
	 * Test the objectToString method with an array as input.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testObjectToStringWithArray()
	{
		$object = array(
			'foo' => 'bar',
			'quoted' => '"stringwithquotes"',
			'booleantrue' => true,
			'booleanfalse' => false,
			'numericint' => 42,
			'numericfloat' => 3.1415,
			'section' => array('key' => 'value'),
			'array' => array('nestedarray' => array('test1' => 'value1'))
		);

		$yaml = 'foo: bar
quoted: \'"stringwithquotes"\'
booleantrue: true
booleanfalse: false
numericint: 42
numericfloat: 3.1415
section:
    key: value
array:
    nestedarray: { test1: value1 }
';

		$this->assertEquals(
			str_replace(array("\n", "\r"), '', trim($this->fixture->objectToString($object))),
			str_replace(array("\n", "\r"), '', trim($yaml))
		);
	}

	/**
	 * Test the stringToObject method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testStringToObject()
	{
		$object = (object) array(
			'foo' => 'bar',
			'quoted' => '"stringwithquotes"',
			'booleantrue' => true,
			'booleanfalse' => false,
			'numericint' => 42,
			'numericfloat' => 3.1415,
			'section' => (object) array('key' => 'value'),
			'array' => (object) array('nestedarray' => (object) array('test1' => 'value1'))
		);

		$yaml = 'foo: bar
quoted: \'"stringwithquotes"\'
booleantrue: true
booleanfalse: false
numericint: 42
numericfloat: 3.1415
section:
    key: value
array:
    nestedarray: { test1: value1 }
';
		$this->assertEquals($object, $this->fixture->stringToObject($yaml));
	}
}
