<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content History table.
 *
 * @package     Joomla.Libraries
 * @subpackage  Table
 * @since       3.2
 */
class JTableContenthistory extends JTable
{
	/**
	 * Array of object fields to unset from the data object before calculating SHA1 hash. This allows us to detect a meaningful change
	 * in the database row using the hash.
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $ignoreChanges = array();

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 *
	 * @since   3.1
	 */
	public function __construct($db)
	{
		parent::__construct('#__ucm_history', 'version_id', $db);
		$this->ignoreChanges = array('modified', 'modified_time', 'checked_out_time', 'version', 'hits');
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

		if (!isset($this->sha1_hash))
		{
			$this->set('sha1_hash', $this->getSha1($this->get('version_data')));
		}

		$this->set('editor_user_id', JFactory::getUser()->id);
		$this->set('save_date', JFactory::getDate()->toSql());

		return parent::store($updateNulls);
	}

	/**
	 * Utility method to get the hash after removing selected values. This lets us detect changes other than
	 * modified date (which will change on every save).
	 *
	 * @param   mixed  $jsonData  Either an object or a string with json-encoded data
	 *
	 * @return  string  SHA1 hash on sucess. Empty string on failure.
	 *
	 * @since   3.2
	 */
	public function getSha1($jsonData)
	{
		$object = (is_object($jsonData)) ? $jsonData : json_decode($jsonData);

		foreach ($this->ignoreChanges as $remove)
		{
			if (isset($object->$remove))
			{
				unset($object->$remove);
			}
		}

		// Convert integers and booleans to strings to get a consistent hash value
		foreach ($object as $name => $value)
		{
			if (is_object($value))
			{
				// Go one level down for JSON column values
				foreach ($value as $subName => $subValue)
				{
					$object->$subName = (is_int($subValue) || is_bool($subValue)) ? (string) $subValue : $subValue;
				}
			}
			else
			{
				$object->$name = (is_int($value) || is_bool($value)) ? (string) $value : $value;
			}
		}

		// Work around empty publish_up, publish_down values
		if (isset($object->publish_down))
		{
			$object->publish_down = (int) $object->publish_down;
		}

		if (isset($object->publish_up))
		{
			$object->publish_up = (int) $object->publish_up;
		}

		return sha1(json_encode($object));
	}

	/**
	 * Utility method to get a matching row based on the hash value and id columns.
	 * This lets us check to make sure we don't save duplicate versions.
	 *
	 * @return  string  SHA1 hash on sucess. Empty string on failure.
	 *
	 * @since   3.2
	 */
	public function getHashMatch()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__ucm_history'))
			->where($db->quoteName('ucm_item_id') . ' = ' . $this->get('ucm_item_id'))
			->where($db->quoteName('ucm_type_id') . ' = ' . $this->get('ucm_type_id'))
			->where($db->quoteName('sha1_hash') . ' = ' . $db->quote($this->get('sha1_hash')));
		$db->setQuery($query, 0, 1);

		return $db->loadObject();
	}

	/**
	 * Utility method to remove the oldest versions of an item, saving only the most recent versions.
	 *
	 * @param   integer  $maxVersions  The maximum number of versions to save. All others will be deleted.
	 *
	 * @return  boolean   true on sucess, false on failure.
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
			->where($db->quoteName('ucm_item_id') . ' = ' . (int) $this->ucm_item_id)
			->where($db->quoteName('ucm_type_id') . ' = ' . (int) $this->ucm_type_id)
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
				->where($db->quoteName('ucm_item_id') . ' = ' . (int) $this->ucm_item_id)
				->where($db->quoteName('ucm_type_id') . ' = ' . (int) $this->ucm_type_id)
				->where($db->quoteName('version_id') . ' NOT IN (' . implode(',', $idsToSave) . ')')
				->where($db->quoteName('keep_forever') . ' != 1');
			$db->setQuery($query);
			$result = (boolean) $db->execute();
		}

		return $result;
	}
}
