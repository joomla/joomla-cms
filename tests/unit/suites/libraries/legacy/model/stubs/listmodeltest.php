<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Stub to test JModelList.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @since       3.1.4
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
