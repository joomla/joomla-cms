<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
		$result = array();

		if ($table1->load($id1) && $table2->load($id2))
		{
			// Get the first history record's content type record so we can check ACL
			/** @var JTableContenttype $contentTypeTable */
			$contentTypeTable = JTable::getInstance('Contenttype');
			$ucmTypeId        = $table1->ucm_type_id;

			if (!$contentTypeTable->load($ucmTypeId))
			{
				// Assume a failure to load the content type means broken data, abort mission
				return false;
			}

			// Access check
			if (!JFactory::getUser()->authorise('core.edit', $contentTypeTable->type_alias . '.' . (int) $table1->ucm_item_id))
			{
				$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));

				return false;
			}

			// All's well, process the records
			foreach (array($table1, $table2) as $table)
			{
				$object = new stdClass;
				$object->data = ContenthistoryHelper::prepareData($table);
				$object->version_note = $table->version_note;
				$object->save_date = $table->save_date;
				$result[] = $object;
			}

			return $result;
		}

		return false;
	}
}
