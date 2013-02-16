<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.database.table');

/**
 * Users table
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @since       11.1
 */
class JTableUser extends JTable
{
	/**
	 * Associative array of user names => group ids
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $groups;

	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  A database connector object.
	 *
	 * @since  11.1
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__users', 'id', $db);

		// Initialise.
		$this->id = 0;
		$this->sendEmail = 0;
	}

	/**
	 * Method to load a user, user groups, and any other necessary data
	 * from the database so that it can be bound to the user object.
	 *
	 * @param   integer  $userId  An optional user id.
	 * @param   boolean  $reset   False if row not found or on error
	 * (internal error state set in that case).
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function load($userId = null, $reset = true)
	{
		// Get the id to load.
		if ($userId !== null)
		{
			$this->id = $userId;
		}
		else
		{
			$userId = $this->id;
		}

		// Check for a valid id to load.
		if ($userId === null)
		{
			return false;
		}

		// Reset the table.
		$this->reset();

		// Load the user data.
		$query = $this->_db->getQuery(true);
		$query->select('*');
		$query->from($this->_db->quoteName('#__users'));
		$query->where($this->_db->quoteName('id') . ' = ' . (int) $userId);
		$this->_db->setQuery($query);
		$data = (array) $this->_db->loadAssoc();

		// Check for an error message.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!count($data))
		{
			return false;
		}
		// Bind the data to the table.
		$return = $this->bind($data);

		if ($return !== false)
		{
			// Load the user groups.
			$query->clear();
			$query->select($this->_db->quoteName('g') . '.' . $this->_db->quoteName('id'));
			$query->select($this->_db->quoteName('g') . '.' . $this->_db->quoteName('title'));
			$query->from($this->_db->quoteName('#__usergroups') . ' AS g');
			$query->join('INNER', $this->_db->quoteName('#__user_usergroup_map') . ' AS m ON m.group_id = g.id');
			$query->where($this->_db->quoteName('m.user_id') . ' = ' . (int) $userId);
			$this->_db->setQuery($query);
			// Add the groups to the user data.
			$this->groups = $this->_db->loadAssocList('id', 'id');

			// Check for an error message.
			if ($this->_db->getErrorNum())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return $return;
	}

	/**
	 * Method to bind the user, user groups, and any other necessary data.
	 *
	 * @param   array  $array   The data to bind.
	 * @param   mixed  $ignore  An array or space separated list of fields to ignore.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function bind($array, $ignore = '')
	{
		if (key_exists('params', $array) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		// Attempt to bind the data.
		$return = parent::bind($array, $ignore);

		// Load the real group data based on the bound ids.
		if ($return && !empty($this->groups))
		{
			// Set the group ids.
			JArrayHelper::toInteger($this->groups);

			// Get the titles for the user groups.
			$query = $this->_db->getQuery(true);
			$query->select($this->_db->quoteName('id'));
			$query->select($this->_db->quoteName('title'));
			$query->from($this->_db->quoteName('#__usergroups'));
			$query->where($this->_db->quoteName('id') . ' = ' . implode(' OR ' . $this->_db->quoteName('id') . ' = ', $this->groups));
			$this->_db->setQuery($query);
			// Set the titles for the user groups.
			$this->groups = $this->_db->loadAssocList('id', 'id');

			// Check for a database error.
			if ($this->_db->getErrorNum())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return $return;
	}

	/**
	 * Validation and filtering
	 *
	 * @return  boolean  True if satisfactory
	 *
	 * @since   11.1
	 */
	public function check()
	{
		// Validate user information
		if (trim($this->name) == '')
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_YOUR_NAME'));
			return false;
		}

