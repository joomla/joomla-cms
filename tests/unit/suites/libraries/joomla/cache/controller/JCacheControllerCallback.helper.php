<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * A testCallbackController test class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @since       1.7.0
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
