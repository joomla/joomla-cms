<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Registry\Tests\Format;

use Joomla\Registry\AbstractRegistryFormat;

/**
 * Test class for Php.
 *
 * @since  1.0
 */
class JRegistryFormatPHPTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test the Php::objectToString method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testObjectToString()
	{
		$class = AbstractRegistryFormat::getInstance('PHP');
		$options = array('class' => 'myClass');
		$object = new \stdClass;
		$object->foo = 'bar';
		$object->quoted = '"stringwithquotes"';
		$object->booleantrue = true;
		$object->booleanfalse = false;
		$object->numericint = 42;
		$object->numericfloat = 3.1415;

		// The PHP registry format does not support nested objects
		$object->section = new \stdClass;
		$object->section->key = 'value';
		$object->array = array('nestedarray' => array('test1' => 'value1'));

		$string = "<?php\n" .
			"class myClass {\n" .
			"\tpublic \$foo = 'bar';\n" .
			"\tpublic \$quoted = '\"stringwithquotes\"';\n" .
			"\tpublic \$booleantrue = '1';\n" .
			"\tpublic \$booleanfalse = '';\n" .
			"\tpublic \$numericint = '42';\n" .
			"\tpublic \$numericfloat = '3.1415';\n" .
			"\tpublic \$section = array(\"key\" => \"value\");\n" .
			"\tpublic \$array = array(\"nestedarray\" => array(\"test1\" => \"value1\"));\n" .
			"}\n?>";
		$this->assertThat(
			$class->objectToString($object, $options),
			$this->equalTo($string)
		);
	}

	/**
	 * Test the Php::stringToObject method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testStringToObject()
	{
		$class = AbstractRegistryFormat::getInstance('PHP');

		// This method is not implemented in the class. The test is to achieve 100% code coverage
		$this->assertTrue($class->stringToObject(''));
	}
}
