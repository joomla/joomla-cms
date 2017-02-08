<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Content History table.
 *
 * @since  3.2
 */
class JTableContenthistory extends JTable
{
	/**
	 * Array of object fields to unset from the data object before calculating SHA1 hash. This allows us to detect a meaningful change
	 * in the database row using the hash. This can be read from the #__content_types content_history_options column.
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $ignoreChanges = array();

	/**
	 * Array of object fields to convert to integers before calculating SHA1 hash. Some values are stored differently
	 * when an item is created than when the item is changed and saved. This works around that issue.
	 * This can be read from the #__content_types content_history_options column.
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $convertToInt = array();

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 *
	 * @since   3.1
	 */
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__ucm_history', 'version_id', $db);
		$this->ignoreChanges = array(
			'modified_by',
			'modified_user_id',
			'modified',
			'modified_time',
			'checked_out',
			'checked_out_time',
			'version',
			'hits',
			'path',
		);
		$this->convertToInt = array('publish_up', 'publish_down', 'ordering', 'featured');
	}

	/**
	 * Overrides JTable::store to set modified hash, user id, and save date.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function store($updateNulls = false)
	{
		$this->set('character_count', strlen($this->get('version_data')));
		$typeTable = JTable::getInstance('Contenttype');
		$typeTable->load($this->ucm_type_id);

		if (!isset($this->sha1_hash))
		{
			$this->set('sha1_hash', $this->getSha1($this->get('version_data'), $typeTable));
		}

		// Modify author and date only when not toggling Keep Forever
		if (is_null($this->get('keep_forever')))
		{
			$this->set('editor_user_id', JFactory::getUser()->id);
			$this->set('save_date', JFactory::getDate()->toSql());
		}

		return parent::store($updateNulls);
	}

	/**
	 * Utility method to get the hash after removing selected values. This lets us detect changes other than
	 * modified date (which will change on every save).
	 *
	 * @param   mixed              $jsonData   Either an object or a string with json-encoded data
	 * @param   JTableContenttype  $typeTable  Table object with data for this content type
	 *
	 * @return  string  SHA1 hash on success. Empty string on failure.
	 *
	 * @since   3.2
	 */
	public function getSha1($jsonData, JTableContenttype $typeTable)
	{
		$object = is_object($jsonData) ? $jsonData : json_decode($jsonData);

		if (isset($typeTable->content_history_options) && is_object(json_decode($typeTable->content_history_options)))
		{
			$options = json_decode($typeTable->content_history_options);
			$this->ignoreChanges = isset($options->ignoreChanges) ? $options->ignoreChanges : $this->ignoreChanges;
			$this->convertToInt = isset($options->convertToInt) ? $options->convertToInt : $this->convertToInt;
		}

		foreach ($this->ignoreChanges as $remove)
		{
			if (property_exists($object, $remove))
			{
				unset($object->$remove);
			}
		}

		// Convert integers, booleans, and nulls to strings to get a consistent hash value
		foreach ($object as $name => $value)
		{
			if (is_object($value))
			{
				// Go one level down for JSON column values
				foreach ($value as $subName => $subValue)
				{
					$object->$subName = (is_int($subValue) || is_bool($subValue) || is_null($subValue)) ? (string) $subValue : $subValue;
				}
			}
			else
			{
				$object->$name = (is_int($value) || is_bool($value) || is_null($value)) ? (string) $value : $value;
			}
		}

		// Work around empty values
		foreach ($this->convertToInt as $convert)
		{
			if (isset($object->$convert))
			{
				$object->$convert = (int) $object->$convert;
			}
		}

		if (isset($object->review_time))
		{
			$object->review_time = (int) $object->review_time;
		}

		return sha1(json_encode($object));
	}

	/**
	 * Utility method to get a matching row based on the hash value and id columns.
	 * This lets us check to make sure we don't save duplicate versions.
	 *
	 * @return  string  SHA1 hash on success. Empty string on failure.
	 *
	 * @since   3.2
	 */
	public function getHashMatch()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__ucm_history'))
			->where($db->quoteName('ucm_item_id') . ' = ' . (int) $this->get('ucm_item_id'))
			->where($db->quoteName('ucm_type_id') . ' = ' . (int) $this->get('ucm_type_id'))
			->where($db->quoteName('sha1_hash') . ' = ' . $db->quote($this->get('sha1_hash')));
		$db->setQuery($query, 0, 1);

		return $db->loadObject();
	}

	/**
	 * Utility method to remove the oldest versions of an item, saving only the most recent versions.
	 *
	 * @param   integer  $maxVersions  The maximum number of versions to save. All others will be deleted.
	 *
	 * @return  boolean   true on success, false on failure.
	 *
	 * @since   3.2
	 */
	public function deleteOldVersions($maxVersions)
	{
		$result = true;

		// Get the list of version_id values we want to save
		$db = $this->_db;
		$query = $db->getQuery(true);
		$query->select($db->quoteName('version_id'))
			->from($db->quoteName('#__ucm_history'))
			->where($db->quoteName('ucm_item_id') . ' = ' . (int) $this->get('ucm_item_id'))
			->where($db->quoteName('ucm_type_id') . ' = ' . (int) $this->get('ucm_type_id'))
			->where($db->quoteName('keep_forever') . ' != 1')
			->order($db->quoteName('save_date') . ' DESC ');
		$db->setQuery($query, 0, (int) $maxVersions);
		$idsToSave = $db->loadColumn(0);

		// Don't process delete query unless we have at least the maximum allowed versions
		if (count($idsToSave) == (int) $maxVersions)
		{
			// Delete any rows not in our list and and not flagged to keep forever.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__ucm_history'))
				->where($db->quoteName('ucm_item_id') . ' = ' . (int) $this->get('ucm_item_id'))
				->where($db->quoteName('ucm_type_id') . ' = ' . (int) $this->get('ucm_type_id'))
				->where($db->quoteName('version_id') . ' NOT IN (' . implode(',', $idsToSave) . ')')
				->where($db->quoteName('keep_forever') . ' != 1');
			$db->setQuery($query);
			$result = (boolean) $db->execute();
		}

		return $result;
	}
}
