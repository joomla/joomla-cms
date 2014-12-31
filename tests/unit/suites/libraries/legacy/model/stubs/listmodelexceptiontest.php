<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Stub to test JModelList.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @since       12.3
 */
class ListModelExceptionTest extends JModelList
{
	/**
	 * throws a Exception for testing purposes
	 *
	 * @throws RuntimeException
	 *
	 * @return void
	 */
	protected function _getList()
	{
		throw new RuntimeException;
	}
}
