<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	User
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;
jimport('joomla.html.parameter');

/**
 * User class.  Handles all application interaction with a user
 *
 * @package 	Joomla.Framework
 * @subpackage	User
 * @since		1.5
 */
class JUser extends JObject
{
	/**
	 * Unique id
	 * @var int
	 */
	public $id				= null;

	/**
	 * The users real name (or nickname)
	 * @var string
	 */
	public $name			= null;

	/**
	 * The login name
	 * @var string
	 */
	public $username		= null;

	/**
	 * The email
	 * @var string
	 */
	public $email			= null;

	/**
	 * MD5 encrypted password
	 * @var string
	 */
	public $password		= null;

	/**
	 * Clear password, only available when a new password is set for a user
	 * @var string
	 */
	public $password_clear	= '';

	/**
	 * Description
	 * @var string
	 */
	public $usertype		= null;

	/**
	 * Description
	 * @var int
	 */
	public $block			= null;

	/**
	 * Description
	 * @var int
	 */
	public $sendEmail		= null;

	/**
	 * Description
	 * @var datetime
	 */
	public $registerDate	= null;

	/**
	 * Description
	 * @var datetime
	 */
	public $lastvisitDate	= null;

	/**
	 * Description
	 * @var string activation hash
	 */
	public $activation		= null;

	/**
	 * Description
	 * @var string
	 */
	public $params			= null;

	/**
	 * Associative array of user group ids => names.
	 *
	 * @access	public
	 * @since	1.6
	 * @var		array
	 */
	var $groups;

	/**
	 * Description
	 * @var boolean
	 */
	var $guest     = null;

	/**
	 * User parameters
	 * @var object
	 */
	protected $_params 	= null;

	/**
	 * Authorised access levels
	 * @var array
	 */
	protected $_authLevels 	= null;

	/**
	 * Authorised access actions
	 * @var array
	 */
	protected $_authActions 	= null;

	/**
	 * Error message
	 * @var string
	 */
	var $_errorMsg	= null;


	/**
	* Constructor activating the default information of the language
	*
	* @access 	protected
	*/
	function __construct($identifier = 0)
	{
		// Create the user parameters object
		$this->_params = new JParameter('');

		// Load the user if it exists
		if (!empty($identifier)) {
			$this->load($identifier);
		}
		else
		{
			//initialise
			$this->id		= 0;
			$this->sendEmail = 0;
			$this->aid		= 0;
			$this->guest	= 1;
		}
	}

	/**
	 * Returns a reference to the global User object, only creating it if it
	 * doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $user = &JUser::getInstance($id);</pre>
	 *
	 * @access 	public
	 * @param 	int 	$id 	The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 * @return 	JUser  			The User object.
	 * @since 	1.5
	 */
	static function &getInstance($id = 0)
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		// Find the user id
		if (!is_numeric($id))
		{
			jimport('joomla.user.helper');
			if (!$id = JUserHelper::getUserId($id)) {
				JError::raiseWarning('SOME_ERROR_CODE', 'JUser::_load: User '.$id.' does not exist');
				$retval = false;
				return $retval;
			}
		}

		if (empty($instances[$id])) {
			$user = new JUser($id);
			$instances[$id] = $user;
		}

