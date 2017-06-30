<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class to mock the DI Container.
 *
 * @package  Joomla.Test
 * @since    __DEPLOY_VERSION__
 */
class TestMockContainer
{
	/**
	 * Creates and instance of the mock DI Container object.
	 *
	 * @param   PHPUnit_Framework_TestCase  $test  A test object.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function create($test)
	{
		// Collect all the relevant methods in JDatabase.
		$methods = array(
			'has',
			'exists',
			'get',
		);

		// Create the mock.
		$mockObject = $test->getMockBuilder(\Joomla\DI\Container::class)
					->setMethods($methods)
					->getMock();

		// @Todo mock the container
		$container = require JPATH_LIBRARIES . '/container.php';

		$test->assignMockCallbacks(
			$mockObject,
			array(
				'has'    => array($container, 'has'),
				'exists' => array($container, 'exists'),
				'get'    => array($container, 'get'),
			)
		);

		return $mockObject;
	}
}
