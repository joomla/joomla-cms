<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContenthistoryHelper', JPATH_ADMINISTRATOR . '/components/com_contenthistory/helpers/contenthistory.php');

/**
 * Methods supporting a list of contenthistory records.
 *
 * @since  3.2
 */
class ContenthistoryModelPreview extends JModelItem
{
	/**
	 * Method to get a version history row.
	 *
	 * @return  stdClass|boolean    On success, standard object with row data. False on failure.
	 *
	 * @since   3.2
	 */
	public function getItem()
	{
		/** @var JTableContenthistory $table */
		$table = JTable::getInstance('Contenthistory');
		$versionId = JFactory::getApplication()->input->getInt('version_id');

		if (!$table->load($versionId))
		{
			return false;
		}

		// Get the content type's record so we can check ACL
		/** @var JTableContenttype $contentTypeTable */
		$contentTypeTable = JTable::getInstance('Contenttype');

		if (!$contentTypeTable->load($table->ucm_type_id))
		{
			// Assume a failure to load the content type means broken data, abort mission
			return false;
		}

		$user = JFactory::getUser();

		// Access check
		if ($user->authorise('core.edit', $contentTypeTable->type_alias . '.' . (int) $table->ucm_item_id) || $this->canEdit($table))
		{
			$return = true;
		}
		else
		{
			$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		// Good to go, finish processing the data
		if ($return == true)
		{
			$result = new stdClass;
			$result->version_note = $table->version_note;
			$result->data = ContenthistoryHelper::prepareData($table);

			// Let's use custom calendars when present
			$result->save_date = JHtml::_('date', $table->save_date, 'Y-m-d H:i:s');

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
				if (array_key_exists($dateProperty, $result->data) && $result->data->$dateProperty->value != '0000-00-00 00:00:00')
				{
					$result->data->$dateProperty->value = JHtml::_('date', $result->data->$dateProperty->value, 'Y-m-d H:i:s');
				}
			}

			return $result;
		}
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
