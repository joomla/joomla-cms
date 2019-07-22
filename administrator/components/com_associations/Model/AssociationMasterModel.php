<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Associations\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseModel;

/**
 * Methods supporting a list of article records.
 *
 * @since  4.0
 */
class AssociationMasterModel extends BaseModel
{

	/**
	 * Update the childs modified date of the master item from #__associations table.
	 *
	 * @param   integer  $childId   The id of the item that gets updated
	 * @param   integer  $masterId  The associated master item of the child item
	 * @param   string   $itemtype  The component item type
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  4.0
	 */
	public function update($childId, $masterId, $itemtype)
	{
		list($extensionName, $typeName) = explode('.', $itemtype, 2);

		$context = ($typeName === 'category')
			? 'com_categories.item'
			: $extensionName . '.item';
		$db      = Factory::getDbo();

		$subQuery = $db->getQuery(true)
			->select($db->quoteName('master_date'))
			->from($db->quoteName('#__associations'))
			->where($db->quoteName('id') . ' = ' . $db->quote($masterId))
			->where($db->quoteName('master_id') . ' = ' . $db->quote(0))
			->where($db->quoteName('context') . ' = ' . $db->quote($context));
		$masterModified = $db->setQuery($subQuery)->loadResult();

		$query = $db->getQuery(true)
			->update($db->quoteName('#__associations'))
			->set($db->quoteName('master_date') . ' = ' . $db->quote($masterModified))
			->where($db->quoteName('id') . ' = ' . $db->quote($childId))
			->where($db->quoteName('master_id') . ' = ' . $db->quote($masterId))
			->where($db->quoteName('context') . ' = ' . $db->quote($context));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (ExecutionFailureException $e)
		{
			return false;
		}

		return true;
	}
}
