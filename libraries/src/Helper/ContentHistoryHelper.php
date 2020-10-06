<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\Versioning;

/**
 * Versions helper class, provides methods to perform various tasks relevant
 * versioning of content.
 *
 * @since  3.2
 */
class ContentHistoryHelper extends CMSHelper
{
	/**
	 * Alias for storing type in versions table
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = null;

	/**
	 * Constructor
	 *
	 * @param   string  $typeAlias  The type of content to be versioned (for example, 'com_content.article').
	 *
	 * @since   3.2
	 */
	public function __construct($typeAlias = null)
	{
		$this->typeAlias = $typeAlias;
	}

	/**
	 * Method to delete the history for an item.
	 *
	 * @param   Table  $table  Table object being versioned
	 *
	 * @return  boolean  true on success, otherwise false.
	 *
	 * @since   3.2
	 */
	public function deleteHistory($table)
	{
		$id = (int) $table->{$table->getKeyName()};

		return Versioning::delete($table->typeAlias, $id);
	}

	/**
	 * Method to get a list of available versions of this item.
	 *
	 * @param   integer  $typeId  Type id for this component item.
	 * @param   mixed    $id      Primary key of row to get history for.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   3.2
	 */
	public function getHistory($typeId, $id)
	{
		// Cast as integer until method is typehinted.
		$typeId = (int) $typeId;
		$id     = (int) $id;

		$contentType = Table::getInstance('ContentType');
		$contentType->load(['type_id' => $typeId]);

		return Versioning::get($contentType->type_alias, $id);
	}

	/**
	 * Method to save a version snapshot to the content history table.
	 *
	 * @param   Table  $table  Table object being versioned
	 *
	 * @return  boolean  True on success, otherwise false.
	 *
	 * @since   3.2
	 */
	public function store($table)
	{
		$dataObject = $this->getDataObject($table);
		$key        = $table->getKeyName();

		$input = Factory::getApplication()->input;
		$data  = $input->get('jform', [], 'array');
		$versionNote  = '';

		if (isset($data['version_note']))
		{
			$versionNote = InputFilter::getInstance()->clean($data['version_note'], 'STRING');
		}

		return Versioning::store($table->typeAlias, $table->$key, $dataObject, $versionNote);
	}
}
