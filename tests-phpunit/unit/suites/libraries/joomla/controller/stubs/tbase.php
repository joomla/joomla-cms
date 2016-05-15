<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Concrete class extending JControllerBase.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Controller
 * @since       12.1
 */
class BaseController extends JControllerBase
{
	/**
	 * Method to execute the controller.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		return 'base';
	}
}
