<?php
/**
* @version $Id$
* @package Joomla.Framework
* @subpackage Application
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Include library dependencies
jimport('joomla.filter.input');
jimport('joomla.application.plugin.helper');

/**
* Base class for a Joomla! application
*
* Acts as a Factory class for application specific objects and
* provides many supporting API functions.
*
* @abstract
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.5
*/

class JApplication extends JObject
{
	/**
	 * The pathway store
	 *
	 * @var object  JPathWay object
	 * @access protected
	 */
	var $_pathway = null;

	/**
	 * The client identifier
	 *
	 * @var integer
	 * @access protected
	 * @since 1.5
	 */
	var $_clientId = null;

	/**
	 * The url of the application
	 *
	 * @var string
	 * @access protected
	 */
	var $_baseURL = null;

	/**
	 * The application message queue
	 *
	 * @var array
	 * @access protected
	 */
	var $_messageQueue = array();

	/**
	* Class constructor
	*
	* @param string 	The URL option passed in
	* @param integer	A client identifier
	*/
	function __construct( $clientId = 0 )
	{
		$this->_clientId = $clientId;
		$this->set( 'requestTime', date('Y-m-d H:i', time()) );
	}

	/**
	* Execute the application
	*
	* @abstract
	* @access public
	*/
	function execute( $option )
	{

	}

	/**
	 * Redirect to another URL
	 *
	 * @access	public
	 * @param	string	$url	The URL to redirect to
	 * @param	string	$msg	A message to display on redirect
	 * @since	1.5
	 */
	function redirect( $url, $msg='', $msgType='message' )
	{
		//TODO :: does this needs to be filtered here ?
		// Instantiate an input filter and process the URL and message
		$filter = & JInputFilter::getInstance();
		$url    = $filter->clean( $url );

		if (!empty($msg)) {
			$msg = $filter->clean( $msg );
		}

		if (JInputFilter::checkAttribute( array( 'href', $url ))) {
			$url = JURI::base();
		}

		// check for relative internal links
		if (preg_match( '#^index[2]?.php#', $url )) {
			$url = JURI::base() . $url;
		}

		// If the message exists, enqueue it
		if (trim( $msg )) {
			$this->enqueueMessage($msg, $msgType);
		}

		// Persist messages if they exist
		if (count($this->_messageQueue))
		{
			$session =& JFactory::getSession();
			$session->set('_JApplication_queue', $this->_messageQueue);
		}

		/*
		 * If the headers have been sent, then we cannot send an additional location header
		 * so we will output a javascript redirect statement.
		 */
		if (headers_sent()) {
			echo "<script>document.location.href='$url';</script>\n";
		} else {
			//@ob_end_clean(); // clear output buffer
			session_write_close();
			header( 'HTTP/1.1 301 Moved Permanently' );
			header( "Location: ". $url );
		}
		exit();
	}

	/**
	 * Enqueue a system message
	 *
	 * @access	public
	 * @param	string 	$msg 	The message to enqueue
	 * @param	string	$type	The message type
	 * @return	void
	 * @since	1.5
	 */
	function enqueueMessage( $msg, $type="message" )
	{
		// For empty queue, if messages exists in the session, enqueue them first
		if (!count($this->_messageQueue))
		{
			$session =& JFactory::getSession();
			$sessionQueue = $session->get('_JApplication_queue');
			if (count($sessionQueue)) {
				$this->_messageQueue = $sessionQueue;
				$session->set('_JApplication_queue', null);
			}
		}
		// Enqueue the message
		$this->_messageQueue[] = array('message' => $msg, 'type' => strtolower($type));
	}

	/**
	 * Get the system message queue
	 *
	 * @access	public
	 * @return	The system message queue
	 * @since	1.5
	 */
	function getMessageQueue()
	{
		// For empty queue, if messages exists in the session, enqueue them
		if (!count($this->_messageQueue))
		{
			$session =& JFactory::getSession();
			$sessionQueue = $session->get('_JApplication_queue');
			if (count($sessionQueue)) {
				$this->_messageQueue = $sessionQueue;
				$session->set('_JApplication_queue', null);
			}
		}
		return $this->_messageQueue;
	}

