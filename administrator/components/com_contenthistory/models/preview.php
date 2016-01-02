<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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

		// Access check
		if (!JFactory::getUser()->authorise('core.edit', $contentTypeTable->type_alias . '.' . (int) $table->ucm_item_id))
		{
			$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		// Good to go, finish processing the data
		$result = new stdClass;
		$result->save_date = $table->save_date;
		$result->version_note = $table->version_note;
		$result->data = ContenthistoryHelper::prepareData($table);

		return $result;
	}
}