		if (trim($this->username) == '')
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_A_USER_NAME'));
			return false;
		}

		if (preg_match("#[<>\"'%;()&]#i", $this->username) || strlen(utf8_decode($this->username)) < 2)
		{
			$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_VALID_AZ09', 2));
			return false;
		}

		if ((trim($this->email) == "") || !JMailHelper::isEmailAddress($this->email))
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_VALID_MAIL'));
			return false;
		}

		// Set the registration timestamp
		if ($this->registerDate == null || $this->registerDate == $this->_db->getNullDate())
		{
			$this->registerDate = JFactory::getDate()->toSql();
		}

		// check for existing username
		$query = $this->_db->getQuery(true);
		$query->select($this->_db->quoteName('id'));
		$query->from($this->_db->quoteName('#__users'));
		$query->where($this->_db->quoteName('username') . ' = ' . $this->_db->quote($this->username));
		$query->where($this->_db->quoteName('id') . ' != ' . (int) $this->id);
		$this->_db->setQuery($query);

		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id))
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_USERNAME_INUSE'));
			return false;
		}

		// check for existing email
		$query->clear();
		$query->select($this->_db->quoteName('id'));
		$query->from($this->_db->quoteName('#__users'));
		$query->where($this->_db->quoteName('email') . ' = ' . $this->_db->quote($this->email));
		$query->where($this->_db->quoteName('id') . ' != ' . (int) $this->id);
		$this->_db->setQuery($query);
		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id))
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_EMAIL_INUSE'));
			return false;
		}

		// check for root_user != username
		$config = JFactory::getConfig();
		$rootUser = $config->get('root_user');
		if (!is_numeric($rootUser))
		{
			$query->clear();
			$query->select($this->_db->quoteName('id'));
			$query->from($this->_db->quoteName('#__users'));
			$query->where($this->_db->quoteName('username') . ' = ' . $this->_db->quote($rootUser));
			$this->_db->setQuery($query);
			$xid = intval($this->_db->loadResult());
			if ($rootUser == $this->username && (!$xid || $xid && $xid != intval($this->id))
				|| $xid && $xid == intval($this->id) && $rootUser != $this->username)
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_USERNAME_CANNOT_CHANGE'));
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/store
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		// Get the table key and key value.
		$k = $this->_tbl_key;
		$key = $this->$k;

		// TODO: This is a dumb way to handle the groups.
		// Store groups locally so as to not update directly.
		$groups = $this->groups;
		unset($this->groups);

		// Insert or update the object based on presence of a key value.
		if ($key)
		{
			// Already have a table key, update the row.
			$return = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
		}
		else
		{
			// Don't have a table key, insert the row.
			$return = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
		}

		// Handle error if it exists.
		if (!$return)
		{
			$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', strtolower(get_class($this)), $this->_db->getErrorMsg()));
			return false;
		}

		// Reset groups to the local object.
		$this->groups = $groups;
		unset($groups);

		// Store the group data if the user data was saved.
		if ($return && is_array($this->groups) && count($this->groups))
		{
			// Delete the old user group maps.
			$query = $this->_db->getQuery(true);
			$query->delete();
			$query->from($this->_db->quoteName('#__user_usergroup_map'));
			$query->where($this->_db->quoteName('user_id') . ' = ' . (int) $this->id);
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Check for a database error.
			if ($this->_db->getErrorNum())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			// Set the new user group maps.
			$query->clear();
			$query->insert($this->_db->quoteName('#__user_usergroup_map'));
			$query->columns(array($this->_db->quoteName('user_id'), $this->_db->quoteName('group_id')));
			$query->values($this->id . ', ' . implode('), (' . $this->id . ', ', $this->groups));
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Check for a database error.
			if ($this->_db->getErrorNum())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to delete a user, user groups, and any other necessary data from the database.
	 *
	 * @param   integer  $userId  An optional user id.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function delete($userId = null)
	{
		// Set the primary key to delete.
		$k = $this->_tbl_key;
		if ($userId)
		{
			$this->$k = intval($userId);
		}

		// Delete the user.
		$query = $this->_db->getQuery(true);
		$query->delete();
		$query->from($this->_db->quoteName($this->_tbl));
		$query->where($this->_db->quoteName($this->_tbl_key) . ' = ' . (int) $this->$k);
		$this->_db->setQuery($query);
		$this->_db->execute();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Delete the user group maps.
		$query->clear();
		$query->delete();
		$query->from($this->_db->quoteName('#__user_usergroup_map'));
		$query->where($this->_db->quoteName('user_id') . ' = ' . (int) $this->$k);
		$this->_db->setQuery($query);
		$this->_db->execute();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		/*
		 * Clean Up Related Data.
		 */

		$query->clear();
		$query->delete();
		$query->from($this->_db->quoteName('#__messages_cfg'));
		$query->where($this->_db->quoteName('user_id') . ' = ' . (int) $this->$k);
		$this->_db->setQuery($query);
		$this->_db->execute();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$query->clear();
		$query->delete();
		$query->from($this->_db->quoteName('#__messages'));
		$query->where($this->_db->quoteName('user_id_to') . ' = ' . (int) $this->$k);
		$this->_db->setQuery($query);
		$this->_db->execute();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Updates last visit time of user
	 *
	 * @param   integer  $timeStamp  The timestamp, defaults to 'now'.
	 * @param   integer  $userId     The user id (optional).
	 *
	 * @return  boolean  False if an error occurs
	 *
	 * @since   11.1
	 */
	public function setLastVisit($timeStamp = null, $userId = null)
	{
		// Check for User ID
		if (is_null($userId))
		{
			if (isset($this))
			{
				$userId = $this->id;
			}
			else
			{
				// do not translate
				jexit(JText::_('JLIB_DATABASE_ERROR_SETLASTVISIT'));
			}
		}

		// If no timestamp value is passed to function, than current time is used.
		$date = JFactory::getDate($timeStamp);

		// Update the database row for the user.
		$db = $this->_db;
		$query = $db->getQuery(true);
		$query->update($db->quoteName($this->_tbl));
		$query->set($db->quoteName('lastvisitDate') . '=' . $db->quote($date->toSql()));
		$query->where($db->quoteName('id') . '=' . (int) $userId);
		$db->setQuery($query);
		$db->execute();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}

		return true;
	}
}
