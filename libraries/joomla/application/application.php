<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Application
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

// Include library dependencies
jimport('joomla.application.plugin.*');

/**
* Base class for a Joomla! application.
*
* Acts as a Factory class for application specific objects and provides many
* supporting API functions. Derived clases should supply the route(), dispatch()
* and render() functions.
*
* @abstract
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.5
*/

class JApplication extends JObject
{
	/**
	 * The client identifier.
	 *
	 * @var integer
	 * @access protected
	 * @since 1.5
	 */
	var $_clientId = null;


	/**
	 * The router object
	 *
	 * @var object  JRouter object
	 * @access protected
	 */
	var $_router = null;

	/**
	 * The application message queue.
	 *
	 * @var array
	 * @access protected
	 */
	var $_messageQueue = array();

	/**
	* Class constructor.
	*
	* @param integer	A client identifier.
	*/
	function __construct( $clientId = 0 )
	{
		$this->_clientId = $clientId;
		$this->set( 'requestTime', date('Y-m-d H:i', time()) );
	}

	/**
	* Initialise the application.
	*
	* @param	array An optional associative array of configuration settings.
	* @access public
	*/
	function initialise($options = array())
	{
		//Set the language in the class
		$conf =& JFactory::getConfig();
		$conf->setValue('config.language', $options['language']);

		//set language debug -> lazy load it later
		$lang =& JFactory::getLanguage();
		$lang->setDebug($this->getCfg('debug_lang'));

		//define date formats
		define('DATE_FORMAT_LC' , JText::_('DATE_FORMAT_LC' ));
		define('DATE_FORMAT_LC2', JText::_('DATE_FORMAT_LC2'));
		define('DATE_FORMAT_LC3', JText::_('DATE_FORMAT_LC3'));
		define('DATE_FORMAT_LC4', JText::_('DATE_FORMAT_LC4'));

		// create the backward compatible language value for old 3PD components
		if($conf->getValue('config.legacy')) {
			$GLOBALS['mosConfig_lang'] = $lang->getBackwardLang();
		}

		//create the router -> lazy load it later
		$this->_createRouter();
	}

	/**
	* Route the applicaiton.
	*
	* Routing is the process of examining the request environment to determine which
	* which component should receive the request. This component optional parameters
	* are then set in the request object to be processed when the application is being
	* dispatched
	*
	* @abstract
	* @access public
	*/
	function route()
 	{
		// get the full request URI
		$uri  =& JURI::getInstance();

		$router =& $this->getRouter();
		$router->parse($uri->toString());
 	}

 	/**
	* Dispatch the applicaiton.
	*
	* Dispatching is the process of pulling the option from the request object and
	* mapping them to a component. If the component do not exist, it handles
	* determining a default component to dispatch
	*
	* @abstract
	* @access public
	*/
 	function dispatch()
 	{

 	}

	/**
	* Render the application.
	*
	* Rendering is the process of rendering the application into the JResponse buffer
	*
	* @abstract
	* @access public
	*/
	function render()
	{

	}

	/**
	* Exit the application.
	*
	* @access	public
	* @param	int	Exit code
	*/
	function close( $code = 0 )
	{
		$session =& JFactory::getSession();
		$session->close();
		exit($code);
	}

	/**
	 * Redirect to another URL.
	 *
	 * Optionally enqueues a message in the system message queue (which will be displayed
	 * the next time a page is loaded) using the enqueueMessage method. If the headers have
	 * not been sent the redirect will be accomplished using a "301 Moved Permanently"
	 * code in the header pointing to the new location. If the headers have already been
	 * sent this will be accomplished using a JavaScript statement.
	 *
	 * @access	public
	 * @param	string	$url	The URL to redirect to.
	 * @param	string	$msg	An optional message to display on redirect.
	 * @param	string  $msgType An optional message type.
	 * @return	none; calls exit().
	 * @since	1.5
	 * @see		JApplication::enqueueMessage()
	 */
	function redirect( $url, $msg='', $msgType='message' )
	{
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
			$session->set('application.queue', $this->_messageQueue);
		}

