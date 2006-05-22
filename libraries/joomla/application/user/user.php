<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.common.base.object' );
jimport( 'joomla.presentation.parameter.parameter' );

/**
 * User class.  Handles all application interaction with a user
 *
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JUser extends JObject
{
	/**
	 * User id
	 *
	 * @var int
	 */
	var $_id		= null;

	/**
	 * User table
	 *
	 * @var object
	 */
	var $_table 	= null;

	/**
	 * User parameters
	 *
	 * @var object
	 */
	var $_params 	= null;

	/**
	 * Error message
	 *
	 * @var string
	 */
	var $_errorMsg	= null;

	/**
	* Constructor activating the default information of the language
	*
	* @access 	protected
	*/
	function __construct($identifier = 'guest')
	{
		global $mainframe;

		/*
		 * Initialize variables
		 */
		$db	=& $mainframe->getDBO();

		/*
		 * Create the user table object
		 */
		$this->_table 	=& JTable::getInstance( 'user', $db );

		/*
		 * Create the user parameters object
		 */
		$this->_params = new JParameter( '' );

		if (!empty($identifier) && $identifier != 'guest') {
			$this->_load($identifier);
		}
	}

	/**
	 * Returns a reference to the global User object, only creating it if it
	 * doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $user = JUser::getInstance($id);</pre>
	 *
	 * @access 	public
	 * @param 	int 	$id 	The user id to load - if int then the id field is referend, for strings the username!
	 * @return 	JUser  			The User object.
	 * @since 	1.5
	 */
	function & getInstance($id = 'guest')
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$id])) {
			$user = new JUser($id);
			if( is_string( $id ) ) {
				$id = $user->get( 'id' );
			}
			// using existing user with correct id (might have been modified!
			if (empty ($instances[$id])) {
				$instances[ $id ] = $user;
			}
		}

		return $instances[$id];
	}

	/**
	 * Overridden set method to pass properties on to the user table
	 *
	 * @access	public
	 * @param	string	$property	The name of the property
	 * @param	mixed	$value		The value of the property to set
	 * @return	void
	 * @since	1.5
	 */
	function set( $property, $value=null )
	{
		$this->_table->$property = $value;
	}

	/**
	 * Overridden get method to get properties from the user table
	 *
	 * @access	public
	 * @param	string	$property	The name of the property
	 * @param	mixed	$value		The value of the property to set
	 * @return 	mixed 				The value of the property
	 * @since	1.5
	 */
	function get($property, $default=null)
	{
		if(isset($this->_table->$property)) {
			return $this->_table->$property;
		}
		return $default;
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
	function getParam( $key, $default = null ) {
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
	function setParam( $key, $value ) {
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
	function defParam( $key, $value ) {
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
		return $acl->acl_check( $acoSection, $aco,	'users', $this->get('usertype'), $axoSection, $axo );
	}

	/**
	 * Pass through method to the table for setting the last visit date
	 *
	 * @access 	public
	 * @param	int		$timestamp	The timestamp, defaults to 'now'
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function setLastVisit($timestamp=null) {
		return $this->_table->setLastVisit($timestamp);
	}

	/**
	 * Method to get the user parameters
	 *
	 * @access 	public
	 * @return	object	The user parameters object
	 * @since	1.5
	 */
	function getParameters() {
		return $this->_params;
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
		/*
		 * If we are not fed a path of an xml file for parameters then we should
		 * assume we are using the xml file from com_users.
		 */
		if (is_null($path)) {
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
		jimport('joomla.application.user.authenticate');

		/*
		 * Lets check to see if the user is new or not
		 */
		if (empty($this->_table->id) && empty($this->_id) /*&& $array['id']*/) {
			/*
			 * Since we have a new user, and we are going to create it... we
			 * need to check a few things and set some defaults if we don't
			 * already have them.
			 */
			//die("HERE");
			// First the password
			if (empty($array['password'])) {
				$array['password'] = JAuthenticateHelper::genRandomPassword();
			}
			$array['password'] = JAuthenticateHelper::getCryptedPassword($array['password']);

			// Next the registration timestamp
			$this->set( 'registerDate', date( 'Y-m-d H:i:s' ) );

			// check that username is not greater than 25 characters
			$username = $this->get( 'username' );
			if ( strlen($username) > 25 ) {
				$username = substr( $username, 0, 25 );
				$this->set( 'username', $username );
			}

			// check that password is not greater than 50 characters
			$password = $this->get( 'password' );
			if ( strlen($password) > 50 ) {
				$password = substr( $password, 0, 50 );
				$this->set( 'password', $password );
			}
		} else {
			/*
			 * We are updating an existing user.. so lets get down to it.
			 */
			if (!empty($array['password'])) {
				$array['password'] = JAuthenticateHelper::getCryptedPassword($array['password']);
			} else {
				$array['password'] = $this->get('password');
			}
		}

		/*
		 * NOTE
		 * TODO
		 * @todo: this will be deprecated as of the ACL implementation
		 */
		$query = "SELECT name"
		. "\n FROM #__core_acl_aro_groups"
		. "\n WHERE id = " . $array['gid']
		;
		$this->_table->_db->setQuery( $query );
		$this->set( 'usertype', $this->_table->_db->loadResult());


		/*
		 * Lets first try to bind the array to the user table... if that fails
		 * then we can certainly fail the whole method as we've done absolutely
		 * no good :)
		 */
		if (!$this->_table->bind($array)) {
			$this->_setError("JUser::bind: Unable to bind array to user object");
			return false;
		}

		/*
		 * We were able to bind the array to the object, so now lets run
		 * through the parameters and build the INI parameter string for the
		 * table
		 */
		$this->_params->loadINI($this->_table->params);

		/*
		 * If the table user id is set, lets set the id for the JUser object.
		 */
		if ($this->get( 'id' )) {
			$this->_id = $this->get( 'id' );
		}

		return true;
	}

	/**
	 * Method to save the JUser object to the database
	 *
	 * @access 	private
	 * @param 	boolean $updateOnly Save the object only if not a new user
	 * @return 	boolean 			True on success
	 * @since 1.5
	 */
	function save( $updateOnly = false )
	{
		global $mainframe;

		/*
		 * We need to get the JUser object for the current installed user, but
		 * might very well be modifying that user... and isn't it ironic...
		 * don't ya think?
		 */
		$me = & $mainframe->getUser();

		/*
		 * Now that we have gotten all the field handling out of the way, time
		 * to check and store the object.
		 */
		if (!$this->_table->check()) {
			$this->_setError("JUser::save: ".$this->_table->getError());
			return false;
		}

		/*
		 * Since we have passed all checks lets load the user plugin group and
		 * fire the onBeforeStoreUser event.
		 */
		JPluginHelper::importPlugin( 'user' );
		$mainframe->triggerEvent( 'onBeforeStoreUser', array( get_object_vars( $this->_table ), $this->_table->id ) );

		/*
		 * Time for the real thing... are you ready for the real thing?  Store
		 * the JUserModel ... if a fail condition exists throw a warning
		 */
		$result = false;
		if (!$result = $this->_table->store()) {
			$this->_setError("JUser::save: ".$this->_table->getError());
		}

		/*
		 * If we have just updated ourselves, lets modify our session
		 * parameters... i know a little too "inside the matrix" for some...
		 */
		if ( $me->get('id') == $this->get('id') ) {
			JSession::set('session_user_params', $this->get( 'params' ));
		}

		/*
		 * If the id is not set, lets set the id for the JUser object.  This
		 * might happen if we just inserted a new user... and need to update
		 * this objects id value with the inserted id.
		 */
		if (empty($this->_id)) {
			$this->_id = $this->get( 'id' );
		}

		/*
		 * We stored the user... lets tell everyone about it.
		 */
		$mainframe->triggerEvent( 'onAfterStoreUser', array( get_object_vars( $this->_table ), $this->_table->id, $result, $this->getError() ) );

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
		global $mainframe;

		echo $this->_id;

		//trigger the onBeforeDeleteUser event
		$mainframe->triggerEvent( 'onBeforeDeleteUser', array( array( 'id' => $this->_id ) ) );

		$result = false;
		if (!$result = $this->_table->delete($this->_id)) {
			$this->_setError("JUser::delete: ".$this->_table->getError());
		}

		//trigger the onAfterDeleteUser event
		$mainframe->triggerEvent( 'onAfterDeleteUser', array( array('id' => $this->_id), $result, $this->getError()) );
		return $result;

	}

	/**
	 * Method to load a JUser object by user id number
	 *
	 * @access 	protected
	 * @param 	mixed 	$identifier The user id or username for the user to load
	 * @param 	string 	$path 		Path to a parameters xml file
	 * @return 	boolean 			True on success
	 * @since 1.5
	 */
	function _load($identifier)
	{
		 /*
		 * Find the user id
		 */
		if(!is_int($identifier))
		{
			if (!$id =  $this->_table->getUserId($identifier)) {
				JError::raiseWarning( 'SOME_ERROR_CODE', 'JUser::_load: User '.$identifier.' does not exist' );
				return false;
			}
		}
		else
		{
			$id = $identifier;
		}

		 /*
		 * Load the JUserModel object based on the user id or throw a warning.
		 */
		 if(!$this->_table->load($id)) {
			JError::raiseWarning( 'SOME_ERROR_CODE', 'JUser::_load: Unable to load user with id: '.$id );
			return false;
		}

		/*
		 * Set the user parameters using the default xml file.  We might want to
		 * extend this in the future to allow for the ability to have custom
		 * user parameters, but for right now we'll leave it how it is.
		 */
		$this->_params->loadINI($this->_table->params);

		/*
		 * Assuming all is well at this point, we set the private id field
		 */
		$this->_id = $this->_table->id;

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
	function _setError( $msg ) {
		$this->_errorMsg .= $msg."\n";
	}
}

/**
 * Helper class for the JUser class.  Performs various tasks in correlation with
 * the JUser class that don't logically fit inside the JUser object
 *
 * @static
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JUserHelper {

	/**
	 * Method to activate a user
	 *
	 * @param	string	$activation	Activation string
	 * @return 	boolean 			True on success
	 * @since	1.5
	 */
	function activateUser($activation)
	{
		global $mainframe;
		/*
		 * Initialize some variables
		 */
		$db = & $mainframe->getDBO();


		/*
		 * Lets get the id of the user we want to activate
		 */
		$query = "SELECT id"
		. "\n FROM #__users"
		. "\n WHERE activation = '$activation'"
		. "\n AND block = 1"
		;
		$db->setQuery( $query );
		$id = intval( $db->loadResult() );

		// Is it a valid user to activate?
		if ($id) {

			$user = JUser::getInstance( (int) $id );

			$user->set('block', '0');
			$user->set('activation', '');

			/*
			 * Time to take care of business.... store the user.
			 */
			if (!$user->save()) {
				JError::raiseWarning( "SOME_ERROR_CODE", "JUserHelper::activateUser: ".$user->getError() );
				return false;
			}
		} else {
			JError::raiseWarning( "SOME_ERROR_CODE", "JUserHelper::activateUser: ".JText::_('Unable to find a user with given activation string.') );
			return false;
		}

		return true;
	}
}
?>