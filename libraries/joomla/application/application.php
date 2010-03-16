<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('JPATH_BASE') or die;

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
	 * @var		integer
	 * @since	1.5
	 */
	protected $_clientId = null;

	/**
	 * The application message queue.
	 *
	 * @var		array
	 */
	protected $_messageQueue = array();

	/**
	 * The name of the application.
	 *
	 * @var		array
	 */
	protected $_name = null;

	/**
	 * The scope of the application.
	 *
	 * @var		string
	 */
	public $scope = null;

	/**
	 * The time the request was made.
	 *
	 * @var		date
	 */
	public $requestTime = null;
	/**
	 * The time the request was made as Unix timestamp.
	 *
	 * @var	integer
	 * @since	1.6
	 */
	public $startTime = null;

	/**
	 * Class constructor.
	 *
	 * @param	integer	A client identifier.
	 */
	public function __construct($config = array())
	{
		jimport('joomla.utilities.utility');
		jimport('joomla.error.profiler');

		// Set the view name.
		$this->_name		= $this->getName();
		$this->_clientId	= $config['clientId'];

		// Enable sessions by default.
		if (!isset($config['session'])) {
			$config['session'] = true;
		}

		// Set the session default name.
		if (!isset($config['session_name'])) {
			$config['session_name'] = $this->_name;
		}

		// Set the default configuration file.
		if (!isset($config['config_file'])) {
			$config['config_file'] = 'configuration.php';
		}

		// Create the configuration object.
		$this->_createConfiguration(JPATH_CONFIGURATION.DS.$config['config_file']);

		// Create the session if a session name is passed.
		if ($config['session'] !== false) {
			$this->_createSession(JUtility::getHash($config['session_name']));
		}

		$this->set('requestTime', gmdate('Y-m-d H:i'));

		// Used by task system to ensure that the system doesn't go over time.
		$this->set('startTime', JProfiler::getmicrotime());
	}

	/**
	 * Returns the global JApplication object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param	mixed		A client identifier or name.
	 * @param	array		An optional associative array of configuration settings.
	 * @return	JApplication	The appliction object.
	 * @since	1.5
	 */
	public static function getInstance($client, $config = array(), $prefix = 'J')
	{
		static $instances;

		if (!isset($instances)) {
			$instances = array();
		}

		if (empty($instances[$client]))
		{
			// Load the router object.
			jimport('joomla.application.helper');
			$info = &JApplicationHelper::getClientInfo($client, true);

			$path = $info->path.DS.'includes'.DS.'application.php';
			if (file_exists($path))
			{
				require_once $path;

				// Create a JRouter object.
				$classname = $prefix.ucfirst($client);
				$instance = new $classname($config);
			}
			else
			{
				$error = JError::raiseError(500, 'Unable to load application: '.$client);
				return $error;
			}

			$instances[$client] = &$instance;
		}

		return $instances[$client];
	}

	/**
	 * Initialise the application.
	 *
	 * @param	array An optional associative array of configuration settings.
	 */
	public function initialise($options = array())
	{
		jimport('joomla.plugin.helper');

		// Set the language in the class.
		$config = &JFactory::getConfig();

		// Check that we were given a language in the array (since by default may be blank).
		if (isset($options['language'])) {
			$config->setValue('config.language', $options['language']);
		}

		// Set user specific editor.
		$user	= &JFactory::getUser();
		$editor	= $user->getParam('editor', $this->getCfg('editor'));
		$editor	= JPluginHelper::isEnabled('editors', $editor) ? $editor : $this->getCfg('editor');
		$config->setValue('config.editor', $editor);

		// Trigger the onAfterInitialise event.
		JPluginHelper::importPlugin('system');
		$this->triggerEvent('onAfterInitialise');
	}

	/**
	 * Route the application.
	 *
	 * Routing is the process of examining the request environment to determine which
	 * component should receive the request. The component optional parameters
	 * are then set in the request object to be processed when the application is being
	 * dispatched.
	 *
	 * @abstract
	 */
	public function route()
	{
		// Get the full request URI.
		$uri	= clone JURI::getInstance();

		$router = &$this->getRouter();
		$result = $router->parse($uri);

		JRequest::set($result, 'get', false);

		// Trigger the onAfterRoute event.
		JPluginHelper::importPlugin('system');
		$this->triggerEvent('onAfterRoute');
	}

	/**
	 * Dispatch the applicaiton.
	 *
	 * Dispatching is the process of pulling the option from the request object and
	 * mapping them to a component. If the component does not exist, it handles
	 * determining a default component to dispatch.
	 *
	 * @abstract
	 */
	public function dispatch($component)
	{
		$document = &JFactory::getDocument();

		$document->setTitle($this->getCfg('sitename'). ' - ' .JText::_('Administration'));
		$document->setDescription($this->getCfg('MetaDesc'));

		$contents = JComponentHelper::renderComponent($component);
		$document->setBuffer($contents, 'component');

		// Trigger the onAfterDispatch event.
		JPluginHelper::importPlugin('system');
		$this->triggerEvent('onAfterDispatch');
	}

	/**
	 * Render the application.
	 *
	 * Rendering is the process of pushing the document buffers into the template
	 * placeholders, retrieving data from the document and pushing it into
	 * the JResponse buffer.
	 *
	 * @abstract
	 */
	public function render()
	{
		$params = array(
			'template'	=> $this->getTemplate(),
			'file'		=> 'index.php',
			'directory'	=> JPATH_THEMES,
			'params'	=> $template->params
		);

		// Parse the document.
		$document = &JFactory::getDocument();
		$document->parse($params);

		// Trigger the onBeforeRender event.
		JPluginHelper::importPlugin('system');
		$this->triggerEvent('onBeforeRender');

		// Render the document.
		JResponse::setBody($document->render($this->getCfg('caching'), $params));

		// Trigger the onAfterRender event.
		$this->triggerEvent('onAfterRender');
	}

	/**
	 * Exit the application.
	 *
	 * @param	int	Exit code
	 */
	public function close($code = 0) {
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
	 * @param	string	The URL to redirect to. Can only be http/https URL
	 * @param	string	An optional message to display on redirect.
	 * @param	string  An optional message type.
	 * @param	boolean	True if the page is 301 Permanently Moved, otherwise 303 See Other is assumed.
	 * @return	none; calls exit().
	 * @since	1.5
	 * @see		JApplication::enqueueMessage()
	 */
	public function redirect($url, $msg='', $msgType='message', $moved = false)
	{
		// Check for relative internal links.
		if (preg_match('#^index2?\.php#', $url)) {
			$url = JURI::base() . $url;
		}

		// Strip out any line breaks.
		$url = preg_split("/[\r\n]/", $url);
		$url = $url[0];

		// If we don't start with a http we need to fix this before we proceed.
		// We could validly start with something else (e.g. ftp), though this would
		// be unlikely and isn't supported by this API.
		if (!preg_match('#^http#i', $url)) {
			$uri = &JURI::getInstance();
			$prefix = $uri->toString(Array('scheme', 'user', 'pass', 'host', 'port'));
			if ($url[0] == '/') {
				// We just need the prefix since we have a path relative to the root.
				$url = $prefix . $url;
			} else {
				// It's relative to where we are now, so lets add that.
				$parts = explode('/', $uri->toString(Array('path')));
				array_pop($parts);
				$path = implode('/',$parts).'/';
				$url = $prefix . $path . $url;
			}
		}


		// If the message exists, enqueue it.
		if (trim($msg)) {
			$this->enqueueMessage($msg, $msgType);
		}

		// Persist messages if they exist.
		if (count($this->_messageQueue)) {
			$session = &JFactory::getSession();
			$session->set('application.queue', $this->_messageQueue);
		}

		// If the headers have been sent, then we cannot send an additional location header
		// so we will output a javascript redirect statement.
		if (headers_sent()) {
			echo "<script>document.location.href='$url';</script>\n";
		} else {
			header($moved ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 303 See other');
			header('Location: '.$url);
		}
		$this->close();
	}

	/**
	 * Enqueue a system message.
	 *
	 * @param	string	$msg	The message to enqueue.
	 * @param	string	$type	The message type.
	 * @return	void
	 * @since	1.5
	 */
	public function enqueueMessage($msg, $type = 'message')
	{
		// For empty queue, if messages exists in the session, enqueue them first.
		if (!count($this->_messageQueue))
		{
			$session = &JFactory::getSession();
			$sessionQueue = $session->get('application.queue');
			if (count($sessionQueue)) {
				$this->_messageQueue = $sessionQueue;
				$session->set('application.queue', null);
			}
		}
		// Enqueue the message.
		$this->_messageQueue[] = array('message' => $msg, 'type' => strtolower($type));
	}

	/**
	 * Get the system message queue.
	 *
	 * @return	The system message queue.
	 * @since	1.5
	 */
	public function getMessageQueue()
	{
		// For empty queue, if messages exists in the session, enqueue them.
		if (!count($this->_messageQueue))
		{
			$session = &JFactory::getSession();
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
	 * @param	string	The name of the value to get.
	 * @param	string	Default value to return
	 * @return	mixed	The user state.
	 * @example	application/japplication-getcfg.php Getting a configuration value
	 */
	public function getCfg($varname, $default=null)
	{
		$config = &JFactory::getConfig();
		return $config->getValue('config.' . $varname, $default);
	}

	/**
	 * Method to get the application name.
	 *
	 * The dispatcher name by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor.
	 *
	 * @return	string The name of the dispatcher.
	 * @since	1.5
	 */
	public function getName()
	{
		$name = $this->_name;

		if (empty($name))
		{
			$r = null;
			if (!preg_match('/J(.*)/i', get_class($this), $r)) {
				JError::raiseError(500, "JApplication::getName() : Can\'t get or parse class name.");
			}
			$name = strtolower($r[1]);
		}

		return $name;
	}

	/**
	 * Gets a user state.
	 *
	 * @param	string	The path of the state.
	 * @return	mixed	The user state.
	 */
	public function getUserState($key)
	{
		$session	= &JFactory::getSession();
		$registry	= $session->get('registry');
		if (!is_null($registry)) {
			return $registry->getValue($key);
		}
		return null;
	}

	/**
	 * Sets the value of a user state variable.
	 *
	 * @param	string	The path of the state.
	 * @param	string	The value of the variable.
	 * @return	mixed	The previous state, if one existed.
	 */
	public function setUserState($key, $value)
	{
		$session	= &JFactory::getSession();
		$registry	= &$session->get('registry');
		if (!is_null($registry)) {
			return $registry->setValue($key, $value);
		}
		return null;
	}

	/**
	 * Gets the value of a user state variable.
	 *
	 * @param	string	The key of the user state variable.
	 * @param	string	The name of the variable passed in a request.
	 * @param	string	The default value for the variable if not found. Optional.
	 * @param	string	Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
	 * @return	The request user state.
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none')
	{
		$old_state = $this->getUserState($key);
		$cur_state = (!is_null($old_state)) ? $old_state : $default;
		$new_state = JRequest::getVar($request, null, 'default', $type);

		// Save the new value only if it was set in this request.
		if ($new_state !== null) {
			$this->setUserState($key, $new_state);
		} else {
			$new_state = $cur_state;
		}

		return $new_state;
	}

	/**
	 * Registers a handler to a particular event group.
	 *
	 * @static
	 * @param	string	The event name.
	 * @param	mixed	The handler, a function or an instance of a event object.
	 * @return	void
	 * @since	1.5
	 */
	public static function registerEvent($event, $handler)
	{
		$dispatcher = &JDispatcher::getInstance();
		$dispatcher->register($event, $handler);
	}

	/**
	 * Calls all handlers associated with an event group.
	 *
	 * @static
	 * @param	string	The event name.
	 * @param	array	An array of arguments.
	 * @return	array	An array of results from each function call.
	 * @since	1.5
	 */
	function triggerEvent($event, $args=null)
	{
		$dispatcher = &JDispatcher::getInstance();
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
	 * @param	array	Array('username' => string, 'password' => string)
	 * @param	array	Array('remember' => boolean)
	 * @return	boolean True on success.
	 * @since	1.5
	 */
	public function login($credentials, $options = array())
	{
		// Get the global JAuthentication object.
		jimport('joomla.user.authentication');

		$authenticate = & JAuthentication::getInstance();
		$response	= $authenticate->authenticate($credentials, $options);

		if ($response->status === JAUTHENTICATE_STATUS_SUCCESS)
		{
			// Import the user plugin group.
			JPluginHelper::importPlugin('user');

			// OK, the credentials are authenticated.  Lets fire the onLogin event.
			$results = $this->triggerEvent('onLoginUser', array((array)$response, $options));

			/*
			 * If any of the user plugins did not successfully complete the login routine
			 * then the whole method fails.
			 *
			 * Any errors raised should be done in the plugin as this provides the ability
			 * to provide much more information about why the routine may have failed.
			 */

			if (!in_array(false, $results, true))
			{
				// Set the remember me cookie if enabled.
				if (isset($options['remember']) && $options['remember'])
				{
					jimport('joomla.utilities.simplecrypt');
					jimport('joomla.utilities.utility');

					// Create the encryption key, apply extra hardening using the user agent string.
					$key = JUtility::getHash(@$_SERVER['HTTP_USER_AGENT']);

					$crypt = new JSimpleCrypt($key);
					$rcookie = $crypt->encrypt(serialize($credentials));
					$lifetime = time() + 365*24*60*60;

					// Use domain and path set in config for cookie if it exists.
					$cookie_domain = $this->getCfg('cookie_domain', '');
					$cookie_path = $this->getCfg('cookie_path', '/');
					setcookie( JUtility::getHash('JLOGIN_REMEMBER'), $rcookie, $lifetime, $cookie_path, $cookie_domain );
				}
				return true;
			}
		}

		// Trigger onLoginFailure Event.
		$this->triggerEvent('onLoginFailure', array((array)$response));


		// If silent is set, just return false.
		if (isset($options['silent']) && $options['silent']) {
			return false;
		}

		// Return the error.
		return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_LOGIN_AUTHENTICATE'));
	}

	/**
	 * Logout authentication function.
	 *
	 * Passed the current user information to the onLogoutUser event and reverts the current
	 * session record back to 'anonymous' parameters.
	 *
	 * @param	int	$userid		The user to load - Can be an integer or string - If string, it is converted to ID automatically
	 * @param	array	$options	Array('clientid' => array of client id's)
	 */
	public function logout($userid = null, $options = array())
	{
		// Initialise variables.
		$retval = false;

		// Get a user object from the JApplication.
		$user = &JFactory::getUser($userid);

		// Build the credentials array.
		$parameters['username']	= $user->get('username');
		$parameters['id']		= $user->get('id');

		// Set clientid in the options array if it hasn't been set already.
		if (empty($options['clientid'])) {
			$options['clientid'][] = $this->getClientId();
		}

		// Import the user plugin group.
		JPluginHelper::importPlugin('user');

		// OK, the credentials are built. Lets fire the onLogout event.
		$results = $this->triggerEvent('onLogoutUser', array($parameters, $options));

		/*
		 * If any of the authentication plugins did not successfully complete
		 * the logout routine then the whole method fails.  Any errors raised
		 * should be done in the plugin as this provides the ability to provide
		 * much more information about why the routine may have failed.
		 */
		if (!in_array(false, $results, true)) {
			// Use domain and path set in config for cookie if it exists.
			$cookie_domain = $this->getCfg('cookie_domain', '');
			$cookie_path = $this->getCfg('cookie_path', '/');
			setcookie(JUtility::getHash('JLOGIN_REMEMBER'), false, time() - 86400, $cookie_path, $cookie_domain );
			return true;
		}

		// Trigger onLoginFailure Event.
		$this->triggerEvent('onLogoutFailure', array($parameters));

		return false;
	}

	/**
	 * Gets the name of the current template.
	 *
	 * @return	string
	 */
	public function getTemplate($params = false)
	{
		if($params)
		{
			$template = new stdClass();
			$template->template = 'system';
			$template->params = new JParameter();
		}
		return 'system';
	}

	/**
	 * Returns the application JRouter object.
	 *
	 * @param	array	$options	An optional associative array of configuration settings.
	 * @return	JRouter.
	 * @since	1.5
	 */
	static public function getRouter($name = null, array $options = array())
	{
		if (!isset($name)) {
			$name = $this->_name;
		}

		jimport('joomla.application.router');
		$router = &JRouter::getInstance($name, $options);
		if (JError::isError($router)) {
			return null;
		}
		return $router;
	}

	/**
	 * This method transliterates a string into an URL
	 * safe string or returns a URL safe UTF-8 string
	 * based on the global configuration
	 *
	 * @param string	$input	String to process
	 * @return	string	Processed string
	 * @since	1.6
	 */
	static public function stringURLSafe($string)
	{
		$app = &JFactory::getApplication();
		if (self::getCfg('unicodeslugs') == 1)
		{
			$output = JFilterOutput::stringURLUnicodeSlug($string);
		} else {
			$output = JFilterOutput::stringURLSafe($string);
		}
		return $output;
	}

	/**
	 * Returns the application JPathway object.
	 *
	 * @param	array	$options	An optional associative array of configuration settings.
	 * @return	object JPathway.
	 * @since 1.5
	 */
	public function getPathway($name = null, $options = array())
	{
		if (!isset($name)) {
			$name = $this->_name;
		}

		jimport('joomla.application.pathway');
		$pathway = &JPathway::getInstance($name, $options);
		if (JError::isError($pathway)) {
			return null;
		}
		return $pathway;
	}

	/**
	 * Returns the application JPathway object.
	 *
	 * @param	array	$options	An optional associative array of configuration settings.
	 * @return	object	JMenu.
	 * @since	1.5
	 */
	public function getMenu($name = null, $options = array())
	{
		if (!isset($name)) {
			$name = $this->_name;
		}

		jimport('joomla.application.menu');
		$menu = &JMenu::getInstance($name, $options);
		if (JError::isError($menu)) {
			return null;
		}
		return $menu;
	}

	/**
	 * Provides a secure hash based on a seed
	 *
	 * @param string Seed string
	 * @return string
	 */
	public static function getHash($seed)
	{
		$conf = &JFactory::getConfig();
		return md5($conf->getValue('config.secret') .  $seed );
	}

	/**
	 * Create the configuration registry.
	 *
	 * @param	string	The path to the configuration file.
	 * return	JConfig
	 */
	protected function _createConfiguration($file)
	{
		jimport('joomla.registry.registry');

		require_once $file;

		// Create the JConfig object.
		$config = new JConfig();

		// Get the global configuration object.
		$registry = &JFactory::getConfig();

		// Load the configuration values into the registry.
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
	 * @param	string	The sessions name.
	 * @return	object	JSession on success. May call exit() on database error.
	 * @since	1.5
	 */
	protected function _createSession($name)
	{
		$options = array();
		$options['name'] = $name;
		switch($this->_clientId) {
			case 0:
				if ($this->getCfg('force_ssl') == 2) {
					$options['force_ssl'] = true;
				}
				break;
			case 1:
				if ($this->getCfg('force_ssl') >= 1) {
					$options['force_ssl'] = true;
				}
				break;
		}

		$session = JFactory::getSession($options);

		//TODO: At some point we need to get away from having session data always in the db.

		// Remove expired sessions from the database.
		$db = JFactory::getDBO();
		$db->setQuery(
			'DELETE FROM `#__session`' .
			' WHERE `time` < '.(int) (time() - $session->getExpire())
		);
		$db->query();

		// Check to see the the session already exists.
		$db->setQuery(
			'SELECT `session_id`' .
			' FROM `#__session`' .
			' WHERE `session_id` = '.$db->quote($session->getId())
		);
		$exists = $db->loadResult();

		// If the session doesn't exist initialise it.
		if (!$exists) {
			$db->setQuery(
				'INSERT INTO `#__session` (`session_id`, `client_id`, `time`)' .
				' VALUES ('.$db->quote($session->getId()).', '.(int) $this->getClientId().', '.(int) time().')'
			);

			// If the insert failed, exit the application.
			if (!$db->query()) {
				jexit($db->getErrorMSG());
			}

			//Session doesn't exist yet, initalise and store it in the session table
			$session->set('registry',	new JRegistry('session'));
			$session->set('user',		new JUser());
		}

		return $session;
	}


	/**
	 * Gets the client id of the current running application.
	 *
	 * @return	int A client identifier.
	 * @since	1.5
	 */
	public function getClientId()
	{
		return $this->_clientId;
	}

	/**
	 * Is admin interface?
	 *
	 * @return	boolean		True if this application is administrator.
	 * @since	1.0.2
	 */
	public function isAdmin()
	{
		return ($this->_clientId == 1);
	}

	/**
	 * Is site interface?
	 *
	 * @return	boolean		True if this application is site.
	 * @since	1.5
	 */
	public function isSite()
	{
		return ($this->_clientId == 0);
	}

	/**
	 * Method to determine if the host OS is  Windows
	 *
	 * @return	true if Windows OS
	 * @since	1.5
	 * @static
	 */
	static function isWinOS() {
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/**
	 * Returns the response
	 *
	 * @return	string
	 * @since	1.6
	 */
	public function __toString()
	{
		$compress = $this->getCfg('gzip', false);
		return JResponse::toString($compress);
	}
}