		/*
		 * If the headers have been sent, then we cannot send an additional location header
		 * so we will output a javascript redirect statement.
		 */
		if (headers_sent()) {
			echo "<script>document.location.href='$url';</script>\n";
		} else {
			//@ob_end_clean(); // clear output buffer
			header( 'HTTP/1.1 301 Moved Permanently' );
			header( 'Location: ' . $url );
		}
		$this->close();
	}

	/**
	 * Enqueue a system message.
	 *
	 * @access	public
	 * @param	string 	$msg 	The message to enqueue.
	 * @param	string	$type	The message type.
	 * @return	void
	 * @since	1.5
	 */
	function enqueueMessage( $msg, $type = 'message' )
	{
		// For empty queue, if messages exists in the session, enqueue them first
		if (!count($this->_messageQueue))
		{
			$session =& JFactory::getSession();
			$sessionQueue = $session->get('application.queue');
			if (count($sessionQueue)) {
				$this->_messageQueue = $sessionQueue;
				$session->set('application.queue', null);
			}
		}
		// Enqueue the message
		$this->_messageQueue[] = array('message' => $msg, 'type' => strtolower($type));
	}

	/**
	 * Get the system message queue.
	 *
	 * @access	public
	 * @return	The system message queue.
	 * @since	1.5
	 */
	function getMessageQueue()
	{
		// For empty queue, if messages exists in the session, enqueue them
		if (!count($this->_messageQueue))
		{
			$session =& JFactory::getSession();
			$sessionQueue = $session->get('application.queue');
			if (count($sessionQueue)) {
				$this->_messageQueue = $sessionQueue;
				$session->set('application.queue', null);
			}
		}
		return $this->_messageQueue;
	}

	 /**
	 * Gets a configuration value.
	 *
	 * @access public
	 * @param string	The name of the value to get.
	 * @return The user state.
	 * @example application/japplication-getcfg.php Getting a configuration value
	 */
	function getCfg( $varname )
	{
		$config =& JFactory::getConfig();
		return $config->getValue('config.' . $varname);
	}

	/**
	 * Gets a user state.
	 *
	 * @access public
	 * @param string The path of the state.
	 * @return mixed The user state.
	 */
	function getUserState( $key )
	{
		$session	=& JFactory::getSession();
		$registry	=& $session->get('registry');
		if(!is_null($registry)) {
			return $registry->getValue($key);
		}
		return null;
	}

	/**
	* Sets the value of a user state variable.
	*
	* @access public
	* @param string	The path of the state.
	* @param string	The value of the variable.
	* @return mixed The previous state, if one existed.
	*/
	function setUserState( $key, $value )
	{
		$session	=& JFactory::getSession();
		$registry	=& $session->get('registry');
		if(!is_null($registry)) {
			return $registry->setValue($key, $value);
		}
		return null;
	}

	/**
	 * Gets the value of a user state variable.
	 *
	 * @access public
	 * @param string The key of the user state variable.
	 * @param string The name of the variable passed in a request.
	 * @param string The default value for the variable if not found. Optional.
	 * @return The request user state.
	 */
	function getUserStateFromRequest( $key, $request, $default = null )
	{
		//Force namespace
		$key = 'request.' . $key;

		$old_state = $this->getUserState( $key );
		$cur_state = (!is_null($old_state)) ? $old_state : $default;
		$new_state = JRequest::getVar( $request, $cur_state );
		$this->setUserState( $key, $new_state );

		return $new_state;
	}

	/**
	 * Registers a handler to a particular event group.
	 *
	 * @static
	 * @param string The event name.
	 * @param mixed The handler, a function or an instance of a event object.
	 * @return void
	 * @since 1.5
	 */
	function registerEvent($event, $handler)
	{
		$dispatcher =& JEventDispatcher::getInstance();
		$dispatcher->register($event, $handler);
	}

	/**
	 * Calls all handlers associated with an event group.
	 *
	 * @static
	 * @param string The event name.
	 * @param array An array of arguments.
	 * @return array An array of results from each function call.
	 * @since 1.5
	 */
	function triggerEvent($event, $args=null)
	{
		$dispatcher =& JEventDispatcher::getInstance();
		return $dispatcher->trigger($event, $args);
	}

	/**
	 * Login authentication function.
	 *
	 * Username and encoded password are passed the the onLoginUser event which
	 * is responsible for the user validation. A successful validation updates
	 * the current session record with the users details.
	 *
	 * Username and encoded password are sent as credentials (along with other
	 * possibilities) to each observer (authentication plugin) for user
	 * validation.  Successful validation will update the current session with
	 * the user details.
	 *
	 * @param string 	The username.
	 * @param string 	The password.
	 * @param boolean  	True, if the user login needs to be remembered by the application.
	 * @return boolean 	True on success.
	 * @access public
	 * @since 1.5
	 */
	function login($username, $password, $remember)
	{
		/*if (empty($username))  {
			return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_LOGIN_USERNAME'));
		}

		if(empty($password)) {
			return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_LOGIN_PASSWORD'));
		}*/

		// Build the credentials array
		$credentials['username'] = $username;
		$credentials['password'] = $password;

		// Get the global JAuthentication object
		jimport( 'joomla.user.authentication');
		$authenticate = & JAuthentication::getInstance();
		$response	 = $authenticate->authenticate($username, $password);

		if ($response->status === JAUTHENTICATE_STATUS_SUCCESS)
		{
			// Import the user plugin group
			JPluginHelper::importPlugin('user');

			// OK, the credentials are authenticated.  Lets fire the onLogin event
			$results = $this->triggerEvent('onLoginUser', array((array)$response, $remember));

			/*
			 * If any of the user plugins did not successfully complete the login routine
			 * then the whole method fails.
			 *
			 * Any errors raised should be done in the plugin as this provides the ability
			 * to provide much more information about why the routine may have failed.
			 */

			if (!in_array(false, $results, true)) {

				// Set the remember me cookie if enabled
				if ($remember) 
				{
					jimport('joomla.utilities.simplecrypt');
					jimport('joomla.utilities.utility');
					$crypt = new JSimpleCrypt();
					$rcookie = $crypt->encrypt($username.':|:'.$password);
					$lifetime = time() + 365*24*60*60;
					setcookie( JUtility::getHash('JLOGIN_REMEMBER'), $rcookie, $lifetime, '/' );
				}
				return true;
			}
		}

		// Trigger onLoginFailure Event
		$this->triggerEvent('onLoginFailure', array((array)$response));

		// Return the error
		return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_LOGIN_AUTHENTICATE'));
	}

	/**
	* Logout authentication function.
	*
	* Passed the current user information to the onLogoutUser event and reverts the current
	* session record back to 'anonymous' parameters.
	*
	* @access public
	*/
	function logout()
	{
		// Initialize variables
		$retval = false;

		// Get a user object from the JApplication
		$user = &JFactory::getUser();

		// Hit the user last visit field
		$user->setLastVisit();

		// Build the credentials array
		$parameters['username'] = $user->get('username');
		$parameters['id'] 	    = $user->get('id');

		// Import the user plugin group
		JPluginHelper::importPlugin('user');

		// OK, the credentials are built. Lets fire the onLogout event
		$results = $this->triggerEvent('onLogoutUser', array($parameters));

		/*
		 * If any of the authentication plugins did not successfully complete
		 * the logout routine then the whole method fails.  Any errors raised
		 * should be done in the plugin as this provides the ability to provide
		 * much more information about why the routine may have failed.
		 */
		if (!in_array(false, $results, true)) {
			setcookie( JUtility::getHash('JLOGIN_REMEMBER'), false, time() - 86400, '/' );
			return true;
		}

		// Trigger onLoginFailure Event
		$this->triggerEvent('onLogoutFailure', array($parameters));

		return false;
	}

	/**
	 * Load the user session.
	 *
	 * @access public
	 * @param string The session's name.
	 */
	function loadSession($name)
	{
		$session =& $this->_createSession($name);

		// Set user specific editor
		$user   =& JFactory::getUser();
		$editor = $user->getParam('editor', $this->getCfg('editor'));

		$config =& JFactory::getConfig();
		$config->setValue('config.editor', $editor);
	}

	/**
	 * Load the configuration
	 *
	 * @access public
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 * @since 1.5
	 */
	function loadConfiguration($file)
	{
		$config =& $this->_createConfiguration($file);

		// Set the database debug
		$db =& JFactory::getDBO();
		$db->debug( $config->debug_db );
	}

	/**
	 * Gets the name of the current template.
	 *
	 * @return string
	 */
	function getTemplate()
	{
		return '_system';
	}

	/**
	 * Return a reference to the JRouter object.
	 *
	 * @access public
	 * @return object JRouter.
	 * @since 1.5
	 */
	function &getRouter()
	{
		return $this->_router;
	}

	/**
	 * Create the configuration registry
	 *
	 * @access private
	 * @param string $file 	The path to the configuration file
	 * return object JConfig
	 */
	function &_createConfiguration($file)
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
	 * Create the user session.
	 *
	 * Old sessions are flushed based on the configuration value for the cookie
	 * lifetime. If an existing session, then the last access time is updated.
	 * If a new session, a session id is generated and a record is created in
	 * the #__sessions table.
	 *
	 * @access	private
	 * @param	string The sessions name.
	 * @return	object JSession on success. May call exit() on database error.
	 * @since	1.5
	 */
	function &_createSession( $name )
	{
		$options = array();
		$options['name'] = $name;

		$session =& JFactory::getSession($options);

		$storage = & JTable::getInstance('session');
		$storage->purge($session->getExpire() * 60);

		// Session exists and is not expired, update time in session table
		if ($storage->load($session->getId())) {
			$storage->update();
			return $session;
		}

		//Session doesn't exist yet, initalise and store it in the session table
		$session->set('registry', new JRegistry('session'));
		$session->set('user'    , new JUser());

		if (!$storage->insert( $session->getId(), $this->getClientId())) {
			die( $storage->getError());
		}

		return $session;
	}

	/**
	 * Create a JRouter object
	 *
	 * @access private
	 * @return object JRouter.
	 * @since 1.5
	 */
	function &_createRouter()
	{
		//Load the pathway object
		jimport( 'joomla.application.router' );

		$options = array();

		// Get routing mode
		$options['mode'] = $this->getCfg('sef');
		if($this->getCfg('sef_rewrite')) {
			$options['mode'] = 2;
		}
		
		// Set default router parameters
		$menu =& JMenu::getInstance();
		$item = $menu->getDefault();
			
		$options['vars']           = $item->query;
		$options['vars']['Itemid'] = $item->id;

		// Create a JRouter object
		$this->_router = JRouter::getInstance($options);

		return $this->_router;
	}

	/**
	 * Gets the client id of the current running application.
	 *
	 * @access	public
	 * @return	int A client identifier.
	 * @since		1.5
	 */
	function getClientId( )
	{
		return $this->_clientId;
	}

	/**
	 * Is admin interface?
	 *
	 * @access	public
	 * @return	boolean		True if this application is administrator.
	 * @since	1.0.2
	 */
	function isAdmin()
	{
		return ($this->_clientId == 1);
	}

	/**
	 * Is site interface?
	 *
	 * @access	public
	 * @return	boolean		True if this application is site.
	 * @since	1.5
	 */
	function isSite()
	{
		return ($this->_clientId == 0);
	}

	/**
	 * Deprecated functions
	 */

	 /**
	 * Deprecated, use JPathWay->addItem() method instead.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 * @see JPathWay::addItem()
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
	 * Deprecated, use JPathWay->getPathWayNames() method instead.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 * @see JPathWay::getPathWayNames()
	 */
	function getCustomPathWay()
	{
		return $this->_pathway->getPathWayNames();
	}

	/**
	 * Deprecated, use JDocument->get( 'head' ) instead.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 * @see JDocument
	 * @see JObject::get()
	 */
	function getHead()
	{
		$document=& JFactory::getDocument();
		return $document->get('head');
	}

	/**
	 * Deprecated, use JDocument->setMetaData instead.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 * @param string Name of the metadata tag
	 * @param string Content of the metadata tag
	 * @param string Deprecated, ignored
	 * @param string Deprecated, ignored
	 * @see JDocument::setMetaData()
	 */
	function addMetaTag( $name, $content, $prepend = '', $append = '' )
	{
		$document=& JFactory::getDocument();
		$document->setMetadata($name, $content);
	}

	/**
	 * Deprecated, use JDocument->setMetaData instead.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
		 * @param string Name of the metadata tag
		 * @param string Content of the metadata tag
	 * @see JDocument::setMetaData()
	 */
	function appendMetaTag( $name, $content )
	{
		$this->addMetaTag($name, $content);
	}

	/**
	 * Deprecated, use JDocument->setMetaData instead
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
		 * @param string Name of the metadata tag
		 * @param string Content of the metadata tag
	 * @see JDocument::setMetaData()
	 */
	function prependMetaTag( $name, $content )
	{
		$this->addMetaTag($name, $content);
	}

	/**
	 * Deprecated, use JDocument->addCustomTag instead (only when document type is HTML).
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 * @param string Valid HTML
	 * @see JDocumentHTML::addCustomTag()
	 */
	function addCustomHeadTag( $html )
	{
		$document=& JFactory::getDocument();
		if($document->getType() == 'html') {
			$document->addCustomTag($html);
		}
	}

	/**
	 * Deprecated.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 */
	function getBlogSectionCount( )
	{
		$menus = &JMenu::getInstance();
		return count($menus->getItems('type', 'content_blog_section'));
	}

	/**
	 * Deprecated.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 */
	function getBlogCategoryCount( )
	{
		$menus = &JMenu::getInstance();
		return count($menus->getItems('type', 'content_blog_category'));
	}

	/**
	 * Deprecated.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 */
	function getGlobalBlogSectionCount( )
	{
		$menus = &JMenu::getInstance();
		return count($menus->getItems('type', 'content_blog_section'));
	}

	/**
	 * Deprecated.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 */
	function getStaticContentCount( )
	{
		$menus = &JMenu::getInstance();
		return count($menus->getItems('type', 'content_typed'));
	}

	/**
	 * Deprecated.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 */
	function getContentItemLinkCount( )
	{
		$menus = &JMenu::getInstance();
		return count($menus->getItems('type', 'content_item_link'));
	}

	/**
	 * Deprecated, use JApplicationHelper::getPath instead.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 * @see JApplicationHelper::getPath()
	 */
	function getPath($varname, $user_option = null)
	{
		jimport('joomla.application.helper');
		return JApplicationHelper::getPath ($varname, $user_option);
	}

	/**
	 * Deprecated, use JURI::base() instead.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 * @see JURI::base()
	 */
	function getBasePath($client=0, $addTrailingSlash = true)
	{
		return JURI::base();
	}

	/**
	 * Deprecated, use JFactory::getUser instead.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 * @see JFactory::getUser()
	 */
	function &getUser()
	{
		$user =& JFactory::getUser();
		return $user;
	}

	/**
	 * Deprecated, use JContentHelper::getItemid instead.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 * @see JContentHelper::getItemid()
	 */
	function getItemid( $id )
	{
		require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'content.php';

		// Load the article data to know what section/category it is in.
		$article =& JTable::getInstance('content');
		$article->load($id);

		$info = JContentHelper::_getArticleMenuInfo($id, $article->catid, $article->sectionid);
		return $info->id;
	}
}