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
use Joomla\Database\ParameterType;

/**
 * Methods supporting a list of article records.
 *
 * @since  4.0
 */
class DefaultAssocLangModel extends BaseModel
{

	/**
	 * Update the childs modified date of the parent from #__associations table.
	 *
	 * @param   integer  $childId   The id of the item that gets updated
	 * @param   integer  $parentId  The associated parent of the child item
	 * @param   string   $itemtype  The component item type
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  4.0
	 */
	public function update($childId, $parentId, $itemtype)
	{
		list($extensionName, $typeName) = explode('.', $itemtype, 2);

		$context = ($typeName === 'category')
			? 'com_categories.item'
			: $extensionName . '.item';
		$db      = Factory::getDbo();

		$subQuery = $db->getQuery(true)
			->select($db->quoteName('parent_date'))
			->from($db->quoteName('#__associations'))
			->where(
				[
					$db->quoteName('id') . ' = :id',
					$db->quoteName('parent_id') . ' = ' . $db->quote(0),
					$db->quoteName('context') . ' = :context'
				]
			)
			->bind(':id', $parentId, ParameterType::INTEGER)
			->bind(':context', $context);
		$parentModified = $db->setQuery($subQuery)->loadResult();
		$parentModifiedSql = Factory::getDate($parentModified)->toSql();

		$query = $db->getQuery(true)
			->update($db->quoteName('#__associations'))
			->set($db->quoteName('parent_date') . ' = :parent_date')
			->where(
				[
					$db->quoteName('id') . ' = :id',
					$db->quoteName('parent_id') . ' = :parent_id',
					$db->quoteName('context') . ' = :context'
				]
			)
			->bind(':parent_date', $parentModifiedSql)
			->bind(':id', $childId, ParameterType::INTEGER)
			->bind(':parent_id', $parentId, ParameterType::INTEGER)
			->bind(':context', $context);
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
