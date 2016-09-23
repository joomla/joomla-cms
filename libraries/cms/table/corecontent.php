<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Core content table
 *
 * @since  3.1
 */
class JTableCorecontent extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 *
	 * @since   3.1
	 */
	public function __construct($db)
	{
		parent::__construct('#__ucm_content', 'core_content_id', $db);
	}

	/**
	 * Overloaded bind function
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  An optional array or space separated list of properties
	 *                          to ignore while binding.
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error string
	 *
	 * @see     JTable::bind()
	 * @since   3.1
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['core_params']) && is_array($array['core_params']))
		{
			$registry = new Registry;
			$registry->loadArray($array['core_params']);
			$array['core_params'] = (string) $registry;
		}

		if (isset($array['core_metadata']) && is_array($array['core_metadata']))
		{
			$registry = new Registry;
			$registry->loadArray($array['core_metadata']);
			$array['core_metadata'] = (string) $registry;
		}

		if (isset($array['core_images']) && is_array($array['core_images']))
		{
			$registry = new Registry;
			$registry->loadArray($array['core_images']);
			$array['core_images'] = (string) $registry;
		}

		if (isset($array['core_urls']) && is_array($array['core_urls']))
		{
			$registry = new Registry;
			$registry->loadArray($array['core_urls']);
			$array['core_urls'] = (string) $registry;
		}

		if (isset($array['core_body']) && is_array($array['core_body']))
		{
			$registry = new Registry;
			$registry->loadArray($array['core_body']);
			$array['core_body'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     JTable::check()
	 * @since   3.1
	 */
	public function check()
	{
		if (trim($this->core_title) == '')
		{
			$this->setError(JText::_('JLIB_CMS_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}

		if (trim($this->core_alias) == '')
		{
			$this->core_alias = $this->core_title;
		}

		$this->core_alias = JApplicationHelper::stringURLSafe($this->core_alias);

		if (trim(str_replace('-', '', $this->core_alias)) == '')
		{
			$this->core_alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}
		// Not Null sanity check
		if (empty($this->core_images))
		{
			$this->core_images = '{}';
		}
		if (empty($this->core_urls))
		{
			$this->core_urls = '{}';
		}
		// Check the publish down date is not earlier than publish up.
		if ($this->core_publish_down > $this->_db->getNullDate() && $this->core_publish_down < $this->core_publish_up)
		{
			// Swap the dates.
			$temp = $this->core_publish_up;
			$this->core_publish_up = $this->core_publish_down;
			$this->core_publish_down = $temp;
		}

		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->core_metakey))
		{
			// Only process if not empty

			// Array of characters to remove
			$bad_characters = array("\n", "\r", "\"", "<", ">");

			// Remove bad characters
			$after_clean = JString::str_ireplace($bad_characters, "", $this->core_metakey);

			// Create array using commas as delimiter
			$keys = explode(',', $after_clean);

			$clean_keys = array();

			foreach ($keys as $key)
			{
				if (trim($key))
				{
					// Ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}
			// Put array back together delimited by ", "
			$this->core_metakey = implode(", ", $clean_keys);
		}

		return true;
	}

	/**
	 * Override JTable delete method to include deleting corresponding row from #__ucm_base.
	 *
	 * @param   integer  $pk  primary key value to delete. Must be set or throws an exception.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 * @throws  UnexpectedValueException
	 */
	public function delete($pk = null)
	{
		$baseTable = JTable::getInstance('Ucm');

		return parent::delete($pk) && $baseTable->delete($pk);
	}

	/**
	 * Method to delete a row from the #__ucm_content table by content_item_id.
	 *
	 * @param   integer  $contentItemId  value of the core_content_item_id to delete. Corresponds to the primary key of the content table.
	 * @param   string   $typeAlias      Alias for the content type
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 * @throws  UnexpectedValueException
	 */
	public function deleteByContentId($contentItemId = null, $typeAlias = null)
	{
		if ($contentItemId === null || ((int) $contentItemId) === 0)
		{
			throw new UnexpectedValueException('Null content item key not allowed.');
		}

		if ($typeAlias === null)
		{
			throw new UnexpectedValueException('Null type alias not allowed.');
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('core_content_id'))
			->from($db->quoteName('#__ucm_content'))
			->where($db->quoteName('core_content_item_id') . ' = ' . (int) $contentItemId)
			->where($db->quoteName('core_type_alias') . ' = ' . $db->quote($typeAlias));
		$db->setQuery($query);

		if ($ucmId = $db->loadResult())
		{
			return $this->delete($ucmId);
		}
		else
		{
			return true;
		}
	}

	/**
	 * Overrides JTable::store to set modified data and user id.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	public function store($updateNulls = false)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if ($this->core_content_id)
		{
			// Existing item
			$this->core_modified_time = $date->toSql();
			$this->core_modified_user_id = $user->get('id');
			$isNew = false;
		}
		else
		{
			// New content item. A content item core_created_time and core_created_user_id field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->core_created_time)
			{
				$this->core_created_time = $date->toSql();
			}

			if (empty($this->core_created_user_id))
			{
				$this->core_created_user_id = $user->get('id');
			}

			$isNew = true;
		}

		$oldRules = $this->getRules();

		if (empty($oldRules))
		{
			$this->setRules('{}');
		}

		$result = parent::store($updateNulls);

		return $result && $this->storeUcmBase($updateNulls, $isNew);
	}

	/**
	 * Insert or update row in ucm_base table
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 * @param   boolean  $isNew        if true, need to insert. Otherwise update.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	protected function storeUcmBase($updateNulls = false, $isNew = false)
	{
		// Store the ucm_base row
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$languageId = JHelperContent::getLanguageId($this->core_language);

		// Selecting "all languages" doesn't give a language id - we can't store a blank string in non mysql databases, so save 0 (the default value)
		if (!$languageId)
		{
			$languageId = '0';
		}

		if ($isNew)
		{
			$query->insert($db->quoteName('#__ucm_base'))
				->columns(array($db->quoteName('ucm_id'), $db->quoteName('ucm_item_id'), $db->quoteName('ucm_type_id'), $db->quoteName('ucm_language_id')))
				->values(
					$db->quote($this->core_content_id) . ', '
					. $db->quote($this->core_content_item_id) . ', '
					. $db->quote($this->core_type_id) . ', '
					. $db->quote($languageId)
			);
		}
		else
		{
			$query->update($db->quoteName('#__ucm_base'))
				->set($db->quoteName('ucm_item_id') . ' = ' . $db->quote($this->core_content_item_id))
				->set($db->quoteName('ucm_type_id') . ' = ' . $db->quote($this->core_type_id))
				->set($db->quoteName('ucm_language_id') . ' = ' . $db->quote($languageId))
				->where($db->quoteName('ucm_id') . ' = ' . $db->quote($this->core_content_id));
		}

		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table. The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_key;

		// Sanitize input.
		$pks = ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

				return false;
			}
		}

		$pksImploded = implode(',', $pks);

		// Get the JDatabaseQuery object
		$query = $this->_db->getQuery(true);

		// Update the publishing state for rows with the given primary keys.
		$query->update($this->_db->quoteName($this->_tbl))
			->set($this->_db->quoteName('core_state') . ' = ' . (int) $state)
			->where($this->_db->quoteName($k) . 'IN (' . $pksImploded . ')');

		// Determine if there is checkin support for the table.
		$checkin = false;

		if (property_exists($this, 'core_checked_out_user_id') && property_exists($this, 'core_checked_out_time'))
		{
			$checkin = true;
			$query->where(
				' ('
				. $this->_db->quoteName('core_checked_out_user_id') . ' = 0 OR ' . $this->_db->quoteName('core_checked_out_user_id') . ' = ' . (int) $userId
				. ')'
			);
		}

		$this->_db->setQuery($query);

		try
		{
			$this->_db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->core_state = $state;
		}

		$this->setError('');

		return true;
	}
}
