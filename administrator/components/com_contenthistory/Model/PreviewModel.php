<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Contenthistory\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Table\Table;
use Joomla\Component\Contenthistory\Administrator\Helper\ContenthistoryHelper;

/**
 * Methods supporting a list of contenthistory records.
 *
 * @since  3.2
 */
class PreviewModel extends ItemModel
{
	/**
	 * Method to get a version history row.
	 *
	 * @return  \stdClass|boolean    On success, standard object with row data. False on failure.
	 *
	 * @since   3.2
	 */
	public function getItem()
	{
		/** @var \Joomla\CMS\Table\ContentHistory $table */
		$table = $this->getTable('Contenthistory');
		$versionId = \JFactory::getApplication()->input->getInt('version_id');

		if (!$table->load($versionId))
		{
			return false;
		}

		// Get the content type's record so we can check ACL
		/** @var \Joomla\CMS\Table\ContentType $contentTypeTable */
		$contentTypeTable = $this->getTable('Contenttype');

		if (!$contentTypeTable->load($table->ucm_type_id))
		{
			// Assume a failure to load the content type means broken data, abort mission
			return false;
		}

		$user = \JFactory::getUser();

		// Access check
		if ($user->authorise('core.edit', $contentTypeTable->type_alias . '.' . (int) $table->ucm_item_id) || $this->canEdit($table))
		{
			$return = true;
		}
		else
		{
			$this->setError(\JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		// Good to go, finish processing the data
		if ($return == true)
		{
			$result = new \stdClass;
			$result->version_note = $table->version_note;
			$result->data = ContenthistoryHelper::prepareData($table);

			// Let's use custom calendars when present
			$result->save_date = \JHtml::_('date', $table->save_date, 'Y-m-d H:i:s');

			$dateProperties = array (
				'modified_time',
				'created_time',
				'modified',
				'created',
				'checked_out_time',
				'publish_up',
				'publish_down',
			);

			$nullDate = $this->getDbo()->getNullDate();

			foreach ($dateProperties as $dateProperty)
			{
				if (array_key_exists($dateProperty, $result->data) && $result->data->$dateProperty->value != $nullDate)
				{
					$result->data->$dateProperty->value = \JHtml::_('date', $result->data->$dateProperty->value, 'Y-m-d H:i:s');
				}
			}

			return $result;
		}
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
	 * @param   \Joomla\CMS\Table\ContentHistory  $record  A Table object.
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

			/** @var \Joomla\CMS\Table\ContentType $contentTypeTable */
			$contentTypeTable = $this->getTable('Contenttype');

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
