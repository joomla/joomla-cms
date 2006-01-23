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

/**
 * User class.  Handles all application interaction with a user
 *
 * @author 		Louis Landry <louis@webimagery.net>
 * @package 	Joomla.Framework
 * @since 1.1
 */
class JUser extends JObject
{
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
	* Constructor activating the default information of the language
	*
	* @access 	protected
	*/
	function __construct($id = null) 
	{
		global $mainframe;
		
		$db				= & $mainframe->getDBO();
		$this->_model 	= JModel :: getInstance( 'user', $db );
		$this->_params	= new JUserParameters();

		if (!is_null($id))
		{
			$this->_load($id);
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
	function & getInstance($id) 
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$id])) {
			$instances[$id] = new JUser($id);
		}

		return $instances[$id];
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
	function set( $property, $value=null ) {
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
	function get($property, $default=null) {
		if(isset($this->_model->$property)) {
			return $this->_model->$property;
		}
		return $default;
	}


	/**
	 * Method to activate/deactivate the user
	 *
	 * @param	boolean	$activated	True to activate False to deactivate
	 * @return 	boolean 			True on success
	 * @since	1.1
	 */
	function activate($activated = true)
	{
		/*
		 * Load the user plugins and fire the onActivate event
		 */
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
	function getParam( $key, $default )
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
	 * @since	1.1
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
	 * @since	1.1
	 */
	function defParam( $key, $value )
	{
		return $this->_params->def( $key, $value );	
	}

	/**
	 * Method to get the user parameters
	 * 
	 * @access 	public
	 * @return	object	The user parameters object
	 * @since	1.1
	 */
	function getParameters()
	{
		return $this->_params;	
	}

	/**
	 * Method to load a JUser object by user id number
	 * 
	 * @access 	private
	 * @param 	int 	$id 	The user id for the user to load
	 * @param 	string 	$path 	Path to a parameters xml file
	 * @return 	boolean 			True on success
	 * @since 1.1
	 */
	function _load($id)
	{
		/*
		 * Load the JUserModel object based on the user id or throw a warning.
		 */
		if (!$this->_model->load($id))
		{
			JError :: raiseWarning( 'SOME_ERROR_CODE', 'JUser::_load: Unable to load user with id: '.$id );
			return false;
		}
		
		/*
		 * Set the user parameters using the default xml file.  We might want to
		 * extend this in the future to allow for the ability to have custom
		 * user parameters, but for right now we'll leave it how it is.
		 */
		$this->_params->setParams( $this->_model->params );
		
		return true;
	}
}

/**
 * User parameters class.  Extended JParameters class to handle special
 * parameters necessary for a user object.
 *
 * @package 	Joomla.Framework
 * @since 1.1
 */
class JUserParameters extends JParameters
{
	/**
	 * This method emulates the constructor so that we can create an empty
	 * object and load it later on.
	 * 
	 * @access public
	 * @param string $text The paramters string in INI format
	 * @param string $path Path to the parameters xml file [optional]
	 * @param string $type Type of parameters [optional]
	 */
	function setParams($text, $path = null, $type = 'component')
	{
		/*
		 * If we are not fed a path of an xml file for parameters then we should
		 * assume we are using the xml file from com_users.
		 */
		if (is_null($path))
		{
			$path 	= JApplicationHelper::getPath( 'com_xml', 'com_users' );
		}
		// Emulate the constructor
		$this->_params = $this->parse($text);
		$this->_raw = $text;
		$this->_path = $path;
		$this->_type = $type;
	}

	/**
	 * Render an editor list parameter select
	 * 
	 * @access private
	 * @param string The name of the form element
	 * @param string The value of the element
	 * @param object The xml element for the parameter
	 * @param string The control name
	 * @return string The html for the element
	 * @since 1.0
	 */
	function _form_editor_list( $name, $value, &$node, $control_name )
	{
		global $mainframe;

		$db	= & $mainframe->getDBO();
		$my	= & $mainframe->getUser();

		/* 
		 * @todo: change to acl_check method
		 */
		if(!($my->gid >= 20) ) {
			return JText::_('No Access');
		}

		// compile list of the editors
		$query = "SELECT element AS value, name AS text"
		. "\n FROM #__plugins"
		. "\n WHERE folder = 'editors'"
		. "\n AND published = 1"
		. "\n ORDER BY ordering, name"
		;
		$db->setQuery( $query );
		$editors = $db->loadObjectList();

		array_unshift( $editors, mosHTML::makeOption( '', '- '. JText::_( 'Select Editor' ) .' -' ) );

		return mosHTML::selectList( $editors, ''. $control_name .'['. $name .']', 'class="inputbox"', 'value', 'text', $value );
	}
}
?>