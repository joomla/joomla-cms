<?php
/**
 * @package     Joomla.Platform
 * @subpackage  User
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * User class.  Handles all application interaction with a user
 *
 * @package     Joomla.Platform
 * @subpackage  User
 * @since       11.1
 */
class JUser extends JObject
{
	/**
	 * A cached switch for if this user has root access rights.
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $isRoot = null;

	/**
	 * Unique id
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $id = null;

	/**
	 * The users real name (or nickname)
	 * @var    string
	 * @since  11.1
	 */
	public $name = null;

	/**
	 * The login name
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $username = null;

	/**
	 * The email
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $email = null;

	/**
	 * MD5 encrypted password
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $password = null;

	/**
	 * Clear password, only available when a new password is set for a user
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $password_clear = '';

	/**
	 * Block status
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $block = null;

	/**
	 * Should this user receive system email
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $sendEmail = null;

	/**
	 * Date the user was registered
	 *
	 * @var    datetime
	 * @since  11.1
	 */
	public $registerDate = null;

	/**
	 * Date of last visit
	 *
	 * @var    datetime
	 * @since  11.1
	 */
	public $lastvisitDate = null;

	/**
	 * Activation hash
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $activation = null;

	/**
	 * User parameters
	 *
	 * @var    JRegistry
	 * @since  11.1
	 */
	public $params = null;

	/**
	 * Associative array of user names => group ids
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $groups = array();

	/**
	 * Guest status
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	public $guest = null;

	/**
	 * Last Reset Time
	 *
	 * @var    string
	 * @since  12.2
	 */
	public $lastResetTime = null;

	/**
	 * Count since last Reset Time
	 *
	 * @var    int
	 * @since  12.2
	 */
	public $resetCount = null;

	/**
	 * User parameters
	 * @var    JRegistry
	 * @since  11.1
	 */
	protected $_params = null;

	/**
	 * Authorised access groups
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $_authGroups = null;

	/**
	 * Authorised access levels
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $_authLevels = null;

	/**
	 * Authorised access actions
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $_authActions = null;

	/**
	 * Error message
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_errorMsg = null;

	/**
	 * @var    array  JUser instances container.
	 * @since  11.3
	 */
	protected static $instances = array();

	/**
	 * Constructor activating the default information of the language
	 *
	 * @param   integer  $identifier  The primary key of the user to load (optional).
	 *
	 * @since   11.1
	 */
	public function __construct($identifier = 0)
	{
		// Create the user parameters object
		$this->_params = new JRegistry;

		// Load the user if it exists
		if (!empty($identifier))
		{
			$this->load($identifier);
		}
		else
		{
			// Initialise
			$this->id = 0;
			$this->sendEmail = 0;
			$this->aid = 0;
			$this->guest = 1;
		}
	}

	/**
	 * Returns the global User object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param   integer  $identifier  The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 *
	 * @return  JUser  The User object.
	 *
	 * @since   11.1
	 */
	public static function getInstance($identifier = 0)
	{
		// Find the user id
		if (!is_numeric($identifier))
		{
			if (!$id = JUserHelper::getUserId($identifier))
			{
				JLog::add(JText::sprintf('JLIB_USER_ERROR_ID_NOT_EXISTS', $identifier), JLog::WARNING, 'jerror');
				$retval = false;
				return $retval;
			}
		}
		else
		{
			$id = $identifier;
		}

		// If the $id is zero, just return an empty JUser.
		// Note: don't cache this user because it'll have a new ID on save!
		if ($id === 0)
		{
			return new JUser;
		}

		// Check if the user ID is already cached.
		if (empty(self::$instances[$id]))
		{
			$user = new JUser($id);
			self::$instances[$id] = $user;
		}

		return self::$instances[$id];
	}

	/**
	 * Method to get a parameter value
	 *
	 * @param   string  $key      Parameter key
	 * @param   mixed   $default  Parameter default value
	 *
	 * @return  mixed  The value or the default if it did not exist
	 *
	 * @since   11.1
	 */
	public function getParam($key, $default = null)
	{
		return $this->_params->get($key, $default);
	}

