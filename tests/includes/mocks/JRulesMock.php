<?php
/**
 * @package    Joomla.UnitTest
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

/**
 * Mock class for JRules.
 *
 * @package  Joomla.UnitTest
 * @since    11.3
 */
class JRulesGlobalMock
{
	/**
	 * Creates an instance of the mock JDatabase object.
	 *
	 * @param   object  $test    A test object.
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	public static function create($test)
	{
		// Mock all the public methods.
		$methods = array(
			'allow',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JRules',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			false
		);

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
	 * @param   string  $action  The action.
	 *
	 * @return  mixed  Boolean or null.
	 *
	 * @since   11.3
	 */
	public function mockAllow($action, $identity)
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
				return false;

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
