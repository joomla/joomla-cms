<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Registry\Tests;

use Joomla\Registry\AbstractRegistryFormat;

/**
 * Test class for AbstractRegistryFormat.
 *
 * @since  1.0
 */
class AbstractRegistryFormatTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Data provider for testGetInstance
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestGetInstance()
	{
		return array(
			array('Xml'),
			array('Ini'),
			array('Json'),
			array('Php'),
			array('Yaml')
		);
	}

	/**
	 * Test the AbstractRegistryFormat::getInstance method.
	 *
	 * @param   string  $format  The format to load
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestGetInstance
	 * @since         1.0
	 */
	public function testGetInstance($format)
	{
		$class = '\\Joomla\\Registry\\Format\\' . $format;

		$object = AbstractRegistryFormat::getInstance($format);
		$this->assertThat(
			$object instanceof $class,
			$this->isTrue()
		);
	}

	/**
	 * Test getInstance with a non-existent format.
	 *
	 * @return  void
	 *
	 * @expectedException  \InvalidArgumentException
	 * @since              1.0
	 */
	public function testGetInstanceNonExistent()
	{
		AbstractRegistryFormat::getInstance('SQL');
	}
}