	/**
	 * Method to set a parameter
	 *
	 * @param   string  $key    Parameter key
	 * @param   mixed   $value  Parameter value
	 *
	 * @return  mixed  Set parameter value
	 *
	 * @since   11.1
	 */
	public function setParam($key, $value)
	{
		return $this->_params->set($key, $value);
	}

	/**
	 * Method to set a default parameter if it does not exist
	 *
	 * @param   string  $key    Parameter key
	 * @param   mixed   $value  Parameter value
	 *
	 * @return  mixed  Set parameter value
	 *
	 * @since   11.1
	 */
	public function defParam($key, $value)
	{
		return $this->_params->def($key, $value);
	}

	/**
	 * Method to check JUser object authorisation against an access control
	 * object and optionally an access extension object
	 *
	 * @param   string  $action     The name of the action to check for permission.
	 * @param   string  $assetname  The name of the asset on which to perform the action.
	 *
	 * @return  boolean  True if authorised
	 *
	 * @since   11.1
	 */
	public function authorise($action, $assetname = null)
	{
		// Make sure we only check for core.admin once during the run.
		if ($this->isRoot === null)
		{
			$this->isRoot = false;

			// Check for the configuration file failsafe.
			$config = JFactory::getConfig();
			$rootUser = $config->get('root_user');

			// The root_user variable can be a numeric user ID or a username.
			if (is_numeric($rootUser) && $this->id > 0 && $this->id == $rootUser)
			{
				$this->isRoot = true;
			}
			elseif ($this->username && $this->username == $rootUser)
			{
				$this->isRoot = true;
			}
			else
			{
				// Get all groups against which the user is mapped.
				$identities = $this->getAuthorisedGroups();
				array_unshift($identities, $this->id * -1);

				if (JAccess::getAssetRules(1)->allow('core.admin', $identities))
				{
					$this->isRoot = true;
					return true;
				}
			}
		}

		return $this->isRoot ? true : JAccess::check($this->id, $action, $assetname);
	}

	/**
	 * Method to return a list of all categories that a user has permission for a given action
	 *
	 * @param   string  $component  The component from which to retrieve the categories
	 * @param   string  $action     The name of the section within the component from which to retrieve the actions.
	 *
	 * @return  array  List of categories that this group can do this action to (empty array if none). Categories must be published.
	 *
	 * @since   11.1
	 */
	public function getAuthorisedCategories($component, $action)
	{
		// Brute force method: get all published category rows for the component and check each one
		// TODO: Modify the way permissions are stored in the db to allow for faster implementation and better scaling
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('c.id AS id, a.name AS asset_name')
			->from('#__categories AS c')
			->join('INNER', '#__assets AS a ON c.asset_id = a.id')
			->where('c.extension = ' . $db->quote($component))
			->where('c.published = 1');
		$db->setQuery($query);
		$allCategories = $db->loadObjectList('id');
		$allowedCategories = array();
		foreach ($allCategories as $category)
		{
			if ($this->authorise($action, $category->asset_name))
			{
				$allowedCategories[] = (int) $category->id;
			}
		}
		return $allowedCategories;
	}

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function getAuthorisedViewLevels()
	{
		if ($this->_authLevels === null)
		{
			$this->_authLevels = array();
		}

		if (empty($this->_authLevels))
		{
			$this->_authLevels = JAccess::getAuthorisedViewLevels($this->id);
		}

		return $this->_authLevels;
	}
	/**
	 * Gets an array of the authorised user groups
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function getAuthorisedGroups()
	{
		if ($this->_authGroups === null)
		{
			$this->_authGroups = array();
		}

		if (empty($this->_authGroups))
		{
			$this->_authGroups = JAccess::getGroupsByUser($this->id);
		}

		return $this->_authGroups;
	}
	/**
	 * Pass through method to the table for setting the last visit date
	 *
	 * @param   integer  $timestamp  The timestamp, defaults to 'now'.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function setLastVisit($timestamp = null)
	{
		// Create the user table object
		$table = $this->getTable();
		$table->load($this->id);

		return $table->setLastVisit($timestamp);
	}

	/**
	 * Method to get the user parameters
	 *
	 * This method used to load the user parameters from a file.
	 *
	 * @return  object   The user parameters object.
	 *
	 * @since   11.1
	 * @deprecated  12.3 (Platform) & 4.0 (CMS) - Instead use JUser::getParam()
	 */
	public function getParameters()
	{
		// @codeCoverageIgnoreStart
		JLog::add('JUser::getParameters() is deprecated. JUser::getParam().', JLog::WARNING, 'deprecated');

		return $this->_params;

		// @codeCoverageIgnoreEnd
	}

