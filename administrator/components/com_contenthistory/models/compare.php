<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContenthistoryHelper', JPATH_ADMINISTRATOR . '/components/com_contenthistory/helpers/contenthistory.php');

/**
 * Methods supporting a list of contenthistory records.
 *
 * @since  3.2
 */
class ContenthistoryModelCompare extends JModelItem
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
		$input = JFactory::getApplication()->input;

		/** @var JTableContenthistory $table1 */
		$table1 = JTable::getInstance('Contenthistory');

		/** @var JTableContenthistory $table2 */
		$table2 = JTable::getInstance('Contenthistory');

		$id1 = $input->getInt('id1');
		$id2 = $input->getInt('id2');

		if (!$id1 || \is_array($id1) || !$id2 || \is_array($id2))
		{
			$this->setError(\JText::_('COM_CONTENTHISTORY_ERROR_INVALID_ID'));

			return false;
		}

		$result = array();

		if ($table1->load($id1) && $table2->load($id2))
		{
			// Get the first history record's content type record so we can check ACL
			/** @var JTableContenttype $contentTypeTable */
			$contentTypeTable = JTable::getInstance('Contenttype');
			$ucmTypeId        = $table1->ucm_type_id;

			if (!$contentTypeTable->load($ucmTypeId))
			{
				$this->setError(\JText::_('COM_CONTENTHISTORY_ERROR_FAILED_LOADING_CONTENT_TYPE'));

				// Assume a failure to load the content type means broken data, abort mission
				return false;
			}

			$user = JFactory::getUser();

			// Access check
			if ($user->authorise('core.edit', $contentTypeTable->type_alias . '.' . (int) $table1->ucm_item_id) || $this->canEdit($table1))
			{
				$return = true;
			}
			else
			{
				$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));

				return false;
			}

			// All's well, process the records
			if ($return == true)
			{
				foreach (array($table1, $table2) as $table)
				{
					$object = new stdClass;
					$object->data = ContenthistoryHelper::prepareData($table);
					$object->version_note = $table->version_note;

					// Let's use custom calendars when present
					$object->save_date = JHtml::_('date', $table->save_date, JText::_('DATE_FORMAT_LC6'));

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
						if (property_exists($object->data, $dateProperty) && $object->data->$dateProperty->value != '0000-00-00 00:00:00')
						{
							$object->data->$dateProperty->value = JHtml::_('date', $object->data->$dateProperty->value, JText::_('DATE_FORMAT_LC6'));
						}
					}

					$result[] = $object;
				}

				return $result;
			}
		}

		$this->setError(\JText::_('COM_CONTENTHISTORY_ERROR_VERSION_NOT_FOUND'));

		return false;
	}

	/**
	 * Method to test whether a record is editable
	 *
	 * @param   JTableContenthistory  $record  A JTable object.
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
			$typeAlias = JFactory::getApplication()->input->get('type_alias');

			/** @var JTableContenttype $contentTypeTable */
			$contentTypeTable = JTable::getInstance('Contenttype', 'JTable');

			if ($contentTypeTable->getTypeId($typeAlias) == $record->ucm_type_id)
			{
				/**
				 * Make sure user has edit privileges for this content item. Note that we use edit permissions
				 * for the content item, not delete permissions for the content history row.
				 */
				$user   = JFactory::getUser();
				$result = $user->authorise('core.edit', $typeAlias . '.' . (int) $record->ucm_item_id);
			}

			// Finally try session (this catches edit.own case too)
			if (!$result)
			{
				$contentTypeTable->load($record->ucm_type_id);
				$typeEditables = (array) JFactory::getApplication()->getUserState(str_replace('.', '.edit.', $contentTypeTable->type_alias) . '.id');
				$result = in_array((int) $record->ucm_item_id, $typeEditables);
			}
		}

		return $result;
	}
}