	 /**
	 * Gets a configuration value
	 *
	 * @access public
	 * @param string 	$varname 	The name of the value to get
	 * @return The user state
	 * @example application/japplication-getcfg.php
	 */
	function getCfg( $varname )
	{
		$config =& JFactory::getConfig();
		return $config->getValue('config.'.$varname);
	}

	/**
	 * Gets a user state
	 *
	 * @access public
	 * @param string 	$key 	The path of the state
	 * @return The user state
	 */
	function getUserState( $key )
	{
		$session  =& JFactory::getSession();
		$registry =& $session->get('registry');
		if(!is_null($registry)) {
			return $registry->getValue($key);
		}
		return null;
	}

	/**
	* Sets the value of a user state variable
	*
	* @access public
	* @param string $key 	The path of the state
	* @param string $value 	The value of the variable
	* @return mixed The previous state if exist
	*/
	function setUserState( $key, $value )
	{
		$session  =& JFactory::getSession();
		$registry =& $session->get('registry');
		if(!is_null($registry)) {
			return $registry->setValue($key, $value);
		}
		return null;
	}

	/**
	 * Gets the value of a user state variable
	 *
	 * @access public
	 * @param string The key of the user state variable
	 * @param string The name of the variable passed in a request
	 * @param string The default value for the variable if not found
	 * @return The request user state
	 */
	function getUserStateFromRequest( $key, $request, $default=null )
	{
		//Force namespace
		$key = 'request.'.$key;

		$old_state = $this->getUserState( $key );
		$cur_state = (!is_null($old_state)) ? $old_state : $default;
		$new_state = JRequest::getVar( $request, $cur_state );
		$this->setUserState( $key, $new_state );

		return $new_state;
	}

	/**
	 * Registers a handler to a particular event group
	 *
	 * @static
	 * @param string The event name
	 * @param mixed The handler, a function or an instance of a event object
	 * @since 1.5
	 */
	function registerEvent($event, $handler) {
		$dispatcher =& JEventDispatcher::getInstance();
		return $dispatcher->register($event, $handler);
	}

	/**
	 * Calls all handlers associated with an event group
	 *
	 * @static
	 * @param string The event name
	 * @param array An array of arguments
	 * @return array An array of results from each function call
	 * @since 1.5
	 */
	function triggerEvent($event, $args=null) {
		$dispatcher =& JEventDispatcher::getInstance();
		return $dispatcher->trigger($event, $args);
	}

	/**
	 * Login authentication function
	 *
	 * Username and encoded password are passed the the onLoginUser event who
	 * is responsible for the user validation.
	 * A successful validation updates the current session record with the
	 * users details.
	 *
	 * Username and Password are sent as credentials (along with other possibilities)
	 * to each observer (JAuthenticatePlugin) for user validation.  Successful validation will
	 * update the current session with the user details
	 *
	 * @param string The username
	 * @param string The password
	 * @return boolean True on success
	 * @access public
	 * @since 1.5
	 */
	function login($username,$password)
	{
		if (empty($username))  {
			return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_LOGIN_USERNAME'));
		}

		if(empty($password)) {
			return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_LOGIN_PASSWORD'));
		}

		// Get the global database connector object
		$db = JFactory::getDBO();

		// Build the credentials array
		$credentials['username'] = $username;
		$credentials['password'] = $password;

		// Get the global JAuthenticate object
		jimport( 'joomla.user.authenticate');
		$auth = & JAuthenticate::getInstance();
		$authenticated = $auth->authenticate($credentials);

		if ($authenticated !== false)
		{
			// Import the user plugin group
			JPluginHelper::importPlugin('user');

			// OK, the credentials are authenticated.  Lets fire the onLogin event
			$results = $this->triggerEvent( 'onLogin', $credentials);

			/*
			 * If any of the authentication plugins did not successfully complete the login
			 * routine then the whole method fails.  Any errors raised should be done in
			 * the plugin as this provides the ability to provide much more information
			 * about why the routine may have failed.
			 */
			if (!in_array(false, $results, true))
			{
				// Get the JUser object for the user to login
				$user =& JUser::getInstance( $username );

				// If the user is blocked, redirect with an error
				if ($user->get('block') == 1) {
					return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_NOLOGIN_BLOCKED'));
				}

				// Fudge the ACL stuff for now...
				// TODO: Implement ACL :)
				jimport('joomla.factory');
				$acl = &JFactory::getACL();
				$grp = $acl->getAroGroup($user->get('id'));
				$row->gid = 1;

				// ToDO: Add simple mapping based on the group table to allow positive references between content and user groups
				if ($acl->is_group_child_of($grp->name, 'Registered', 'ARO') || $acl->is_group_child_of($grp->name, 'Public Backend', 'ARO')) {
					// fudge Authors, Editors, Publishers and Super Administrators into the Special Group
					$user->set('gid', 2);
				}
				$user->set('usertype', $grp->name);

				// Register the needed session variables
				$session =& JFactory::getSession();
				$session->set('session.user.id', $user->get('id'));

				// Get the session object
				$table = & JTable::getInstance('session');
				$table->load( $session->getId());

				$table->guest 		= 0;
				$table->username 	= $user->get('username');
				$table->userid 		= intval($user->get('id'));
				$table->usertype 	= $user->get('usertype');
				$table->gid 		= intval($user->get('gid'));

				$table->update();

				// Hit the user last visit field
				$user->setLastVisit();

				// Set remember me option
				$remember = JRequest::getVar( 'remember' ); //needs to be a paramater
				if ($remember == 'yes') {
					$lifetime = time() + 365*24*60*60;
					setcookie( 'usercookie[username]', $user->get('username'), $lifetime, '/' );
					setcookie( 'usercookie[password]', $user->get('password'), $lifetime, '/' );
				}

				return true;
			}
		}
		return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_LOGIN_AUTHENTICATE'));
	}

