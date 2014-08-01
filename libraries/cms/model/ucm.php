<?php
/**
 * @version   0.0.2
 * @package   Babel-U-Lib
 * @copyright Copyright (C) 2011 - 2014 Mathew Lenning. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @author    Mathew Lenning - http://babel-university.com/
 */

// No direct access
defined('_JEXEC') or die;

abstract class Babelu_libModelUcm extends Babelu_libModelCollection
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

				throw new ErrorException(JText::_('BABELU_LIB_MODEL_ERROR_HISTORY_ID_MISMATCH'));
			}
		}

		$this->setState('save_date', $historyTable->save_date);
		$this->setState('version_note', $historyTable->version_note);

		return $table->bind($rowArray);
	}
} 