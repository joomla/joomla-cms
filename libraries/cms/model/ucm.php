<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

abstract class JModelUcm extends JModelCollection
{
	protected $typeAlias;
	/**
	 * Method to load a row for editing from the version history table.
	 *
	 * @param   integer $version_id Key to the version history table.
	 * @param   JTable  $table      Content table object being loaded.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 * @throws ErrorException
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
				throw new ErrorException($historyTable->getError());
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

				throw new ErrorException(JText::_('JLIB_APPLICATION_ERROR_HISTORY_ID_MISMATCH'));
			}
		}

		$this->setState('save_date', $historyTable->save_date);
		$this->setState('version_note', $historyTable->version_note);

		return $table->bind($rowArray);
	}
} 