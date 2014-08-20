<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base Cms Model Class for UCM data
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       3.4
 */
abstract class JModelUcm extends JModelCollection
{
	/**
	 * The type alias for the UCM table.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $typeAlias;

	/**
	 * Method to load a row for editing from the version history table.
	 *
	 * @param   integer $version_id Key to the version history table.
	 * @param   JTable  $table      Content table object being loaded.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @throws  RuntimeException
	 * @since   12.2
	 */
	public function loadHistory($version_id, JTable $table)
	{
		// Get an instance of the row to checkout.
		$historyTable = JTable::getInstance('Contenthistory');
		$rowArray = array();

		// Only attempt to check the row in if it exists.
		if ($version_id)
		{
			if (!$historyTable->load($version_id))
			{
				throw new RuntimeException($historyTable->getError());
			}

			$rowArray = JArrayHelper::fromObject(json_decode($historyTable->version_data));

			$typeId = JTable::getInstance('Contenttype')->getTypeId($this->typeAlias);

			if ($historyTable->ucm_type_id != $typeId)
			{
				$key = $table->getKeyName();

				if (isset($rowArray[$key]))
				{
					$table->checkIn($rowArray[$key]);
				}

				throw new RuntimeException(JText::_($this->text_prefix . '_LIB_MODEL_ERROR_HISTORY_ID_MISMATCH'));
			}
		}

		$this->setState('save_date', $historyTable->save_date);
		$this->setState('version_note', $historyTable->version_note);

		return $table->bind($rowArray);
	}
} 