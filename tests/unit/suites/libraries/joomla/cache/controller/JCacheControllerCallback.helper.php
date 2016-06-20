<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * A testCallbackController test class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @since       11.1
 */
class TestCallbackController
{

	/**
	 * Test...
	 *
	 * @param   mixed  $arg1  Nr. 1
	 * @param   mixed  $arg2  Nr. 2
	 *
	 * @return mixed
	 */
	public function instanceCallback($arg1, $arg2)
	{
		echo $arg1;

		return $arg2;
	}

	/**
	 * Test...
	 *
	 * @param   mixed  $arg1  Nr. 1
	 * @param   mixed  $arg2  Nr. 2
	 *
	 * @return mixed
	 */
	public static function staticCallback($arg1, $arg2)
	{
		echo $arg1;

		return $arg2;
	}

}

/**
 * Test...
 *
 * @param   mixed  $arg1  Nr. 1
 * @param   mixed  $arg2  Nr. 2
 *
 * @return mixed
 */
function testCallbackControllerFunc($arg1, $arg2)
{
	echo $arg1;

	return $arg2;
}