	/**
	 * Method to get the user parameters
	 *
	 * @param   object  $params  The user parameters object
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function setParameters($params)
	{
		$this->_params = $params;
	}

	/**
	 * Method to get the user table object
	 *
	 * This function uses a static variable to store the table name of the user table to
	 * instantiate. You can call this function statically to set the table name if
	 * needed.
	 *
	 * @param   string  $type    The user table name to be used
	 * @param   string  $prefix  The user table prefix to be used
	 *
	 * @return  object  The user table object
	 *
	 * @since   11.1
	 */
	public static function getTable($type = null, $prefix = 'JTable')
	{
		static $tabletype;

		// Set the default tabletype;
		if (!isset($tabletype))
		{
			$tabletype['name'] = 'user';
			$tabletype['prefix'] = 'JTable';
		}

		// Set a custom table type is defined
		if (isset($type))
		{
			$tabletype['name'] = $type;
			$tabletype['prefix'] = $prefix;
		}

		// Create the user table object
		return JTable::getInstance($tabletype['name'], $tabletype['prefix']);
	}

	/**
	 * Method to bind an associative array of data to a user object
	 *
	 * @param   array  &$array  The associative array to bind to the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function bind(&$array)
	{
		// Let's check to see if the user is new or not
		if (empty($this->id))
		{
			// Check the password and create the crypted password
			if (empty($array['password']))
			{
				$array['password'] = JUserHelper::genRandomPassword();
				$array['password2'] = $array['password'];
			}

			// TODO: Backend controller checks the password, frontend doesn't but should.
			// Hence this code is required:
			if (isset($array['password2']) && $array['password'] != $array['password2'])
			{
				$this->setError(JText::_('JLIB_USER_ERROR_PASSWORD_NOT_MATCH'));
				return false;
			}

			$this->password_clear = JArrayHelper::getValue($array, 'password', '', 'string');

			$salt = JUserHelper::genRandomPassword(32);
			$crypt = JUserHelper::getCryptedPassword($array['password'], $salt);
			$array['password'] = $crypt . ':' . $salt;

			// Set the registration timestamp

			$this->set('registerDate', JFactory::getDate()->toSql());

			// Check that username is not greater than 150 characters
			$username = $this->get('username');
			if (strlen($username) > 150)
			{
				$username = substr($username, 0, 150);
				$this->set('username', $username);
			}

			// Check that password is not greater than 100 characters
			$password = $this->get('password');
			if (strlen($password) > 100)
			{
				$password = substr($password, 0, 100);
				$this->set('password', $password);
			}
		}
		else
		{
			// Updating an existing user
			if (!empty($array['password']))
			{
				if ($array['password'] != $array['password2'])
				{
					$this->setError(JText::_('JLIB_USER_ERROR_PASSWORD_NOT_MATCH'));
					return false;
				}

				$this->password_clear = JArrayHelper::getValue($array, 'password', '', 'string');

				$salt = JUserHelper::genRandomPassword(32);
				$crypt = JUserHelper::getCryptedPassword($array['password'], $salt);
				$array['password'] = $crypt . ':' . $salt;
			}
			else
			{
				$array['password'] = $this->password;
			}
		}

		if (array_key_exists('params', $array))
		{
			$params = '';

			$this->_params->loadArray($array['params']);

			if (is_array($array['params']))
			{
				$params = (string) $this->_params;
			}
			else
			{
				$params = $array['params'];
			}

			$this->params = $params;
		}

		// Bind the array
		if (!$this->setProperties($array))
		{
			$this->setError(JText::_('JLIB_USER_ERROR_BIND_ARRAY'));
			return false;
		}

		// Make sure its an integer
		$this->id = (int) $this->id;

		return true;
	}

	/**
	 * Method to save the JUser object to the database
	 *
	 * @param   boolean  $updateOnly  Save the object only if not a new user
	 *                                Currently only used in the user reset password method.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function save($updateOnly = false)
	{
		// Create the user table object
		$table = $this->getTable();
		$this->params = (string) $this->_params;
		$table->bind($this->getProperties());

		// Allow an exception to be thrown.
		try
		{
			// Check and store the object.
			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}

			// If user is made a Super Admin group and user is NOT a Super Admin

			// @todo ACL - this needs to be acl checked

			$my = JFactory::getUser();

			// Are we creating a new user
			$isNew = empty($this->id);

			// If we aren't allowed to create new users return
			if ($isNew && $updateOnly)
			{
				return true;
			}

			// Get the old user
			$oldUser = new JUser($this->id);

			// Access Checks

			// The only mandatory check is that only Super Admins can operate on other Super Admin accounts.
			// To add additional business rules, use a user plugin and throw an Exception with onUserBeforeSave.

			// Check if I am a Super Admin
			$iAmSuperAdmin = $my->authorise('core.admin');

			// We are only worried about edits to this account if I am not a Super Admin.
			if ($iAmSuperAdmin != true)
			{
				if ($isNew)
				{
					// Check if the new user is being put into a Super Admin group.
					foreach ($this->groups as $groupId)
					{
						if (JAccess::checkGroup($groupId, 'core.admin'))
						{
							throw new RuntimeException('User not Super Administrator');
						}
					}
				}
				else
				{
					// I am not a Super Admin, and this one is, so fail.
					if (JAccess::check($this->id, 'core.admin'))
					{
						throw new RuntimeException('User not Super Administrator');
					}

					if ($this->groups != null)
					{
						// I am not a Super Admin and I'm trying to make one.
						foreach ($this->groups as $groupId)
						{
							if (JAccess::checkGroup($groupId, 'core.admin'))
							{
								throw new RuntimeException('User not Super Administrator');
							}
						}
					}
				}
			}

			// Fire the onUserBeforeSave event.
			JPluginHelper::importPlugin('user');
			$dispatcher = JEventDispatcher::getInstance();

			$result = $dispatcher->trigger('onUserBeforeSave', array($oldUser->getProperties(), $isNew, $this->getProperties()));
			if (in_array(false, $result, true))
			{
				// Plugin will have to raise its own error or throw an exception.
				return false;
			}

			// Store the user data in the database
			$result = $table->store();

			// Set the id for the JUser object in case we created a new user.
			if (empty($this->id))
			{
				$this->id = $table->get('id');
			}

			if ($my->id == $table->id)
			{
				$registry = new JRegistry;
				$registry->loadString($table->params);
				$my->setParameters($registry);
			}

			// Fire the onUserAfterSave event
			$dispatcher->trigger('onUserAfterSave', array($this->getProperties(), $isNew, $result, $this->getError()));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to delete the JUser object from the database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function delete()
	{
		JPluginHelper::importPlugin('user');

		// Trigger the onUserBeforeDelete event
		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onUserBeforeDelete', array($this->getProperties()));

		// Create the user table object
		$table = $this->getTable();

		$result = false;
		if (!$result = $table->delete($this->id))
		{
			$this->setError($table->getError());
		}

		// Trigger the onUserAfterDelete event
		$dispatcher->trigger('onUserAfterDelete', array($this->getProperties(), $result, $this->getError()));

		return $result;
	}

	/**
	 * Method to load a JUser object by user id number
	 *
	 * @param   mixed  $id  The user id of the user to load
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function load($id)
	{
		// Create the user table object
		$table = $this->getTable();

		// Load the JUserModel object based on the user id or throw a warning.
		if (!$table->load($id))
		{
			// Reset to guest user
			$this->guest = 1;

			JLog::add(JText::sprintf('JLIB_USER_ERROR_UNABLE_TO_LOAD_USER', $id), JLog::WARNING, 'jerror');
			return false;
		}

		/*
		 * Set the user parameters using the default XML file.  We might want to
		 * extend this in the future to allow for the ability to have custom
		 * user parameters, but for right now we'll leave it how it is.
		 */

		$this->_params->loadString($table->params);

		// Assuming all is well at this point let's bind the data
		$this->setProperties($table->getProperties());

		// The user is no longer a guest
		if ($this->id != 0)
		{
			$this->guest = 0;
		}
		else
		{
			$this->guest = 1;
		}

		return true;
	}
}
