<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class to mock JAccessRules.
 *
 * @package  Joomla.Test
 * @since    3.0.0
 */
class TestMockRules
{
	/**
	 * Creates an instance of the mock JAccessRules object.
	 *
	 * @param   PHPUnit_Framework_TestCase  $test  A test object.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   1.7.3
	 */
	public static function create($test)
	{
		// Mock all the public methods.
		$methods = array(
			'allow',
		);

		// Build the mock object.
		$mockObject = $test->getMockBuilder('JAccessRules')
					->setMethods($methods)
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();

		$test->assignMockCallbacks(
			$mockObject,
			array(
				'allow' => array(get_called_class(), 'mockAllow'),
			)
		);

		return $mockObject;
	}

	/**
	 * Mocking the allow method.
	 *
	 * @param   string   $action    The action.
	 * @param   integer  $identity  The identity ID.
	 *
	 * @return  mixed  Boolean or null.
	 *
	 * @since   1.7.3
	 */
	public static function mockAllow($action, $identity)
	{
		switch ($action)
		{
			case 'run':
				if ($identity == 0)
				{
					return null;
				}
				else
				{
					// Odds return true, evens false.
					return (boolean) ($identity % 2);
				}

			case 'walk':
				if ($identity == 0)
				{
					return null;
				}
				else
				{
					// Odds return false, evens true.
					return (boolean) (1 - ($identity % 2));
				}

			default:
				return null;
		}
	}
}
