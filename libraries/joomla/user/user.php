<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	User
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport( 'joomla.html.parameter');

/**
 * User class.  Handles all application interaction with a user
 *
 * @author 		Louis Landry <louis.landry@joomla.org>
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
	var $id				= null;

	/**
	 * The users real name (or nickname)
	 * @var string
	 */
	var $name			= null;

	/**
	 * The login name
	 * @var string
	 */
	var $username		= null;

	/**
	 * The email
	 * @var string
	 */
	var $email			= null;

	/**
	 * MD5 encrypted password
	 * @var string
	 */
	var $password		= null;

	/**
	 * Description
	 * @var string
	 */
	var $usertype		= null;

	/**
	 * Description
	 * @var int
	 */
	var $block			= null;

	/**
	 * Description
	 * @var int
	 */
	var $sendEmail		= null;

	/**
	 * The group id number
	 * @var int
	 */
	var $gid			= null;

	/**
	 * Description
	 * @var datetime
	 */
	var $registerDate	= null;

	/**
	 * Description
	 * @var datetime
	 */
	var $lastvisitDate	= null;

	/**
	 * Description
	 * @var string activation hash
	 */
	var $activation		= null;

	/**
	 * Description
	 * @var string
	 */
	var $params			= null;

	/**
	 * Description
	 * @var string integer
	 */
	var $aid 		= null;

	/**
	 * Description
	 * @var boolean
	 */
	var $guest     = null;

	/**
	 * User parameters
	 * @var object
	 */
	var $_params 	= null;

	/**
	 * Error message
	 * @var string
	 */
	var $_errorMsg	= null;

	/**
	 * Clear password, only available when a new password is set for a user
	 * @var string
	 */
	var $clearPW	= '';


	/**
	* Constructor activating the default information of the language
	*
	* @access 	protected
	*/
	function __construct($identifier = 0)
	{
		// Create the user parameters object
		$this->_params = new JParameter( '' );

		// Load the user if it exists
		if (!empty($identifier)) {
			$this->load($identifier);
		} else {
			//initialise
			$this->id        = 0;
			$this->gid       = 0;
			$this->sendEmail = 1;
			$this->aid       = 0;
			$this->guest     = 1;
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
	function &getInstance($id = 0)
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
				JError::raiseWarning( 'SOME_ERROR_CODE', 'JUser::_load: User '.$id.' does not exist' );
				return false;
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
	function getParam( $key, $default = null )
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
	function setParam( $key, $value )
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
	function defParam( $key, $value )
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
	function authorize( $acoSection, $aco, $axoSection = null, $axo = null )
	{
		$acl = & JFactory::getACL();
		return $acl->acl_check( $acoSection, $aco,	'users', $this->usertype, $axoSection, $axo );
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
		$table 	=& JTable::getInstance( 'user');
		$table->load($this->id);

		return $table->setLastVisit($timestamp);
	}

	/**
	 * Method to get the user parameters
	 *
	 * @access 	public
	 * @return	object	The user parameters object
	 * @since	1.5
	 */
	function &getParameters()
	{
		return $this->_params;
	}

	/**
	 * Method to get the user table object
	 *
	 * @access 	public
	 * @return	object	The user table object
	 * @since	1.5
	 */
	function &getTable()
	{
		// Create the user table object
		$table 	=& JTable::getInstance( 'user');
		$table->load($this->id);

		return $table;
	}

	/**
	 * Method to set the user parameters
	 *
	 *
	 * @access 	public
	 * @param 	string 	$data 	The paramters string in INI format
	 * @param 	string 	$path 	Path to the parameters xml file [optional]
	 * @since 	1.5
	 */
	function setParameters($data, $path = null)
	{
		// Assume we are using the xml file from com_users if no other xml file has been set
		if (is_null($path)) 
		{
			jimport( 'joomla.application.helper' );
			$path 	= JApplicationHelper::getPath( 'com_xml', 'com_users' );
		}

		$this->_params->loadSetupFile($path);
		$this->_params->loadINI($data);
	}

	/**
	 * Method to get JUser error message
	 *
	 * @access 	public
	 * @return	string	The error message
	 * @since	1.5
	 */
	function getError() {
		return $this->_errorMsg;
	}

	/**
	 * Method to bind an associative array of data to a user object
	 *
	 * @access 	private
	 * @param 	array 	$array 	The associative array to bind to the object
	 * @return 	boolean 		True on success
	 * @since 1.5
	 */
	function bind(& $array)
	{
		jimport('joomla.user.helper');
		jimport( 'joomla.utilities.array' );

		// Lets check to see if the user is new or not
		if (empty($this->id) /*&& $array['id']*/)
		{
			/*
			 * Since we have a new user, and we are going to create it... we
			 * need to check a few things and set some defaults if we don't
			 * already have them.
			 */

			// First the password
			if (empty($array['password'])) {
				$array['password'] = JUserHelper::genRandomPassword();
			}
			$this->clearPW = JArrayHelper::getValue( $array, 'password', '', 'string' );
			$array['password'] = JUserHelper::getCryptedPassword($array['password']);

			// Next the registration timestamp
			$this->set( 'registerDate', date( 'Y-m-d H:i:s' ) );

			// check that username is not greater than 25 characters
			$username = $this->get( 'username' );
			if ( strlen($username) > 150 )
			{
				$username = substr( $username, 0, 150 );
				$this->set( 'username', $username );
			}

			// check that password is not greater than 50 characters
			$password = $this->get( 'password' );
			if ( strlen($password) > 100 )
			{
				$password = substr( $password, 0, 100 );
				$this->set( 'password', $password );
			}
		}
		else
		{
			// We are updating an existing user.. so lets get down to it.
			if (!empty($array['password']))
			{
				$this->clearPW = JArrayHelper::getValue( $array, 'password', '', 'string' );
				$array['password'] = JUserHelper::getCryptedPassword($array['password']);
			}
			else
			{
				$array['password'] = $this->password;
			}
		}

		/*
		 * NOTE
		 * TODO
		 * @todo: this will be deprecated as of the ACL implementation
		 */
		$db =& JFactory::getDBO();

		$gid	= array_key_exists('gid', $array ) ? $array['gid'] : $this->get('gid');

		$query = 'SELECT name'
		. ' FROM #__core_acl_aro_groups'
		. ' WHERE id = ' . (int) $gid
		;
		$db->setQuery( $query );
		$this->set( 'usertype', $db->loadResult());

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

		/*
		 * Lets first try to bind the array to us... if that fails
		 * then we can certainly fail the whole method as we've done absolutely
		 * no good :)
		 */
		if (!$this->_bind($array, 'aid guest')) {
			$this->_setError("Unable to bind array to user object");
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
	function save( $updateOnly = false )
	{
		jimport( 'joomla.utilities.array' );

		// Create the user table object
		$table 	=& JTable::getInstance( 'user');
		$table->bind(JArrayHelper::fromObject($this, false));

		/*
		 * We need to get the JUser object for the current installed user, but
		 * might very well be modifying that user... and isn't it ironic...
		 * don't ya think?
		 */
		$me =& JFactory::getUser();

		/*
		 * Now that we have gotten all the field handling out of the way, time
		 * to check and store the object.
		 */
		if (!$table->check())
		{
			$this->_setError($table->getError());
			return false;
		}

		// if user is made a Super Admin group and user is NOT a Super Admin
		if ( $this->get('gid') == 25 && $me->get('gid') != 25 )
		{
			// disallow creation of Super Admin by non Super Admin users
			$this->_setError(JText::_( 'WARNSUPERADMINCREATE' ));
			return false;
		}

		//are we creating a new user
		$isnew = !$this->id;

		// If we aren't allowed to create new and we are  about to... return true .. job done
		if ($isnew && $updateOnly) {
			return true;
		}

		/*
		 * Since we have passed all checks lets load the user plugin group and
		 * fire the onBeforeStoreUser event.
		 */
		JPluginHelper::importPlugin( 'user' );
		$dispatcher =& JEventDispatcher::getInstance();
		$dispatcher->trigger( 'onBeforeStoreUser', array( get_object_vars( $table ), $isnew ) );

		/*
		 * Time for the real thing... are you ready for the real thing?  Store
		 * the JUserModel ... if a fail condition exists throw a warning
		 */
		$result = false;
		if (!$result = $table->store()) {
			$this->_setError($table->getError());
		}

		/*
		 * If the id is not set, lets set the id for the JUser object.  This
		 * might happen if we just inserted a new user... and need to update
		 * this objects id value with the inserted id.
		 */
		if (empty($this->id)) {
			$this->id = $table->get( 'id' );
		}

		// We stored the user... lets tell everyone about it.
		$dispatcher->trigger( 'onAfterStoreUser', array( get_object_vars( $table ), $isnew, $result, $this->getError() ) );

		return $result;
	}

	/**
	 * Method to delete the JUser object from the database
	 *
	 * @access 	private
	 * @param 	boolean $updateOnly Save the object only if not a new user
	 * @return 	boolean 			True on success
	 * @since 1.5
	 */
	function delete( )
	{
		JPluginHelper::importPlugin( 'user' );

		//trigger the onBeforeDeleteUser event
		$dispatcher =& JEventDispatcher::getInstance();
		$dispatcher->trigger( 'onBeforeDeleteUser', array( array( 'id' => $this->id ) ) );

		// Create the user table object
		$table 	=& JTable::getInstance( 'user');

		$result = false;
		if (!$result = $table->delete($this->id)) {
			$this->_setError($table->getError());
		}

		//trigger the onAfterDeleteUser event
		$dispatcher->trigger( 'onAfterDeleteUser', array( array('id' => $this->id), $result, $this->getError()) );
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
		$table 	=& JTable::getInstance( 'user');

		 // Load the JUserModel object based on the user id or throw a warning.
		 if(!$table->load($id))
		 {
			JError::raiseWarning( 'SOME_ERROR_CODE', 'JUser::_load: Unable to load user with id: '.$id );
			return false;
		}

		/*
		 * Set the user parameters using the default xml file.  We might want to
		 * extend this in the future to allow for the ability to have custom
		 * user parameters, but for right now we'll leave it how it is.
		 */
		$this->_params->loadINI($table->params);

		// Assuming all is well at this point lets bind the data
		$this->_bind(JArrayHelper::fromObject($table, false));

		return true;
	}

	/**
	* Binds a named array/hash to this object
	*
	* @access	protected
	* @param	$array  mixed Either and associative array or another object
	* @param	$ignore string	Space separated list of fields not to bind
	* @return	boolean
	* @since	1.5
	*/
	function _bind( $from, $ignore='' )
	{
		if (!is_array( $from ) && !is_object( $from )) {
			$this->_setError(strtolower(get_class( $this ))."::bind failed.");
			return false;
		}

		$fromArray  = is_array( $from );
		$fromObject = is_object( $from );

		if ($fromArray || $fromObject)
		{
			foreach (get_object_vars($this) as $k => $v)
			{
				// only bind to public variables
				if( substr( $k, 0, 1 ) != '_' )
				{
					// internal attributes of an object are ignored
					if (strpos( $ignore, $k) === false)
					{
						$ak = $k;

						if ($fromArray && isset( $from[$ak] )) {
							$this->$k = $from[$ak];
						} else if ($fromObject && isset( $from->$ak )) {
							$this->$k = $from->$ak;
						}
					}
				}
			}
		}
		else
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to set an error message
	 *
	 * @access	private
	 * @param	string	$msg	The message to append to the error message
	 * @return	void
	 * @since	1.5
	 */
	function _setError( $msg )
	{
		$this->_errorMsg .= $msg."\n";
	}
}
