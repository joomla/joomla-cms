<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Contenthistory\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Table\Table;
use Joomla\Component\Contenthistory\Administrator\Helper\ContenthistoryHelper;
use Joomla\CMS\Table\ContentHistory;
use Joomla\CMS\Table\ContentType;

/**
 * Methods supporting a list of contenthistory records.
 *
 * @since  3.2
 */
class CompareModel extends ItemModel
{
	/**
	 * Method to get a version history row.
	 *
	 * @return  array|boolean    On success, array of populated tables. False on failure.
	 *
	 * @since   3.2
	 */
	public function getItems()
	{
		$input = \JFactory::getApplication()->input;

		/** @var ContentHistory $table1 */
		$table1 = $this->getTable('ContentHistory');

		/** @var ContentHistory $table2 */
		$table2 = $this->getTable('ContentHistory');

		$id1 = $input->getInt('id1');
		$id2 = $input->getInt('id2');
		$result = array();

		if ($table1->load($id1) && $table2->load($id2))
		{
			// Get the first history record's content type record so we can check ACL
			/** @var ContentType $contentTypeTable */
			$contentTypeTable = $this->getTable('ContentType');
			$ucmTypeId        = $table1->ucm_type_id;

			if (!$contentTypeTable->load($ucmTypeId))
			{
				// Assume a failure to load the content type means broken data, abort mission
				return false;
			}

			$user = \JFactory::getUser();

			// Access check
			if ($user->authorise('core.edit', $contentTypeTable->type_alias . '.' . (int) $table1->ucm_item_id) || $this->canEdit($table1))
			{
				$return = true;
			}
			else
			{
				$this->setError(\JText::_('JERROR_ALERTNOAUTHOR'));

				return false;
			}

			// All's well, process the records
			if ($return == true)
			{
				$nullDate = $this->getDbo()->getNullDate();

				foreach (array($table1, $table2) as $table)
				{
					$object = new \stdClass;
					$object->data = ContenthistoryHelper::prepareData($table);
					$object->version_note = $table->version_note;

					// Let's use custom calendars when present
					$object->save_date = \JHtml::_('date', $table->save_date, 'Y-m-d H:i:s');

					$dateProperties = array (
						'modified_time',
						'created_time',
						'modified',
						'created',
						'checked_out_time',
						'publish_up',
						'publish_down',
					);

					foreach ($dateProperties as $dateProperty)
					{
						if (array_key_exists($dateProperty, $object->data) && $object->data->$dateProperty->value != $nullDate)
						{
							$object->data->$dateProperty->value = \JHtml::_('date', $object->data->$dateProperty->value, 'Y-m-d H:i:s');
						}
					}

					$result[] = $object;
				}

				return $result;
			}
		}

		return false;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table   A Table object
	 *
	 * @since   3.2
	 */
	public function getTable($type = 'Contenthistory', $prefix = 'Joomla\\CMS\\Table\\', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to test whether a record is editable
	 *
	 * @param   ContentHistory  $record  A \JTable object.
	 *
	 * @return  boolean  True if allowed to edit the record. Defaults to the permission set in the component.
	 *
	 * @since   3.6
	 */
	protected function canEdit($record)
	{
		$result = false;

		if (!empty($record->ucm_type_id))
		{
			// Check that the type id matches the type alias
			$typeAlias = \JFactory::getApplication()->input->get('type_alias');

			/** @var ContentType $contentTypeTable */
			$contentTypeTable = $this->getTable('ContentType');

			if ($contentTypeTable->getTypeId($typeAlias) == $record->ucm_type_id)
			{
				/**
				 * Make sure user has edit privileges for this content item. Note that we use edit permissions
				 * for the content item, not delete permissions for the content history row.
				 */
				$user   = \JFactory::getUser();
				$result = $user->authorise('core.edit', $typeAlias . '.' . (int) $record->ucm_item_id);
			}

			// Finally try session (this catches edit.own case too)
			if (!$result)
			{
				$contentTypeTable->load($record->ucm_type_id);
				$typeEditables = (array) \JFactory::getApplication()->getUserState(str_replace('.', '.edit.', $contentTypeTable->type_alias) . '.id');
				$result = in_array((int) $record->ucm_item_id, $typeEditables);
			}
		}

		return $result;
	}
}