		return $instances[$id];
	}

	/**
	 * Method to get a parameter value
	 *
	 * @access 	public
	 * @param 	string 	$key 		Parameter key
	 * @param 	mixed	$default	Parameter default value
	 * @return	mixed				The value or the default if it did not exist
	 * @since	1.5
	 */
	function getParam($key, $default = null)
	{
		return $this->_params->get($key, $default);
	}

	/**
	 * Method to set a parameter
	 *
	 * @access 	public
	 * @param 	string 	$key 	Parameter key
	 * @param 	mixed	$value	Parameter value
	 * @return	mixed			Set parameter value
	 * @since	1.5
	 */
	function setParam($key, $value)
	{
		return $this->_params->set($key, $value);
	}

	/**
	 * Method to set a default parameter if it does not exist
	 *
	 * @access 	public
	 * @param 	string 	$key 	Parameter key
	 * @param 	mixed	$value	Parameter value
	 * @return	mixed			Set parameter value
	 * @since	1.5
	 */
	function defParam($key, $value)
	{
		return $this->_params->def($key, $value);
	}

	/**
	 * @deprecated 1.6
	 */
	public function authorize($action, $assetname = null)
	{
		return $this->authorise($action, $assetname);
	}

	/**
	 * Method to check JUser object authorisation against an access control
	 * object and optionally an access extension object
	 *
	 * @access 	public
	 * @param	string	$acoSection	The ACO section value
	 * @param	string	$aco		The ACO value
	 * @param	string	$axoSection	The AXO section value	[optional]
	 * @param	string	$axo		The AXO value			[optional]
	 * @return	boolean	True if authorized
	 * @since	1.5
	 */
	public function authorise($action, $assetname = null)
	{
		if ($assetname)
		{
			$acl	= & JFactory::getACL();
			return $acl->check($this->id, $action, $assetname);
		}
		if ($this->_authActions === null) {
			$this->_authActions = array();
		}

		if (!isset($this->_authActions[$action])) {
			$acl = JFactory::getACL();
			$this->_authActions[$action] = $acl->check($this->id, $action);
		}

		return $this->_authActions[$action];
	}

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @param	string $action	The action to apply (type 3 rule). Defaults to 'core.view'.
	 *
	 * @return	array
	 */
	public function authorisedLevels($action = 'core.view')
	{
		if ($this->_authLevels === null) {
			$this->_authLevels = array();
		}

		if (!isset($this->_authLevels[$action])) {
			$acs = JFactory::getACL();
			$this->_authLevels[$action] = $acs->getAuthorisedAccessLevels($this->id, $action);
		}

		return $this->_authLevels[$action];
	}

	/**
	 * Pass through method to the table for setting the last visit date
	 *
	 * @access 	public
	 * @param	int		$timestamp	The timestamp, defaults to 'now'
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function setLastVisit($timestamp=null)
	{
		// Create the user table object
		$table 	= &$this->getTable();
		$table->load($this->id);

		return $table->setLastVisit($timestamp);
	}

	/**
	 * Method to get the user parameters
	 *
	 * This function tries to load an xml file based on the users usertype. The filename of the xml
	 * file is the same as the usertype. The functionals has a static variable to store the parameters
	 * setup file base path. You can call this function statically to set the base path if needed.
	 *
	 * @access 	public
	 * @param	boolean	If true, loads the parameters setup file. Default is false.
	 * @param	path	Set the parameters setup file base path to be used to load the user parameters.
	 * @return	object	The user parameters object
	 * @since	1.5
	 */
	function &getParameters($loadsetupfile = false, $path = null)
	{
		static $parampath;

		// Set a custom parampath if defined
		if (isset($path)) {
			$parampath = $path;
		}

		// Set the default parampath if not set already
		if (!isset($parampath)) {
			$parampath = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_users'.DS.'models';
		}

		if ($loadsetupfile)
		{
			$type = str_replace(' ', '_', strtolower($this->usertype));

			$file = $parampath.DS.$type.'.xml';
			if (!file_exists($file)) {
				$file = $parampath.DS.'user.xml';
			}

			$this->_params->loadSetupFile($file);
		}
		return $this->_params;
	}

	/**
	 * Method to get the user parameters
	 *
	 * @access 	public
	 * @param	object	The user parameters object
	 * @since	1.5
	 */
	function setParameters($params)
	{
		$this->_params = $params;
	}

	/**
	 * Method to get the user table object
	 *
	 * This function uses a static variable to store the table name of the user table to
	 * it instantiates. You can call this function statically to set the table name if
	 * needed.
	 *
	 * @access 	public
	 * @param	string	The user table name to be used
	 * @param	string	The user table prefix to be used
	 * @return	object	The user table object
	 * @since	1.5
	 */
	function &getTable($type = null, $prefix = 'JTable')
	{
		static $tabletype;

		//Set the default tabletype;
		if (!isset($tabletype)) {
			$tabletype['name'] 		= 'user';
			$tabletype['prefix']	= 'JTable';
		}

		//Set a custom table type is defined
		if (isset($type)) {
			$tabletype['name'] 		= $type;
			$tabletype['prefix']	= $prefix;
		}

		// Create the user table object
		$table 	= &JTable::getInstance($tabletype['name'], $tabletype['prefix']);
		return $table;
	}

	/**
	 * Method to bind an associative array of data to a user object
	 *
	 * @access 	public
	 * @param 	array 	$array 	The associative array to bind to the object
	 * @return 	boolean 		True on success
	 * @since 1.5
	 */
	function bind(& $array)
	{
		jimport('joomla.user.helper');

		// Lets check to see if the user is new or not
		if (empty($this->id))
		{
			// Check the password and create the crypted password
			if (empty($array['password'])) {
				$array['password']  = JUserHelper::genRandomPassword();
				$array['password2'] = $array['password'];
			}

			if ($array['password'] != $array['password2']) {
					$this->setError(JText::_('PASSWORD DO NOT MATCH.'));
					return false;
			}

			$this->password_clear = JArrayHelper::getValue($array, 'password', '', 'string');

			$salt  = JUserHelper::genRandomPassword(32);
			$crypt = JUserHelper::getCryptedPassword($array['password'], $salt);
			$array['password'] = $crypt.':'.$salt;

			// Set the registration timestamp

			$now = &JFactory::getDate();
			$this->set('registerDate', $now->toMySQL());

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
				if ($array['password'] != $array['password2']) {
					$this->setError(JText::_('PASSWORD DO NOT MATCH.'));
					return false;
				}

				$this->password_clear = JArrayHelper::getValue($array, 'password', '', 'string');

				$salt = JUserHelper::genRandomPassword(32);
				$crypt = JUserHelper::getCryptedPassword($array['password'], $salt);
				$array['password'] = $crypt.':'.$salt;
			}
			else
			{
				$array['password'] = $this->password;
			}
		}

		// TODO: this will be deprecated as of the ACL implementation
		$db = &JFactory::getDbo();

		if (array_key_exists('params', $array))
		{
			$params	= '';
			$this->_params->bind($array['params']);
			if (is_array($array['params'])) {
				$params	= $this->_params->toString();
			} else {
				$params = $array['params'];
			}

			$this->params = $params;
		}

		// Bind the array
		if (!$this->setProperties($array)) {
			$this->setError("Unable to bind array to user object");
			return false;
		}

		// Make sure its an integer
		$this->id = (int) $this->id;

		return true;
	}

	/**
	 * Method to save the JUser object to the database
	 *
	 * @access 	public
	 * @param 	boolean $updateOnly Save the object only if not a new user
	 * @return 	boolean 			True on success
	 * @since 1.5
	 */
	function save($updateOnly = false)
	{
		// Create the user table object
		$table 	= &$this->getTable();
		$this->params = $this->_params->toString();
		$table->bind($this->getProperties());

		// Check and store the object.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// If user is made a Super Admin group and user is NOT a Super Admin
		//
		// @todo ACL - this needs to be acl checked
		//
		$my = &JFactory::getUser();
//		if ($this->get('gid') == 25 && $my->get('gid') != 25)
//		{
//			// disallow creation of Super Admin by non Super Admin users
//			$this->setError(JText::_('WARNSUPERADMINCREATE'));
//			return false;
//		}
//
//		// If user is made an Admin group and user is NOT a Super Admin
//		if ($this->get('gid') == 24 && !($my->get('gid') == 25 || ($this->get('id') == $my->id && $my->get('gid') == 24)))
//		{
//			// disallow creation of Admin by non Super Admin users
//			$this->setError(JText::_('WARNSUPERADMINCREATE'));
//			return false;
//		}

		//are we creating a new user
		$isnew = !$this->id;

		// If we aren't allowed to create new users return
		if ($isnew && $updateOnly) {
			return true;
		}

		// Get the old user
		$old = new JUser($this->id);

		// Fire the onBeforeStoreUser event.
		JPluginHelper::importPlugin('user');
		$dispatcher = &JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeStoreUser', array($old->getProperties(), $isnew, $this->getProperties()));

		//Store the user data in the database
		if (!$result = $table->store()) {
			$this->setError($table->getError());
		}

		// Set the id for the JUser object in case we created a new user.
		if (empty($this->id)) {
			$this->id = $table->get('id');
		}

		// Fire the onAftereStoreUser event
		$dispatcher->trigger('onAfterStoreUser', array($this->getProperties(), $isnew, $result, $this->getError()));

		return $result;
	}

	/**
	 * Method to delete the JUser object from the database
	 *
	 * @access 	public
	 * @param 	boolean $updateOnly Save the object only if not a new user
	 * @return 	boolean 			True on success
	 * @since 1.5
	 */
	function delete()
	{
		JPluginHelper::importPlugin('user');

		//trigger the onBeforeDeleteUser event
		$dispatcher = &JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeDeleteUser', array($this->getProperties()));

		// Create the user table object
		$table 	= &$this->getTable();

		$result = false;
		if (!$result = $table->delete($this->id)) {
			$this->setError($table->getError());
		}

		//trigger the onAfterDeleteUser event
		$dispatcher->trigger('onAfterDeleteUser', array($this->getProperties(), $result, $this->getError()));
		return $result;

	}

	/**
	 * Method to load a JUser object by user id number
	 *
	 * @access 	public
	 * @param 	mixed 	$identifier The user id of the user to load
	 * @param 	string 	$path 		Path to a parameters xml file
	 * @return 	boolean 			True on success
	 * @since 1.5
	 */
	function load($id)
	{
		// Create the user table object
		$table 	= &$this->getTable();

		// Load the JUserModel object based on the user id or throw a warning.
		if (!$table->load($id)) {
			JError::raiseWarning('SOME_ERROR_CODE', 'JUser::_load: Unable to load user with id: '.$id);
			return false;
		}

		/*
		 * Set the user parameters using the default xml file.  We might want to
		 * extend this in the future to allow for the ability to have custom
		 * user parameters, but for right now we'll leave it how it is.
		 */
		$this->_params->loadINI($table->params);

		// Assuming all is well at this point lets bind the data
		$this->setProperties($table->getProperties());

		return true;
	}
}
