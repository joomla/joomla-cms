<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JApplication is deprecated.', JLog::WARNING, 'deprecated');

/**
 * Base class for a Joomla! application.
 *
 * Acts as a Factory class for application specific objects and provides many
 * supporting API functions. Derived clases should supply the route(), dispatch()
 * and render() functions.
 *
 * @package     Joomla.Legacy
 * @subpackage  Application
 * @since       11.1
 * @deprecated  4.0  Use JApplicationCms instead unless specified otherwise
 */
class JApplication extends JApplicationBase
{
	/**
	 * The client identifier.
	 *
	 * @var    integer
	 * @since  11.1
	 * @deprecated  4.0
	 */
	protected $_clientId = null;

	/**
	 * The application message queue.
	 *
	 * @var    array
	 * @since  11.1
	 * @deprecated  4.0
	 */
	protected $_messageQueue = array();

	/**
	 * The name of the application.
	 *
	 * @var    array
	 * @since  11.1
	 * @deprecated  4.0
	 */
	protected $_name = null;

	/**
	 * The scope of the application.
	 *
	 * @var    string
	 * @since  11.1
	 * @deprecated  4.0
	 */
	public $scope = null;

	/**
	 * The time the request was made.
	 *
	 * @var    date
	 * @since  11.1
	 * @deprecated  4.0
	 */
	public $requestTime = null;

	/**
	 * The time the request was made as Unix timestamp.
	 *
	 * @var    integer
	 * @since  11.1
	 * @deprecated  4.0
	 */
	public $startTime = null;

	/**
	 * @var    JApplicationWebClient  The application client object.
	 * @since  12.2
	 * @deprecated  4.0
	 */
	public $client;

	/**
	 * @var    array  JApplication instances container.
	 * @since  11.3
	 * @deprecated  4.0
	 */
	protected static $instances = array();

	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A configuration array including optional elements such as session
	 * session_name, clientId and others. This is not exhaustive.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function __construct($config = array())
	{
		// Set the view name.
		$this->_name = $this->getName();

		// Only set the clientId if available.
		if (isset($config['clientId']))
		{
			$this->_clientId = $config['clientId'];
		}

		// Enable sessions by default.
		if (!isset($config['session']))
		{
			$config['session'] = true;
		}

		// Create the input object
		$this->input = new JInput;

		$this->client = new JApplicationWebClient;

		$this->loadDispatcher();

		// Set the session default name.
		if (!isset($config['session_name']))
		{
			$config['session_name'] = $this->_name;
		}

		// Set the default configuration file.
		if (!isset($config['config_file']))
		{
			$config['config_file'] = 'configuration.php';
		}

		// Create the configuration object.
		if (file_exists(JPATH_CONFIGURATION . '/' . $config['config_file']))
		{
			$this->_createConfiguration(JPATH_CONFIGURATION . '/' . $config['config_file']);
		}

		// Create the session if a session name is passed.
		if ($config['session'] !== false)
		{
			$this->_createSession(self::getHash($config['session_name']));
		}

		$this->requestTime = gmdate('Y-m-d H:i');

		// Used by task system to ensure that the system doesn't go over time.
		$this->startTime = JProfiler::getmicrotime();
	}

	/**
	 * Returns the global JApplicationCms object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param   mixed   $client  A client identifier or name.
	 * @param   array   $config  An optional associative array of configuration settings.
	 * @param   string  $prefix  A prefix for class names
	 *
	 * @return  JApplicationCms  A JApplicationCms object.
	 *
	 * @since   11.1
	 * @deprecated  4.0  Use JApplicationCms::getInstance() instead
	 * @note    As of 3.2, this proxies to JApplicationCms::getInstance()
	 */
	public static function getInstance($client, $config = array(), $prefix = 'J')
	{
		return JApplicationCms::getInstance($client);
	}

