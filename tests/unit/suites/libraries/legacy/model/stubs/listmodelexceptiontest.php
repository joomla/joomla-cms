<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	 * throws an Exception for testing purposes
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  array  An array of results.
	 *
	 * @throws  RuntimeException
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		throw new RuntimeException;
	}
}
