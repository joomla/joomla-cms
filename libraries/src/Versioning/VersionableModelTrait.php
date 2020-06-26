<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Versioning;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

/**
 * Defines the trait for a Versionable Model Class.
 *
 * @since  4.0.0
 */
trait VersionableModelTrait
{
	/**
	 * Method to load a row for editing from the version history table.
	 *
	 * @param   integer  $version_id  Key to the version history table.
	 * @param   Table    $table       Content table object being loaded.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   4.0.0
	 */
	public function loadHistory($version_id, Table &$table)
	{
		// Only attempt to check the row in if it exists, otherwise do an early exit.
		if (!$version_id)
		{
			return false;
		}

		// Get an instance of the row to checkout.
		$historyTable = Table::getInstance('Contenthistory');

		if (!$historyTable->load($version_id))
		{
			$this->setError($historyTable->getError());

			return false;
		}

		$typeAlias = explode('.', $historyTable->item_id);
		array_pop($typeAlias);

		$rowArray = ArrayHelper::fromObject(json_decode($historyTable->version_data));

		if (implode('.', $typeAlias) != $this->typeAlias)
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_HISTORY_ID_MISMATCH'));

			$key = $table->getKeyName();

			if (isset($rowArray[$key]))
			{
				$table->checkIn($rowArray[$key]);
			}

			return false;
		}

		$this->setState('save_date', $historyTable->save_date);
		$this->setState('version_note', $historyTable->version_note);

		return $table->bind($rowArray);
	}
}