	/**
	* Logout authentication function
	*
	* Passed the current user information to the onLogoutUser event and reverts the current
	* session record back to 'anonymous' parameters
	* @access public
	*/
	function logout()
	{
		// Initialize variables
		$retval = false;

		// Get a user object from the JApplication
		$user = JFactory::getUser();

		// Build the credentials array
		$credentials['username'] 	= $user->get('username');
		$credentials['id'] 			= $user->get('id');

		// Import the user plugin group
		JPluginHelper::importPlugin('user');

		// OK, the credentials are built. Lets fire the onLogout event
		$results = $this->triggerEvent( 'onLogout', $credentials);

		/*
		 * If any of the authentication plugins did not successfully complete the logout
		 * routine then the whole method fails.  Any errors raised should be done in
		 * the plugin as this provides the ability to provide much more information
		 * about why the routine may have failed.
		 */
		if (!in_array(false, $results, true))
		{
			$session =& JFactory::getSession();

			// Remove the session from the session table
			$table = & JTable::getInstance('session');
			$table->load( $session->getId());
			$table->destroy();

			// Destroy the php session for this user
			$session->destroy();

			$retval = true;
		}

		// Hit the user last visit field
		$user->setLastVisit();

		return $retval;
	}

	/**
	 * Set the user session
	 *
	 * @access public
	 * @param string	The sessions name
	 */
	function setSession($name)
	{
		$session =& $this->_createSession($name);

		if ($session->getState() == 'expired') {
			$this->logout();
		}
	}

	/**
	 * Set the application language
	 *
	 * @access public
	 * @param string 	The language name
	 * @since 1.5
	 */
	function setLanguage( $lang )
	{
		//Set the language in the class
		$conf =& JFactory::getConfig();
		$conf->setValue('config.language', $lang);

		//set language debug
		$lang =& JFactory::getLanguage();
		$lang->setDebug($this->getCfg('debug_lang'));

		//define date formats
		define('DATE_FORMAT_LC' , JText::_('DATE_FORMAT_LC' ));
		define('DATE_FORMAT_LC2', JText::_('DATE_FORMAT_LC2'));
		define('DATE_FORMAT_LC3', JText::_('DATE_FORMAT_LC3'));
		define('DATE_FORMAT_LC4', JText::_('DATE_FORMAT_LC4'));

		if($conf->getValue('config.legacy')) {
			// create the backward compatible language value for old 3PD components
			$GLOBALS['mosConfig_lang']  = $lang->getBackwardLang();
		}
	}

