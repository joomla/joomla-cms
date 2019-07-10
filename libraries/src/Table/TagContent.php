<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Core content table
 *
 * @since  3.1
 */
class TagContent extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  A database connector object
	 *
	 * @since   3.1
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__tag_content', ['type_alias', 'content_id'], $db);
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
	 * @see     Table::bind()
	 * @since   3.1
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['core_params']) && is_array($array['core_params']))
		{
			$registry = new Registry($array['core_params']);
			$array['core_params'] = (string) $registry;
		}

		if (isset($array['core_metadata']) && is_array($array['core_metadata']))
		{
			$registry = new Registry($array['core_metadata']);
			$array['core_metadata'] = (string) $registry;
		}

		if (isset($array['core_images']) && is_array($array['core_images']))
		{
			$registry = new Registry($array['core_images']);
			$array['core_images'] = (string) $registry;
		}

		if (isset($array['core_urls']) && is_array($array['core_urls']))
		{
			$registry = new Registry($array['core_urls']);
			$array['core_urls'] = (string) $registry;
		}

		if (isset($array['core_body']) && is_array($array['core_body']))
		{
			$registry = new Registry($array['core_body']);
			$array['core_body'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     Table::check()
	 * @since   3.1
	 */
	public function check()
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		if (trim($this->core_title) === '')
		{
			$this->setError(Text::_('JLIB_CMS_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}

		if (trim($this->core_alias) === '')
		{
			$this->core_alias = $this->core_title;
		}

		$this->core_alias = ApplicationHelper::stringURLSafe($this->core_alias);

		if (trim(str_replace('-', '', $this->core_alias)) === '')
		{
			$this->core_alias = Factory::getDate()->format('Y-m-d-H-i-s');
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
		if ($this->core_publish_down < $this->core_publish_up && $this->core_publish_down > $this->_db->getNullDate())
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
			$bad_characters = array("\n", "\r", "\"", '<', '>');

			// Remove bad characters
			$after_clean = StringHelper::str_ireplace($bad_characters, '', $this->core_metakey);

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
			$this->core_metakey = implode(', ', $clean_keys);
		}

		return true;
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
		$pks    = ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

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
				$this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

				return false;
			}
		}

		$pksImploded = implode(',', $pks);

		// Get the DatabaseQuery object
		$query = $this->_db->getQuery(true);

		// Update the publishing state for rows with the given primary keys.
		$query->update($this->_db->quoteName($this->_tbl))
			->set($this->_db->quoteName('core_state') . ' = ' . (int) $state)
			->where($this->_db->quoteName($k) . 'IN (' . $pksImploded . ')');

		// Determine if there is checkin support for the table.
		$checkin = false;

		if ($this->hasField('core_checked_out_user_id') && $this->hasField('core_checked_out_time'))
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
		catch (\RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && count($pks) === $this->_db->getAffectedRows())
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
