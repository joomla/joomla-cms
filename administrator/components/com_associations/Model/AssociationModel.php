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
	 * Method to get the history version ids of a master item.
	 *
	 * @param   integer  $masterId       Id of the master item
	 * @param   integer  $targetId       Id of an child item
	 * @param   string   $extensionName  The extension name with com_
	 * @param   string   $typeName       The item type
	 * @param   integer  $typeId         the content type id
	 *
	 * @return  array    Array containing two version history ids
	 */
	public function getMasterCompareValues($masterId, $targetId, $extensionName, $typeName, $typeId)
	{

		$context = ($typeName === 'category')
			? 'com_categories.item'
			: $extensionName . '.item';

		$db = Factory::getDbo();
		$masterQuery = $db->getQuery(true)
			->select($db->quoteName('master_date'))
			->from($db->quoteName('#__associations'))
			->where($db->quoteName('id') . ' = ' . $db->quote($masterId))
			->where($db->quoteName('context') . ' = ' . $db->quote($context));
		$latestMasterDate = $db->setQuery($masterQuery)->loadResult();

		$latestVersionQuery = $db->getQuery(true)
			->select($db->quoteName('version_id'))
			->from($db->quoteName('#__ucm_history'))
			->where($db->quoteName('ucm_item_id') . ' = ' . $db->quote($masterId))
			->where($db->quoteName('ucm_type_id') . ' = ' . $db->quote($typeId))
			->where($db->quoteName('save_date') . ' = ' . $db->quote($latestMasterDate));
		$latestVersionId = $db->setQuery($latestVersionQuery)->loadResult();

		$childQuery = $db->getQuery(true)
			->select($db->quoteName('master_date'))
			->from($db->quoteName('#__associations'))
			->where($db->quoteName('id') . ' = ' . $db->quote($targetId))
			->where($db->quoteName('master_id') . ' = ' . $db->quote($masterId))
			->where($db->quoteName('context') . ' = ' . $db->quote($context));
		$childMasterDate = $db->setQuery($childQuery)->loadResult();

		$olderVersionQuery = $db->getQuery(true)
			->select($db->quoteName('version_id'))
			->from($db->quoteName('#__ucm_history'))
			->where($db->quoteName('ucm_item_id') . ' = ' . $db->quote($masterId))
			->where($db->quoteName('ucm_type_id') . ' = ' . $db->quote($typeId))
			->where($db->quoteName('save_date') . ' = ' . $db->quote($childMasterDate));
		$olderVersionId = $db->setQuery($olderVersionQuery)->loadResult();

		return [$latestVersionId, $olderVersionId];
	}
}
