<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test stub controller.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       3.1.4
 */
class MyTestControllerFoo extends JControllerBase
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @since   3.0.0
	 * @throws  LogicException
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		return true;
	}
}
