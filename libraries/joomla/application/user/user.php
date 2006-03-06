<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.common.base.object' );
jimport( 'joomla.parameter.parameter' );

/**
 * User class.  Handles all application interaction with a user
 *
 * @author 		Louis Landry <louis@webimagery.net>
 * @package 	Joomla.Framework
 * @subpackage	Application
 * @since		1.1
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
	 * User model
	 * 
	 * @var object
	 */	
	var $_model 	= null;
	
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
	function __construct($identifier) 
	{
		global $mainframe;
		
		/*
		 * Initialize variables
		 */
		$db	=& $mainframe->getDBO();

		/*
		 * Create the user model object
		 */
		$this->_model 	=& JModel::getInstance( 'user', $db );
		
		/*
		 * Create the user parameters object
		 */
		$path 	= JApplicationHelper::getPath( 'com_xml', 'com_users' );
		$this->_params = new JParameter( '', $path );
		
		if (!empty($identifier) && $identifier != 'guest') {	
			$this->_load($identifier);
		}
	}

	/**
	 * Returns a reference to the global User object, only creating it if it
	 * doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $user = JUser :: getInstance($id);</pre>
	 *
	 * @access 	public
	 * @param 	int 	$id 	The user id to load.
	 * @return 	JUser  			The User object.
	 * @since 	1.1
	 */
	function & getInstance($username = 'guest') 
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$username])) {
			$instances[$username] = new JUser($username);
		}

		return $instances[$username];
	}

	/**
	 * Overridden set method to pass properties on to the user model
	 * 
	 * @access	public
	 * @param	string	$property	The name of the property
	 * @param	mixed	$value		The value of the property to set
	 * @return	void
	 * @since	1.1
	 */
	function set( $property, $value=null ) 
	{
		$this->_model->$property = $value;
	}

	/**
	 * Overridden get method to get properties from the user model
	 * 
	 * @access	public
	 * @param	string	$property	The name of the property
	 * @param	mixed	$value		The value of the property to set
	 * @return 	mixed 				The value of the property
	 * @since	1.1
	 */
	function get($property, $default=null) 
	{
		if(isset($this->_model->$property)) {
			return $this->_model->$property;
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
	 * @since	1.1
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
	 * @since	1.1
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
	 * @since	1.1
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
	 * @since	1.1
	 */
	function authorize( $acoSection, $aco, $axoSection = null, $axo = null )
	{
		$acl = & JFactory::getACL();
		return $acl->acl_check( $acoSection, $aco,	'users', $this->get('usertype'), $axoSection, $axo );
	}

	/**
	 * Pass through method to the model for setting the last visit date
	 * 
	 * @access 	public
	 * @param	int		$timestamp	The timestamp, defaults to 'now'
	 * @return	boolean	True on success
	 * @since	1.1
	 */
	function setLastVisit($timestamp=null) {
		return $this->_model->setLastVisit($timestamp);
	}
	
	/**
	 * Method to get the user parameters
	 * 
	 * @access 	public
	 * @return	object	The user parameters object
	 * @since	1.1
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
	 * @since 	1.1
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
	 * @since	1.1
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
	 * @since 1.1
	 */
	function bind(& $array)
	{
		/*
		 * Lets first try to bind the array to the user model... if that fails
		 * then we can certainly fail the whole method as we've done absolutely
		 * no good :)
		 */
		if (!$this->_model->bind($array))
		{
			$this->_setError("JUser::bind: Unable to bind array to user object");
			return false;
		}
		else
		{
			/*
			 * We were able to bind the array to the object, so now lets run
			 * through the parameters and build the INI parameter string for the
			 * model
			 */
			if (array_key_exists( 'params', $array ))
			{
				$txt = array();
				foreach ( $array['params'] as $k => $v )
				{
					$txt[] = "$k=$v";
				}
				$this->_model->params = implode( "\n", $txt );
				
				/*
				 * Load the paramters for the object to be the new parameters we
				 * got from the array.
				 */
				$this->_params->loadINI($this->_model->params);
			}
		}
		
		/*
		 * If the model user id is set, lets set the id for the JUser object.
		 */
		if ($this->get( 'id' ))
		{
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
	 * @since 1.1
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
		 * Lets check to see if the user is new or not
		 */
		if (empty($this->_model->id) || empty($this->_id))
		{
			// The user is new, should we create it?
			if ($updateOnly)
			{
				return false;
			}
			else
			{
				/*
				 * Since we have a new user, and we are going to create it... we
				 * need to check a few things and set some defaults if we don't
				 * already have them.
				 */
				
				// First the password
				if (!$this->get('password'))
				{
					$this->clearPW = JAuthenticateHelper::genRandomPassword();
				}
				else
				{
					$this->clearPW = $this->get('password');
				}
				$this->set('password', JAuthenticateHelper::getCryptedPassword($this->clearPW));
				
				// Next the registration timestamp
				$this->set( 'registerDate', date( 'Y-m-d H:i:s' ) );
				
				/*
				 * NOTE
				 * TODO
				 * @todo: this will be deprecated as of the ACL implementation
				 */
				$query = "SELECT name"
				. "\n FROM #__core_acl_aro_groups"
				. "\n WHERE id = " . $this->get('gid')
				;
				$this->_model->_db->setQuery( $query );
				$this->set( 'usertype', $this->_model->_db->loadResult());
			}
		}
		else
		{
			/*
			 * We are updating an existing user.. so lets get down to it.
			 */
			if (!$this->get('password'))
			{
				/*
				 * If the password is empty we set it to null
				 */
				$this->clearPW = null;
				$this->set('password', null );
			}
			else
			{
				$this->clearPW = $this->get('password');
				$this->set('password', JAuthenticateHelper::getCryptedPassword($this->clearPW));
			}

			/*
			 * NOTE
			 * TODO
			 * @todo: this will be deprecated as of the ACL implementation
			 */
			$query = "SELECT name"
			. "\n FROM #__core_acl_aro_groups"
			. "\n WHERE id = " . $this->get('gid')
			;
			$this->_model->_db->setQuery( $query );
			$this->set( 'usertype', $this->_model->_db->loadResult());
		}

		/*
		 * Now that we have gotten all the field handling out of the way, time
		 * to check and store the object.
		 */
		if (!$this->_model->check()) {
			$this->_setError("JUser::save: ".$this->_model->getError());
			return false;
		}

		/*
		 * Since we have passed all checks lets load the user plugin group and
		 * fire the onBeforeStoreUser event.
		 */
		JPluginHelper::importGroup( 'user' );
		$results = $mainframe->triggerEvent( 'onBeforeStoreUser', array( get_object_vars( $this->_model ), $this->_model->id ) );

		/*
		 * Time for the real thing... are you ready for the real thing?  Store
		 * the JUserModel ... if a fail condition exists throw a warning and
		 * return false.
		 */
		if (!$this->_model->store()) {
			$this->_setError("JUser::save: ".$this->_model->getError());
			return false;
		}
		
		/*
		 * If we have just updated ourselves, lets modify our session
		 * parameters... i know a little too "inside the matrix" for some...
		 */
		if ( $me->get('id') == $this->get('id') ) {
			JSession :: set('session_user_params', $this->get( 'params' ));
		}
	
		/*
		 * If the id is not set, lets set the id for the JUser object.  This
		 * might happen if we just inserted a new user... and need to update
		 * this objects id value with the inserted id.
		 */
		if (empty($this->_id))
		{
			$this->_id = $this->get( 'id' );
		}

		/*
		 * We stored the user... lets tell everyone about it.
		 */
		$results = $mainframe->triggerEvent( 'onAfterStoreUser', array( get_object_vars( $this->_model ), $this->_model->id ) );

		return true;
	}

	/**
	 * Method to load a JUser object by user id number
	 * 
	 * @access 	protected
	 * @param 	int 	$identifier The user id or username for the user to load
	 * @param 	string 	$path 		Path to a parameters xml file
	 * @return 	boolean 			True on success
	 * @since 1.1
	 */
	function _load($identifier)
	{
		 /*
		 * Find the user id
		 */
		if(!is_int($identifier)) 
		{
			if (!$id =  $this->_model->userExists($identifier))
			{
				JError::raiseWarning( 'SOME_ERROR_CODE', 'JUser::_load: User '.$username.' does not exist' );
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
		 if(!$this->_model->load($id))
		 {
			JError::raiseWarning( 'SOME_ERROR_CODE', 'JUser::_load: Unable to load user with id: '.$id );
			return false;
		}
			
		/*
		 * Set the user parameters using the default xml file.  We might want to
		 * extend this in the future to allow for the ability to have custom
		 * user parameters, but for right now we'll leave it how it is.
		 */
		$this->_params->loadINI($this->_model->params);
		
		/*
		 * Assuming all is well at this point, we set the private id field
		 */
		$this->_id = $this->_model->id;
		
		return true;
	}
	
	/**
	 * Method to set an error message
	 * 
	 * @access	private
	 * @param	string	$msg	The message to append to the error message
	 * @return	void
	 * @since	1.1
	 */
	function _setError( $msg )
	{
		$this->_errorMsg .= $msg."\n";
	}
}

/**
 * Helper class for the JUser class.  Performs various tasks in correlation with
 * the JUser class that don't logically fit inside the JUser object
 *
 * @static
 * @author 		Louis Landry <louis@webimagery.net>
 * @package 	Joomla.Framework
 * @subpackage	Application
 * @since		1.1
 */
class JUserHelper {

	/**
	 * Method to activate a user
	 *
	 * @param	string	$activation	Activation string
	 * @return 	boolean 			True on success
	 * @since	1.1
	 */
	function activate($activation)
	{
		global $mainframe;
		/*
		 * Initialize some variables
		 */
		$db = & $mainframe->getDBO();
		
		/*
		 * Load the user plugins
		 */
		JPluginHelper::importGroup( 'user' );

		/*
		 * Lets get the id of the user we want to activate
		 */
		$query = "SELECT id"
		. "\n FROM #__users"
		. "\n WHERE activation = '$activation'"
		. "\n AND block = 1"
		;
		$db->setQuery( $query );
		$id = $db->loadResult();
		
		// Is it a valid user to activate?
		if ($id) {
			
			$user = JModel :: getInstance( 'user', $db );
			$user->load($id);
			
			$user->set('block', '0');
			$user->set('activation', '');

			/*
			 * Time to take care of business.... store the user.
			 */
			if (!$user->store()) {
				JError::raiseWarning( "SOME_ERROR_CODE", "JUserHelper::activate: ".$user->getError() );
				return false;
			}
		} else {
			JError::raiseWarning( "SOME_ERROR_CODE", "JUserHelper::activate: ".JText::_('Unable to find a user with given activation string.') );
			return false;
		}
		$results = $mainframe->triggerEvent( 'onActivate', $id );
		return true;
	}
	
	/**
	 * Method to block a user
	 *
	 * @param	int		$id	User id to block
	 * @return 	boolean 	True on success
	 * @since	1.1
	 */
	function block($id)
	{
		global $mainframe;
		
		/*
		 * Initialize variables
		 */
		$db = & $mainframe->getDBO();
		
		/*
		 * Perform user block query
		 */
		$query = "UPDATE `#__users` " .
				"\n SET `block` = '1' " .
				"\n WHERE `id` = '$id'";
		$db->setQuery( $query );
		if (!$db->query())
		{
			return false;
		} else
		{
			/*
			 * Load the user plugins
			 */
			JPluginHelper::importGroup( 'user' );
			$results = $mainframe->triggerEvent( 'onBlock', $id );
			return true;
		}
	}
	
	/**
	 * Method to unblock a user
	 *
	 * @param	int		$id	User id to unblock
	 * @return 	boolean 	True on success
	 * @since	1.1
	 */
	function unblock($id)
	{
		global $mainframe;
		
		/*
		 * Initialize variables
		 */
		$db = & $mainframe->getDBO();
		
		/*
		 * Perform user unblock query
		 */
		$query = "UPDATE `#__users` " .
				"\n SET `block` = '0' " .
				"\n WHERE `id` = '$id'";
		$db->setQuery( $query );
		if (!$db->query())
		{
			return false;
		} else
		{
			/*
			 * Load the user plugins
			 */
			JPluginHelper::importGroup( 'user' );
			$results = $mainframe->triggerEvent( 'onUnblock', $id );
			return true;
		}
	}
}
?>