	/**
	 * Initialise the application.
	 *
	 * @param   array  $options  An optional associative array of configuration settings.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function initialise($options = array())
	{
		// Set the language in the class.
		$config = JFactory::getConfig();

		// Check that we were given a language in the array (since by default may be blank).
		if (isset($options['language']))
		{
			$config->set('language', $options['language']);
		}

		// Set user specific editor.
		$user = JFactory::getUser();
		$editor = $user->getParam('editor', $this->getCfg('editor'));

		if (!JPluginHelper::isEnabled('editors', $editor))
		{
			$editor = $this->getCfg('editor');

			if (!JPluginHelper::isEnabled('editors', $editor))
			{
				$editor = 'none';
			}
		}

		$config->set('editor', $editor);

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
	 * @return  void
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function route()
	{
		// Get the full request URI.
		$uri = clone JUri::getInstance();

		$router = $this->getRouter();
		$result = $router->parse($uri);

		foreach ($result as $key => $value)
		{
			$this->input->def($key, $value);
		}

		// Trigger the onAfterRoute event.
		JPluginHelper::importPlugin('system');
		$this->triggerEvent('onAfterRoute');
	}

	/**
	 * Dispatch the application.
	 *
	 * Dispatching is the process of pulling the option from the request object and
	 * mapping them to a component. If the component does not exist, it handles
	 * determining a default component to dispatch.
	 *
	 * @param   string  $component  The component to dispatch.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function dispatch($component = null)
	{
		$document = JFactory::getDocument();

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
	 * @return  void
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function render()
	{
		$template = $this->getTemplate(true);

		$params = array('template' => $template->template, 'file' => 'index.php', 'directory' => JPATH_THEMES, 'params' => $template->params);

		// Parse the document.
		$document = JFactory::getDocument();
		$document->parse($params);

		// Trigger the onBeforeRender event.
		JPluginHelper::importPlugin('system');
		$this->triggerEvent('onBeforeRender');

		// Render the document.
		$caching = ($this->getCfg('caching') >= 2) ? true : false;
		JResponse::setBody($document->render($caching, $params));

		// Trigger the onAfterRender event.
		$this->triggerEvent('onAfterRender');
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
	 * @param   string   $url      The URL to redirect to. Can only be http/https URL
	 * @param   string   $msg      An optional message to display on redirect.
	 * @param   string   $msgType  An optional message type. Defaults to message.
	 * @param   boolean  $moved    True if the page is 301 Permanently Moved, otherwise 303 See Other is assumed.
	 *
	 * @return  void  Calls exit().
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 *
	 * @see     JApplication::enqueueMessage()
	 */
	public function redirect($url, $msg = '', $msgType = 'message', $moved = false)
	{
		// Check for relative internal links.
		if (preg_match('#^index2?\.php#', $url))
		{
			$url = JUri::base() . $url;
		}

		// Strip out any line breaks.
		$url = preg_split("/[\r\n]/", $url);
		$url = $url[0];

		/*
		 * If we don't start with a http we need to fix this before we proceed.
		 * We could validly start with something else (e.g. ftp), though this would
		 * be unlikely and isn't supported by this API.
		 */
		if (!preg_match('#^http#i', $url))
		{
			$uri = JUri::getInstance();
			$prefix = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));

