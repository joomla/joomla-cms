<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class to mock JApplicationCli.
 *
 * @package  Joomla.Test
 * @since    3.0.1
 */
class TestMockApplicationCli extends TestMockApplicationBase
{
	/**
	 * Gets the methods of the JApplicationCli object.
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public static function getMethods()
	{
		// Collect all the relevant methods in JApplicationCli.
		$methods = array(
			'get',
			'execute',
			'loadConfiguration',
			'out',
			'in',
			'set',
		);

		return array_merge($methods, parent::getMethods());
	}

	/**
	 * Creates and instance of the mock JApplicationCli object.
	 *
	 * @param   TestCase  $test     A test object.
	 * @param   array     $options  A set of options to configure the mock.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   3.0.1
	 */
	public static function create($test, $options = array())
	{
		// Collect all the relevant methods in JApplicationCli.
		$methods = self::getMethods();

		// Build the mock object & allow Call to original constructor.
		$mockObject = $test->getMockBuilder('JApplicationCli')
					->setMethods($methods)
					->setConstructorArgs(array())
					->setMockClassName('')
					->getMock();

		$mockObject = self::addBehaviours($test, $mockObject, $options);

		return $mockObject;
	}
}
