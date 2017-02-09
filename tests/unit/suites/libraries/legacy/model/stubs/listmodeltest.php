<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
class ListModelTest extends JModelList
{
	public function getListQuery()
	{
		$query = parent::getListQuery();

		$query->select('id')
			->from('jos_dbtest');

		return $query;
	}
}