			if ($url[0] == '/')
			{
				// We just need the prefix since we have a path relative to the root.
				$url = $prefix . $url;
			}
			else
			{
				// It's relative to where we are now, so lets add that.
				$parts = explode('/', $uri->toString(array('path')));
				array_pop($parts);
				$path = implode('/', $parts) . '/';
				$url = $prefix . $path . $url;
			}
		}

		// If the message exists, enqueue it.
		if (trim($msg))
		{
			$this->enqueueMessage($msg, $msgType);
		}

		// Persist messages if they exist.
		if (count($this->_messageQueue))
		{
			$session = JFactory::getSession();
			$session->set('application.queue', $this->_messageQueue);
		}

		// If the headers have been sent, then we cannot send an additional location header
		// so we will output a javascript redirect statement.
		if (headers_sent())
		{
			echo "<script>document.location.href='" . str_replace("'", "&apos;", $url) . "';</script>\n";
		}
		else
		{
			$document = JFactory::getDocument();

			jimport('phputf8.utils.ascii');

			if (($this->client->engine == JApplicationWebClient::TRIDENT) && !utf8_is_ascii($url))
			{
				// MSIE type browser and/or server cause issues when url contains utf8 character,so use a javascript redirect method
				echo '<html><head><meta http-equiv="content-type" content="text/html; charset=' . $document->getCharset() . '" />'
					. '<script>document.location.href=\'' . str_replace("'", "&apos;", $url) . '\';</script></head></html>';
			}
			else
			{
				// All other browsers, use the more efficient HTTP header method
				header($moved ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 303 See other');
				header('Location: ' . $url);
				header('Content-Type: text/html; charset=' . $document->getCharset());
			}
		}

		$this->close();
	}

	/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type. Default is message.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function enqueueMessage($msg, $type = 'message')
	{
		// For empty queue, if messages exists in the session, enqueue them first.
		if (!count($this->_messageQueue))
		{
			$session = JFactory::getSession();
			$sessionQueue = $session->get('application.queue');

			if (count($sessionQueue))
			{
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
	 * @return  array  The system message queue.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function getMessageQueue()
	{
		// For empty queue, if messages exists in the session, enqueue them.
		if (!count($this->_messageQueue))
		{
			$session = JFactory::getSession();
			$sessionQueue = $session->get('application.queue');

			if (count($sessionQueue))
			{
				$this->_messageQueue = $sessionQueue;
				$session->set('application.queue', null);
			}
		}

		return $this->_messageQueue;
	}

	/**
	 * Gets a configuration value.
	 *
	 * An example is in application/japplication-getcfg.php Getting a configuration
	 *
	 * @param   string  $varname  The name of the value to get.
	 * @param   string  $default  Default value to return
	 *
	 * @return  mixed  The user state.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function getCfg($varname, $default = null)
	{
		$config = JFactory::getConfig();

		return $config->get('' . $varname, $default);
	}

	/**
	 * Method to get the application name.
	 *
	 * The dispatcher name is by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor.
	 *
	 * @return  string  The name of the dispatcher.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function getName()
	{
		$name = $this->_name;

		if (empty($name))
		{
			$r = null;

			if (!preg_match('/J(.*)/i', get_class($this), $r))
			{
				JLog::add(JText::_('JLIB_APPLICATION_ERROR_APPLICATION_GET_NAME'), JLog::WARNING, 'jerror');
			}

			$name = strtolower($r[1]);
		}

		return $name;
	}

	/**
	 * Gets a user state.
	 *
	 * @param   string  $key      The path of the state.
	 * @param   mixed   $default  Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  The user state or null.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function getUserState($key, $default = null)
	{
		$session = JFactory::getSession();
		$registry = $session->get('registry');

		if (!is_null($registry))
		{
			return $registry->get($key, $default);
		}

		return $default;
	}

	/**
	 * Sets the value of a user state variable.
	 *
	 * @param   string  $key    The path of the state.
	 * @param   string  $value  The value of the variable.
	 *
	 * @return  mixed  The previous state, if one existed.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function setUserState($key, $value)
	{
		$session = JFactory::getSession();
		$registry = $session->get('registry');

		if (!is_null($registry))
		{
			return $registry->set($key, $value);
		}

		return null;
	}

	/**
	 * Gets the value of a user state variable.
	 *
	 * @param   string  $key      The key of the user state variable.
	 * @param   string  $request  The name of the variable passed in a request.
	 * @param   string  $default  The default value for the variable if not found. Optional.
	 * @param   string  $type     Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
	 *
	 * @return  The request user state.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none')
	{
		$cur_state = $this->getUserState($key, $default);
		$new_state = $this->input->get($request, null, $type);

		// Save the new value only if it was set in this request.
		if ($new_state !== null)
		{
			$this->setUserState($key, $new_state);
		}
		else
		{
			$new_state = $cur_state;
		}

		return $new_state;
	}

	/**
	 * Login authentication function.
	 *
	 * Username and encoded password are passed the onUserLogin event which
	 * is responsible for the user validation. A successful validation updates
	 * the current session record with the user's details.
	 *
	 * Username and encoded password are sent as credentials (along with other
	 * possibilities) to each observer (authentication plugin) for user
	 * validation.  Successful validation will update the current session with
	 * the user details.
	 *
	 * @param   array  $credentials  Array('username' => string, 'password' => string)
	 * @param   array  $options      Array('remember' => boolean)
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function login($credentials, $options = array())
	{
		// Get the global JAuthentication object.
		jimport('joomla.user.authentication');

		$authenticate = JAuthentication::getInstance();
		$response = $authenticate->authenticate($credentials, $options);

		if ($response->status === JAuthentication::STATUS_SUCCESS)
		{
			// Validate that the user should be able to login (different to being authenticated).
			// This permits authentication plugins blocking the user
			$authorisations = $authenticate->authorise($response, $options);

			foreach ($authorisations as $authorisation)
			{
				$denied_states = array(JAuthentication::STATUS_EXPIRED, JAuthentication::STATUS_DENIED);

				if (in_array($authorisation->status, $denied_states))
				{
					// Trigger onUserAuthorisationFailure Event.
					$this->triggerEvent('onUserAuthorisationFailure', array((array) $authorisation));

					// If silent is set, just return false.
					if (isset($options['silent']) && $options['silent'])
					{
						return false;
					}

					// Return the error.
					switch ($authorisation->status)
					{
						case JAuthentication::STATUS_EXPIRED:
							return JError::raiseWarning('102002', JText::_('JLIB_LOGIN_EXPIRED'));
							break;

						case JAuthentication::STATUS_DENIED:
							return JError::raiseWarning('102003', JText::_('JLIB_LOGIN_DENIED'));
							break;

						default:
							return JError::raiseWarning('102004', JText::_('JLIB_LOGIN_AUTHORISATION'));
							break;
					}
				}
			}

			// Import the user plugin group.
			JPluginHelper::importPlugin('user');

			// OK, the credentials are authenticated and user is authorised.  Let's fire the onLogin event.
			$results = $this->triggerEvent('onUserLogin', array((array) $response, $options));

			/*
			 * If any of the user plugins did not successfully complete the login routine
			 * then the whole method fails.
			 *
			 * Any errors raised should be done in the plugin as this provides the ability
			 * to provide much more information about why the routine may have failed.
			 */
			$user = JFactory::getUser();

			if ($response->type == 'Cookie')
			{
				$user->set('cookieLogin', true);
			}

			if (in_array(false, $results, true) == false)
			{
				$options['user'] = $user;
				$options['responseType'] = $response->type;

				if (isset($response->length) && isset($response->secure) && isset($response->lifetime))
				{
					$options['length'] = $response->length;
					$options['secure'] = $response->secure;
					$options['lifetime'] = $response->lifetime;
				}

				// The user is successfully logged in. Run the after login events
				$this->triggerEvent('onUserAfterLogin', array($options));
			}

			return true;
		}

		// Trigger onUserLoginFailure Event.
		$this->triggerEvent('onUserLoginFailure', array((array) $response));

		// If silent is set, just return false.
		if (isset($options['silent']) && $options['silent'])
		{
			return false;
		}

		// If status is success, any error will have been raised by the user plugin
		if ($response->status !== JAuthentication::STATUS_SUCCESS)
		{
			JLog::add($response->error_message, JLog::WARNING, 'jerror');
		}

		return false;
	}

	/**
	 * Logout authentication function.
	 *
	 * Passed the current user information to the onUserLogout event and reverts the current
	 * session record back to 'anonymous' parameters.
	 * If any of the authentication plugins did not successfully complete
	 * the logout routine then the whole method fails. Any errors raised
	 * should be done in the plugin as this provides the ability to give
	 * much more information about why the routine may have failed.
	 *
	 * @param   integer  $userid   The user to load - Can be an integer or string - If string, it is converted to ID automatically
	 * @param   array    $options  Array('clientid' => array of client id's)
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function logout($userid = null, $options = array())
	{
		// Get a user object from the JApplication.
		$user = JFactory::getUser($userid);

		// Build the credentials array.
		$parameters['username'] = $user->get('username');
		$parameters['id'] = $user->get('id');

		// Set clientid in the options array if it hasn't been set already.
		if (!isset($options['clientid']))
		{
			$options['clientid'] = $this->getClientId();
		}

		// Import the user plugin group.
		JPluginHelper::importPlugin('user');

		// OK, the credentials are built. Lets fire the onLogout event.
		$results = $this->triggerEvent('onUserLogout', array($parameters, $options));

		if (!in_array(false, $results, true))
		{
				$options['username'] = $user->get('username');
				$results = $this->triggerEvent('onUserAfterLogout', array($options));

			return true;
		}

		// Trigger onUserLoginFailure Event.
		$this->triggerEvent('onUserLogoutFailure', array($parameters));

		return false;
	}

	/**
	 * Gets the name of the current template.
	 *
	 * @param   boolean  $params  An optional associative array of configuration settings
	 *
	 * @return  mixed  System is the fallback.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function getTemplate($params = false)
	{
		$template = new stdClass;

		$template->template = 'system';
		$template->params   = new JRegistry;

		if ($params)
		{
			return $template;
		}

		return $template->template;
	}

	/**
	 * Returns the application JRouter object.
	 *
	 * @param   string  $name     The name of the application.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  JRouter  A JRouter object
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	static public function getRouter($name = null, array $options = array())
	{
		if (!isset($name))
		{
			$app = JFactory::getApplication();
			$name = $app->getName();
		}

		try
		{
			$router = JRouter::getInstance($name, $options);
		}
		catch (Exception $e)
		{
			return null;
		}

		return $router;
	}

	/**
	 * This method transliterates a string into an URL
	 * safe string or returns a URL safe UTF-8 string
	 * based on the global configuration
	 *
	 * @param   string  $string  String to process
	 *
	 * @return  string  Processed string
	 *
	 * @since   11.1
	 * @deprecated  4.0  Use JApplicationHelper::stringURLSafe instead
	 */
	static public function stringURLSafe($string)
	{
		return JApplicationHelper::stringURLSafe($string);
	}

	/**
	 * Returns the application JPathway object.
	 *
	 * @param   string  $name     The name of the application.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  JPathway  A JPathway object
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function getPathway($name = null, $options = array())
	{
		if (!isset($name))
		{
			$name = $this->_name;
		}

		try
		{
			$pathway = JPathway::getInstance($name, $options);
		}
		catch (Exception $e)
		{
			return null;
		}

		return $pathway;
	}

	/**
	 * Returns the application JPathway object.
	 *
	 * @param   string  $name     The name of the application/client.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  JMenu  JMenu object.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function getMenu($name = null, $options = array())
	{
		if (!isset($name))
		{
			$name = $this->_name;
		}

		try
		{
			$menu = JMenu::getInstance($name, $options);
		}
		catch (Exception $e)
		{
			return null;
		}

		return $menu;
	}

	/**
	 * Provides a secure hash based on a seed
	 *
	 * @param   string  $seed  Seed string.
	 *
	 * @return  string  A secure hash
	 *
	 * @since   11.1
	 * @deprecated  4.0  Use JApplicationHelper::getHash instead
	 */
	public static function getHash($seed)
	{
		return JApplicationHelper::getHash($seed);
	}

	/**
	 * Create the configuration registry.
	 *
	 * @param   string  $file  The path to the configuration file
	 *
	 * @return  JConfig  A JConfig object
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	protected function _createConfiguration($file)
	{
		JLoader::register('JConfig', $file);

		// Create the JConfig object.
		$config = new JConfig;

		// Get the global configuration object.
		$registry = JFactory::getConfig();

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
	 * @param   string  $name  The sessions name.
	 *
	 * @return  JSession  JSession on success. May call exit() on database error.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	protected function _createSession($name)
	{
		$options = array();
		$options['name'] = $name;

		switch ($this->_clientId)
		{
			case 0:
				if ($this->getCfg('force_ssl') == 2)
				{
					$options['force_ssl'] = true;
				}
				break;

			case 1:
				if ($this->getCfg('force_ssl') >= 1)
				{
					$options['force_ssl'] = true;
				}
				break;
		}

		$this->registerEvent('onAfterSessionStart', array($this, 'afterSessionStart'));

		$session = JFactory::getSession($options);
		$session->initialise($this->input, $this->dispatcher);
		$session->start();

		// TODO: At some point we need to get away from having session data always in the db.

		$db = JFactory::getDbo();

		// Remove expired sessions from the database.
		$time = time();

		if ($time % 2)
		{
			// The modulus introduces a little entropy, making the flushing less accurate
			// but fires the query less than half the time.
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__session'))
				->where($db->quoteName('time') . ' < ' . $db->quote((int) ($time - $session->getExpire())));

			$db->setQuery($query);
			$db->execute();
		}

		// Check to see the the session already exists.
		$handler = $this->getCfg('session_handler');

		if (($handler != 'database' && ($time % 2 || $session->isNew()))
			|| ($handler == 'database' && $session->isNew()))
		{
			$this->checkSession();
		}

		return $session;
	}

	/**
	 * Checks the user session.
	 *
	 * If the session record doesn't exist, initialise it.
	 * If session is new, create session variables
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function checkSession()
	{
		$db = JFactory::getDbo();
		$session = JFactory::getSession();
		$user = JFactory::getUser();

		$query = $db->getQuery(true)
			->select($db->quoteName('session_id'))
			->from($db->quoteName('#__session'))
			->where($db->quoteName('session_id') . ' = ' . $db->quote($session->getId()));

		$db->setQuery($query, 0, 1);
		$exists = $db->loadResult();

		// If the session record doesn't exist initialise it.
		if (!$exists)
		{
			$query->clear();

			if ($session->isNew())
			{
				$query->insert($db->quoteName('#__session'))
					->columns($db->quoteName('session_id') . ', ' . $db->quoteName('client_id') . ', ' . $db->quoteName('time'))
					->values($db->quote($session->getId()) . ', ' . (int) $this->getClientId() . ', ' . $db->quote((int) time()));
				$db->setQuery($query);
			}
			else
			{
				$query->insert($db->quoteName('#__session'))
					->columns(
						$db->quoteName('session_id') . ', ' . $db->quoteName('client_id') . ', ' . $db->quoteName('guest') . ', ' .
						$db->quoteName('time') . ', ' . $db->quoteName('userid') . ', ' . $db->quoteName('username')
					)
					->values(
						$db->quote($session->getId()) . ', ' . (int) $this->getClientId() . ', ' . (int) $user->get('guest') . ', ' .
						$db->quote((int) $session->get('session.timer.start')) . ', ' . (int) $user->get('id') . ', ' . $db->quote($user->get('username'))
					);

				$db->setQuery($query);
			}

			// If the insert failed, exit the application.
			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				jexit($e->getMessage());
			}
		}
	}

	/**
	 * After the session has been started we need to populate it with some default values.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 * @deprecated  4.0
	 */
	public function afterSessionStart()
	{
		$session = JFactory::getSession();

		if ($session->isNew())
		{
			$session->set('registry', new JRegistry('session'));
			$session->set('user', new JUser);
		}
	}

	/**
	 * Gets the client id of the current running application.
	 *
	 * @return  integer  A client identifier.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function getClientId()
	{
		return $this->_clientId;
	}

	/**
	 * Is admin interface?
	 *
	 * @return  boolean  True if this application is administrator.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function isAdmin()
	{
		return ($this->_clientId == 1);
	}

	/**
	 * Is site interface?
	 *
	 * @return  boolean  True if this application is site.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function isSite()
	{
		return ($this->_clientId == 0);
	}

	/**
	 * Method to determine if the host OS is  Windows
	 *
	 * @return  boolean  True if Windows OS
	 *
	 * @since   11.1
	 * @deprecated  13.3 (Platform) & 4.0 (CMS) Use the IS_WIN constant instead.
	 */
	public static function isWinOS()
	{
		JLog::add('JApplication::isWinOS() is deprecated. Use the IS_WIN constant instead.', JLog::WARNING, 'deprecated');

		return IS_WIN;
	}

	/**
	 * Determine if we are using a secure (SSL) connection.
	 *
	 * @return  boolean  True if using SSL, false if not.
	 *
	 * @since   12.2
	 * @deprecated  4.0
	 */
	public function isSSLConnection()
	{
		return ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) || getenv('SSL_PROTOCOL_VERSION'));
	}

	/**
	 * Returns the response as a string.
	 *
	 * @return  string  The response
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function __toString()
	{
		$compress = $this->getCfg('gzip', false);

		return JResponse::toString($compress);
	}
}