	/**
	 * Set the configuration
	 *
	 * @access public
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 * @since 1.5
	 */
	function setConfiguration($file, $type = 'config')
	{
		$config =& $this->_createConfiguration($file, $type);

		// Set the database debug
		$db =& JFactory::getDBO();
		$db->debug( $config->debug_db);

	}

	/**
	 * Gets the name of the current template
	 *
	 * @return string
	 */
	function getTemplate() {
		return '_system';
	}

	/**
	 * Return a reference to the JPathWay object
	 *
	 * @access public
	 * @return jpathway 	JPathWay object
	 * @since 1.5
	 */
	function &getPathWay() {
		return $this->_pathway;
	}

	/**
	 * Create a JPathWay object and set the home/component items of the pathway
	 *
	 * @access private
	 * @return object JPathway
	 * @since 1.5
	 */
	function &_createPathWay()
	{
		global $option, $Itemid;
		
		//Load the pathway object
		jimport( 'joomla.application.pathway' );

		// Create a JPathWay object
		$this->_pathway = new JPathWay();

		// If not on the frontpage, add the component item to the pathway
		if (($option == 'com_frontpage') || ($option == '')) {

			// Add the home item to the pathway only and it is not linked
			$this->_pathway->addItem( JText::_('Home'), '' );
		} 
		else 
		{
			// Initialize variables
			$IIDstring = null;

			// Add the home item to the pathway
			$this->_pathway->addItem( JText::_('Home'), 'index.php' );

			// Get the actual component name
			if (substr($option, 0, 4) == 'com_') {
				$comName = substr($option, 4);
			} else {
				$comName = $option;
			}
			// Handle the ItemID
			if ($Itemid) {
				$IIDstring = '&Itemid='.$Itemid;
			}

			$this->_pathway->addItem( $comName, 'index.php?option='.$option.$IIDstring);
		}

		return $this->_pathway;
	}

	/**
	 * Create the configuration registry
	 *
	 * @access private
	 * @param string $file 	The path to the configuration file
	 * @param string $type	The format type
	 * return object JConfig
	 */
	function &_createConfiguration($file, $type = 'PHP')
	{
		jimport( 'joomla.registry.registry' );

		require_once( $file );

		// Create the JConfig object
		$config = new JConfig();

		// Get the global configuration object
		$registry =& JFactory::getConfig();

		// Load the configuration values into the registry
		$registry->loadObject($config);

		return $config;
	}

	/**
	 * Create the user session
	 *
	 * Old sessions are flushed based on the configuration value for the cookie
	 * lifetime. If an existing session, then the last access time is updated.
	 * If a new session, a session id is generated and a record is created in
	 * the #__sessions table.
	 *
	 * @access	private
	 * @param	string		The sessions name
	 * @return	object 		JSession
	 * @since	1.5
	 */
	function &_createSession( $name )
	{
		$options = array();
		$options['name'] = $name;

		$session =& JFactory::getSession($options);

		$storage = & JTable::getInstance('session');
		$storage->purge( intval( $this->getCfg( 'lifetime' ) * 60) );

		if ($storage->load( $session->getId())) {
			// Session cookie exists, update time in session table
			$storage->update();
		}
		else
		{
			//create persistance store in the session
			$session->set('registry', new JRegistry('session'));

			if (!$storage->insert( $session->getId(), $this->getClientId())) {
				die( $storage->getError() );
			}

			//TODO::Fix remember me (harden and move out of function)
			//$usercookie = JRequest::getVar( 'usercookie', null, 'COOKIE' );
			//if ($usercookie) {
				// Remember me cookie exists. Login with usercookie info.
			//	$this->login( $usercookie['username'], $usercookie['password'] );
			//}
		}

		if (!is_a($session->get('registry'), 'JRegistry')) {
			// Registry has been corrupted somehow
			$session->set('registry', new JRegistry('session'));
		}

		// Set user specific editor
		//$user    =& JFactory::getUser();
		//$editor = $user->getParam('editor', $this->getCfg('editor'));

		//$config =& JFactory::getConfig();
		//$config->setValue('config.editor', $editor);

		return $session;
	}

	/**
	 * Gets the client id of the current running application
	 *
	 * @access	public
	 * @return	int			A client identifier
	 * @since		1.5
	 */
	function getClientId( ) {
		return $this->_clientId;
	}

