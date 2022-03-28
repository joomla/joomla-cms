<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Controller
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Concrete class extending JControllerBase.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Controller
 * @since       3.0.0
 */
class BaseController extends JControllerBase
{
	/**
	 * Method to execute the controller.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		return 'base';
	}
}
