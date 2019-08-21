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
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

/**
 * Methods supporting a list of article records.
 *
 * @since  3.7.0
 */
class AssociationModel extends ListModel
{
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A \JForm object on success, false on failure
	 *
	 * @since  3.7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_associations.association', 'association', array('control' => 'jform', 'load_data' => $loadData));

		return !empty($form) ? $form : false;
	}

	/**
	 * Method to get the history version ids of a parent.
	 *
	 * @param   integer  $parentId       Id of the parent
	 * @param   integer  $targetId       Id of a child
	 * @param   string   $extensionName  The extension name with com_
	 * @param   string   $typeName       The item type
	 * @param   integer  $typeId         the content type id
	 *
	 * @return  array    Array containing two version history ids
	 */
	public function getParentCompareValues($parentId, $targetId, $extensionName, $typeName, $typeId)
	{

		$context = ($typeName === 'category')
			? 'com_categories.item'
			: $extensionName . '.item';

		$db = Factory::getDbo();
		$parentQuery = $db->getQuery(true)
			->select($db->quoteName('parent_date'))
			->from($db->quoteName('#__associations'))
			->where(
				[
					$db->quoteName('id') . ' = :id',
					$db->quoteName('context') . ' = :context'
				]
			)
			->bind(':id', $parentId, ParameterType::INTEGER)
			->bind(':context', $context);
		$latestParentDate = $db->setQuery($parentQuery)->loadResult();

		$latestVersionQuery = $db->getQuery(true)
			->select($db->quoteName('version_id'))
			->from($db->quoteName('#__ucm_history'))
			->where(
				[
					$db->quoteName('ucm_item_id') . ' = :ucm_item_id',
					$db->quoteName('ucm_type_id') . ' = :ucm_type_id',
					$db->quoteName('save_date') . ' = :save_date'
				]
			)
			->bind(':ucm_item_id', $parentId, ParameterType::INTEGER)
			->bind(':ucm_type_id', $typeId, ParameterType::INTEGER)
			->bind(':save_date', $latestParentDate);
		$latestVersionId = $db->setQuery($latestVersionQuery)->loadResult();

		$childQuery = $db->getQuery(true)
			->select($db->quoteName('parent_date'))
			->from($db->quoteName('#__associations'))
			->where(
				[
					$db->quoteName('id') . ' = :id',
					$db->quoteName('parent_id') . ' = :parent_id',
					$db->quoteName('context') . ' = :context'
				]
			)
			->bind(':id', $targetId, ParameterType::INTEGER)
			->bind(':parent_id', $parentId, ParameterType::INTEGER)
			->bind(':context', $context);
		$childParentDate = $db->setQuery($childQuery)->loadResult();

		$olderVersionQuery = $db->getQuery(true)
			->select($db->quoteName('version_id'))
			->from($db->quoteName('#__ucm_history'))
			->where(
				[
					$db->quoteName('ucm_item_id') . ' = :ucm_item_id',
					$db->quoteName('ucm_type_id') . ' = :ucm_type_id',
					$db->quoteName('save_date') . ' = :save_date'
				]
			)
			->bind(':ucm_item_id', $parentId, ParameterType::INTEGER)
			->bind(':ucm_type_id', $typeId, ParameterType::INTEGER)
			->bind(':save_date', $childParentDate);
		$olderVersionId = $db->setQuery($olderVersionQuery)->loadResult();

		return [$latestVersionId, $olderVersionId];
	}
}