	/**
	 * Is admin interface?
	 *
	 * @access	public
	 * @return	boolean		True if this application is administrator
	 * @since	1.0.2
	 */
	function isAdmin() {
		return ($this->_clientId == 1) ?  true : false;
	}

	/**
	 * Is site interface?
	 *
	 * @access	public
	 * @return	boolean		True of this application is site
	 * @since	1.5
	 */
	function isSite() {
		return ($this->_clientId == 0) ?  true : false;
	}

	/**
	 * Depreceated functions
	 */

	 /**
	 * Depreceated, use JPathWay->addItem() method instead
	 * @since 1.5
	 */
	function appendPathWay( $name, $link = null )
	{
		/*
		 * To provide backward compatability if no second parameter is set
		 * set it to null
		 */
		if ($link == null) {
			$link = '';
		}

		// Add item to the pathway object
		if ($this->_pathway->addItem($name, $link)) {
			return true;
		}

		return false;
  }

	 /**
 	 * Depreceated, use JPathWay->getPathWayNames() method instead
 	 * @since 1.5
 	 */
	function getCustomPathWay() {
		return $this->_pathway->getPathWayNames();
	}

	 /**
	* Depreacted, use JDocument->renderHead instead
	* @since 1.5
	*/
	 function getHead() {
		$document=& JFactory::getDocument();
		return $document->get('head');
	 }

	/**
	* Depreacted, use JDocument->setMetadata instead
	* @since 1.5
	*/
	function addMetaTag( $name, $content, $prepend='', $append='' ) {
		$document=& JFactory::getDocument();
		$document->setMetadata($name, $content);
	}

	/**
	* Depreacted, use JDocument->setMetadata instead
	* @since 1.5
	*/
	function appendMetaTag( $name, $content ) {
		$this->addMetaTag($name, $content);
	}

	/**
	* Depreacted, use JDocument->setMetadata instead
	* @since 1.5
	*/
	function prependMetaTag( $name, $content ) {
		$this->addMetaTag($name, $content);
	}

	/**
	* Depreacted, use JDocument->addCustomTag instead
	* @since 1.5
	*/
	function addCustomHeadTag( $html ) {
		$document=& JFactory::getDocument();
		return $document->addCustomTag($html);
	}

	/**
	* Depreacted
	* @since 1.5
	*/
	function getBlogSectionCount( ) {
		$menus = &JMenu::getInstance();
		return count($menus->getItems('type', 'content_blog_section'));
	}

	/**
	* Depreacted
	* @since 1.5
	*/
	function getBlogCategoryCount( ) {
		$menus = &JMenu::getInstance();
		return count($menus->getItems('type', 'content_blog_category'));
	}

	/**
	* Depreacted
	* @since 1.5
	*/
	function getGlobalBlogSectionCount( ) {
		$menus = &JMenu::getInstance();
		return count($menus->getItems('type', 'content_blog_section'));
	}

	/**
	* Depreacted
	* @since 1.5
	*/
	function getStaticContentCount( ) {
		$menus = &JMenu::getInstance();
		return count($menus->getItems('type', 'content_typed'));
	}

	/**
	* Depreacted
	* @since 1.5
	*/
	function getContentItemLinkCount( ) {
		$menus = &JMenu::getInstance();
		return count($menus->getItems('type', 'content_item_link'));
	}

	/**
	* Depreacted, use JApplicationHelper::getPath instead
	* @since 1.5
	*/
	function getPath($varname, $user_option=null) {
		jimport('joomla.application.helper');
		return JApplicationHelper::getPath ($varname, $user_option);
	}

	/**
	* Depreacted, use JURI::base() instead
	* @since 1.5
	*/
	function getBasePath($client=0, $addTrailingSlash=true) {
		return JURI::base();
	}

	/**
	* Depreacted, use JFactory::getUser instead
	* @since 1.5
	*/
	function &getUser() {
		$user =& JFactory::getUser();
		return $user;
	}
	
	/**
	 * Deprecated, use JContentHelper::getItemid instead
	 * @since 1.5
	 */
	function getItemid( $id ) {
		require_once (JPATH_SITE . '/components/com_content/helpers/content.php');
		return JContentHelper::getItemid($id);
	}
}

?>