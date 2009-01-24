<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	User
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access
defined('JPATH_BASE') or die();

jimport( 'joomla.html.parameter');


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
	 * The group id number
	 * @var int
	 */
	public $gid			= null;

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
	 * Description
	 * @var string integer
	 */
	public $aid 		= null;

	/**
	 * Description
	 * @var boolean
	 */
	public $guest	 = null;

	/**
	 * User parameters
	 * @var object
	 */
	protected $_params 	= null;

	/**
	 * Error message
	 * @var string
	 */
	protected $_errorMsg	= null;


	/**
	* Constructor activating the default information of the language
	*
	* @access 	protected
	*/
	protected function __construct($identifier = 0)
	{
		// Create the user parameters object
		$this->_params = new JParameter( '' );

		// Load the user if it exists
		if (!empty($identifier)) {
			$this->load($identifier);
		}
		else
		{
			//initialise
			$this->id		= 0;
			$this->gid		= 0;
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
	 * 		<pre>  $user =& JUser::getInstance($id);</pre>
	 *
	 * @access 	public
	 * @param 	int 	$id 	The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 * @return 	JUser  			The User object.
	 * @since 	1.5
	 */
	public static function &getInstance($id = 0)
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		// Find the user id
		if(!is_numeric($id))
		{
			jimport('joomla.user.helper');
			if (!$id = JUserHelper::getUserId($id)) {
				throw new JException('JUser::_load: User does not exist', 0, E_WARNING, $id);
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
	public function getParam( $key, $default = null )
	{
		return $this->_params->get( $key, $default );
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
	public function setParam( $key, $value )
	{
		return $this->_params->set( $key, $value );
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
	public function defParam( $key, $value )
	{
		return $this->_params->def( $key, $value );
	}

	/**
	 * Method to check JUser object authorization against an access control
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
	public function authorize( $acoSection, $aco, $axoSection = null, $axo = null )
	{
		// the native calls (Check Mode 1) work on the user id, not the user type
		$acl	= & JFactory::getACL();
		$value	= $acl->getCheckMode() == 1 ? $this->id : $this->usertype;

		return $acl->acl_check( $acoSection, $aco,	'users', $value, $axoSection, $axo );
	}

	/**
	 * NOTE: Temporary method to test new access control checks
	 *
	 * @access 	public
	 * @param	string	$acoSection	The ACO section value
	 * @param	string	$aco		The ACO value
	 * @param	string	$axoSection	The AXO section value	[optional]
	 * @param	string	$axo		The AXO value			[optional]
	 * @return	boolean	True if authorized
	 * @since	1.5
	 */
	public function authorize2( $acoSection, $aco, $axoSection = null, $axo = null )
	{
		// the native calls (Check Mode 1) work on the user id, not the user type
		$acl	= & JFactory::getACL();
		$old	= $acl->setCheckMode( 1 );
		$value	= $this->id;

		$result	= $acl->acl_check( $acoSection, $aco,	'users', $value, $axoSection, $axo );
		$acl->setCheckMode( $old );
		return $result;
	}

	/**
	 * Pass through method to the table for setting the last visit date
	 *
	 * @access 	public
	 * @param	int		$timestamp	The timestamp, defaults to 'now'
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function setLastVisit($timestamp=null)
	{
		// Create the user table object
		$table 	=& $this->getTable();
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
	public function &getParameters($loadsetupfile = false, $path = null)
	{
		static $parampath;

		// Set a custom parampath if defined
		if( isset($path) ) {
			$parampath = $path;
		}

		// Set the default parampath if not set already
		if( !isset($parampath) ) {
			$parampath = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_users'.DS.'params';
		}

		if($loadsetupfile)
		{
			$type = str_replace(' ', '_', strtolower($this->usertype));

			$file = $parampath.DS.$type.'.xml';
			if(!file_exists($file)) {
				$file = $parampath.DS.'user.xml';
			}

			$this->_params->loadSetupFile($file);
//			$this->_params->loadSetupDirectory($parampath, $type.'_(.*)\.xml');
//			$this->_params->loadSetupDirectory($parampath, 'general_(.*)\.xml');
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
	public function setParameters($params )
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
	public function &getTable( $type = null, $prefix = 'JTable' )
	{
		static $tabletype;

		//Set the default tabletype;
		if(!isset($tabletype)) {
			$tabletype['name'] 		= 'user';
			$tabletype['prefix']	= 'JTable';
		}

		//Set a custom table type is defined
		if(isset($type)) {
			$tabletype['name'] 		= $type;
			$tabletype['prefix']	= $prefix;
		}

		// Create the user table object
		$table 	=& JTable::getInstance( $tabletype['name'], $tabletype['prefix'] );
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
	public function bind(& $array)
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
					$this->setError( JText::_( 'PASSWORD DO NOT MATCH.' ) );
					return false;
			}

			$this->password_clear = JArrayHelper::getValue( $array, 'password', '', 'string' );

			$salt  = JUserHelper::genRandomPassword(32);
			$crypt = JUserHelper::getCryptedPassword($array['password'], $salt);
			$array['password'] = $crypt.':'.$salt;

			// Set the registration timestamp

			$now =& JFactory::getDate();
			$this->set( 'registerDate', $now->toMySQL() );

			// Check that username is not greater than 25 characters
			$username = $this->get( 'username' );
			if ( strlen($username) > 150 )
			{
				$username = substr( $username, 0, 150 );
				$this->set( 'username', $username );
			}

			// Check that password is not greater than 50 characters
			$password = $this->get( 'password' );
			if ( strlen($password) > 100 )
			{
				$password = substr( $password, 0, 100 );
				$this->set( 'password', $password );
			}
		}
		else
		{
			// Updating an existing user
			if (!empty($array['password']))
			{
				if ( $array['password'] != $array['password2'] ) {
					$this->setError( JText::_( 'PASSWORD DO NOT MATCH.' ) );
					return false;
				}

				$this->password_clear = JArrayHelper::getValue( $array, 'password', '', 'string' );

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
		$db =& JFactory::getDBO();

		$gid = array_key_exists('gid', $array ) ? $array['gid'] : $this->get('gid');

		$query = 'SELECT name'
		. ' FROM #__core_acl_aro_groups'
		. ' WHERE id = ' . (int) $gid
		;
		$db->setQuery( $query );
		try {
			$this->set( 'usertype', $db->loadResult());
		} catch (JException $e) {
			$this->set( 'usertype', null);
		}

		if ( array_key_exists('params', $array) )
		{
			$params	= '';
			$this->_params->bind($array['params']);
			if ( is_array($array['params']) ) {
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
	public function save( $updateOnly = false )
	{
		// Create the user table object
		$table 	=& $this->getTable();
		$this->params = $this->_params->toString();
		$table->bind($this->getProperties());

		// Check and store the object.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// If user is made a Super Admin group and user is NOT a Super Admin
		$my =& JFactory::getUser();
		if ( $this->get('gid') == 25 && $my->get('gid') != 25 )
		{
			// disallow creation of Super Admin by non Super Admin users
			$this->setError(JText::_( 'WARNSUPERADMINCREATE' ));
			return false;
		}

		// If user is made an Admin group and user is NOT a Super Admin
		if ($this->get('gid') == 24 && !($my->get('gid') == 25 || ($this->get('id') == $my->id && $my->get('gid') == 24)))
		{
			// disallow creation of Admin by non Super Admin users
			$this->setError(JText::_( 'WARNSUPERADMINCREATE' ));
			return false;
		}

		//are we creating a new user
		$isnew = !$this->id;

		// If we aren't allowed to create new users return
		if ($isnew && $updateOnly) {
			return true;
		}

		// Get the old user
		$old = new JUser($this->id);

		// Fire the onBeforeStoreUser event.
		JPluginHelper::importPlugin( 'user' );
		$dispatcher =& JDispatcher::getInstance();
		$dispatcher->trigger( 'onBeforeStoreUser', array( $old->getProperties(), $isnew ) );

		//Store the user data in the database
		if (!$result = $table->store()) {
			$this->setError($table->getError());
		}

		// Set the id for the JUser object in case we created a new user.
		if (empty($this->id)) {
			$this->id = $table->get( 'id' );
		}

		// Fire the onAftereStoreUser event
		$dispatcher->trigger( 'onAfterStoreUser', array( $this->getProperties(), $isnew, $result, $this->getError() ) );

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
	public function delete( )
	{
		JPluginHelper::importPlugin( 'user' );

		//trigger the onBeforeDeleteUser event
		$dispatcher =& JDispatcher::getInstance();
		$dispatcher->trigger( 'onBeforeDeleteUser', array( $this->getProperties() ) );

		// Create the user table object
		$table 	=& $this->getTable();

		$result = false;
		if (!$result = $table->delete($this->id)) {
			$this->setError($table->getError());
		}

		//trigger the onAfterDeleteUser event
		$dispatcher->trigger( 'onAfterDeleteUser', array( $this->getProperties(), $result, $this->getError()) );
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
	public function load($id)
	{
		// Create the user table object
		$table 	=& $this->getTable();

		// Load the JUserModel object based on the user id or throw a warning.
		if(!$table->load($id)) {
			throw new JException('JUser::_load: User does not exist', 0, E_WARNING, $id);
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
