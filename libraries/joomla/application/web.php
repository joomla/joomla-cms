<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

jimport('joomla.application.applicationexception');
jimport('joomla.application.input');
jimport('joomla.environment.uri');
jimport('joomla.event.dispatcher');
jimport('joomla.log.log');
jimport('joomla.registry.registry');
jimport('joomla.session.session');
jimport('joomla.user.user');

/**
 * Base class for a Joomla! Web application.
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       12.1
 */
class JWeb
{
	/**
	 * @var    JInput  The application input object.
	 * @since  12.1
	 */
	public $input;

	/**
	 * @var    string  Character encoding string.
	 * @since  12.1
	 */
	public $charSet = 'utf-8';

	/**
	 * @var    string  Response mime type.
	 * @since  12.1
	 */
	public $mimeType = 'text/html';

	/**
	 * @var    JDate  The body modified date for response headers.
	 * @since  12.1
	 */
	public $modifiedDate;

	/**
	 * @var    JRegistry  The application configuration object.
	 * @since  12.1
	 */
	protected $config;

	/**
	 * @var    JSession  The application session object.
	 * @since  12.1
	 */
	protected $session;

	/**
	 * @var    object  The application response object.
	 * @since  12.1
	 */
	protected $response;

	/**
	 * @var    JWeb  The application instance.
	 * @since  12.1
	 */
	protected static $instance;

	/**
	 * Class constructor.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function __construct()
	{
		// Get the command line options
		$this->input = new JInput();

		// Create the registry with a default namespace of config
		$this->config = new JRegistry();

		// Load the configuration object.
		$this->loadConfiguration($this->fetchConfigurationData());

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());

		// Setup the response object.
		$this->setupResponse();

		// Set the system URIs.
		$this->loadSystemURIs();
	}

	/**
	 * Returns a reference to the global JWeb object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as: $web = JWeb::getInstance();
	 *
	 * @param   string  $name  The name (optional) of the JWeb class to instantiate.
	 *
	 * @return  JWeb
	 *
	 * @since   12.1
	 */
	public static function &getInstance($name = null)
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
			if (class_exists($name) && (is_subclass_of($name, 'JWeb')))
			{
				self::$instance = new $name();
			}
			else
			{
				self::$instance = new JWeb();
			}
		}

		return self::$instance;
	}

	/**
	 * Initialise the application.
	 *
	 * @param   mixed  $session   An optional argument to provide dependency injection for the application's
	 *                            session object.  If the argument is a JSession object that object will become
	 *                            the application's session object, if it is false then there will be no session
	 *                            object, and if it is null then the default session object will be created based
	 *                            on the application's loadSession() method.
	 * @param   mixed  $language  An optional argument to provide dependency injection for the application's
	 *                            language object.  If the argument is a JLanguage object that object will become
	 *                            the application's language object, if it is false then there will be no language
	 *                            object, and if it is null then the default language object will be created based
	 *                            on the application's loadLanguage() method.
	 *
	 * @return  void
	 *
	 * @see     loadSession()
	 * @since   12.1
	 */
	public function initialise($session = null, $language = null)
	{
		// If a session object is given use it.
		if ($session instanceof JSession)
		{
			$this->session = $session;
		}
		// We don't have a session, nor do we want one.
		elseif ($session === false)
		{
			// Do nothing.
		}
		// Create the session based on the application logic.
		else
		{
			$this->loadSession();
		}

		// If a language object is given use it.
		if ($language instanceof JLanguage)
		{
			$this->language = $language;
		}
		// We don't have a language, nor do we want one.
		elseif ($language === false)
		{
			// Do nothing.
		}
		// Create the language based on the application logic.
		else
		{
			$this->loadLanguage();
		}

		// Trigger the onAfterInitialise event.
		$this->triggerEvent('onAfterInitialise');
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function execute()
	{
		/*
		 * Logic to instantiate and execute your controller goes here.
		 */

		// Trigger the onAfterExecute event.
		$this->triggerEvent('onAfterExecute');
	}

	/**
	 * Render the application.
	 *
	 * Rendering is the process of pushing the document buffers into the template
	 * placeholders, retrieving data from the document and pushing it into
	 * the response buffer.
	 *
	 * @return	void
	 * @since	1.5
	 */
	public function render()
	{
		$params = array(
			'template' => $this->get('theme'),
			'file' => 'index.php',
			'directory' => JPATH_APPLICATION . DS . 'themes',
			'params' => ''
		);

		// Parse the document.
		$document = JFactory::getDocument();
		$document->parse($params);

		// Trigger the onBeforeRender event.
		$this->triggerEvent('onBeforeRender');

		// Render the document.
		$this->setBody($document->render($this->get('cache_enabled'), $params));

		// Trigger the onAfterRender event.
		$this->triggerEvent('onAfterRender');
	}

	/**
	 * Redirect to another URL.
	 *
	 * If the headers have not been sent the redirect will be accomplished using a "301 Moved Permanently"
	 * or "303 See Other" code in the header pointing to the new location. If the headers have already been
	 * sent this will be accomplished using a JavaScript statement.
	 *
	 * @param   string   $url    The URL to redirect to. Can only be http/https URL
	 * @param   boolean  $moved  True if the page is 301 Permanently Moved, otherwise 303 See Other is assumed.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function redirect($url, $moved = false)
	{
		// Check for relative internal links.
		if (preg_match('#^index2?\.php#', $url))
		{
			$url = JURI::base() . $url;
		}

		// Strip out any line breaks.
		$url = preg_split("/[\r\n]/", $url);
		$url = $url[0];

		// If we don't start with a http we need to fix this before we proceed.
		// We could validly start with something else (e.g. ftp), though this would
		// be unlikely and isn't supported by this API.
		if (!preg_match('#^http#i', $url))
		{
			$uri = JURI::getInstance();
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

		// If the headers have been sent, then we cannot send an additional location header
		// so we will output a javascript redirect statement.
		if (headers_sent())
		{
			echo "<script>document.location.href='$url';</script>\n";
		}
		else
		{
			jimport('joomla.environment.browser');
			$navigator = JBrowser::getInstance();
			jimport('phputf8.utils.ascii');
			if ($navigator->isBrowser('msie') && !utf8_is_ascii($url))
			{
				// MSIE type browser and/or server cause issues when url contains utf8 character,so use a javascript redirect method
				echo '<html><head><meta http-equiv="content-type" content="text/html; charset=' . $this->charSet .
					'" /><script>document.location.href=\'' . $url . '\';</script></head><body></body></html>';
			}
			elseif (!$moved and $navigator->isBrowser('konqueror'))
			{
				// WebKit browser (identified as konqueror by Joomla!) - Do not use 303, as it causes subresources
				// reload (https://bugs.webkit.org/show_bug.cgi?id=38690)
				echo '<html><head><meta http-equiv="refresh" content="0; url=' . $url .
					'" /><meta http-equiv="content-type" content="text/html; charset=' . $this->charSet . '" /></head><body></body></html>';
			}
			else
			{
				// All other browsers, use the more efficient HTTP header method
				header($moved ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 303 See other');
				header('Location: ' . $url);
				header('Content-Type: text/html; charset=' . $this->charSet);
			}
		}

		// Close the application after the redirect.
		$this->close();
	}

	/**
	 * Exit the application.
	 *
	 * @param   integer  $code  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function close($code = 0)
	{
		exit($code);
	}

	/**
	 * Load an object or array into the application configuration object.
	 *
	 * @param   mixed  $data  Either an array or object to be loaded into the configuration object.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function loadConfiguration($data)
	{
		// Load the data into the configuration object.
		if (is_array($data))
		{
			$this->config->loadArray($data);
		}
		elseif (is_object($data))
		{
			$this->config->loadObject($data);
		}
	}

	/**
	 * Registers a handler to a particular event group.
	 *
	 * @param   string    $event    The event name.
	 * @param   callback  $handler  The handler, a function or an instance of a event object.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	function registerEvent($event, $handler)
	{
		JDispatcher::getInstance()->register($event, $handler);
	}

	/**
	 * Calls all handlers associated with an event group.
	 *
	 * @param   string  $event  The event name.
	 * @param   array   $args   An array of arguments (optional).
	 *
	 * @return  array   An array of results from each function call.
	 *
	 * @since   12.1
	 */
	function triggerEvent($event, $args = null)
	{
		return JDispatcher::getInstance()->trigger($event, $args);
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $key      The name of the property.
	 * @param   mixed   $default  The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 *
	 * @since   12.1
	 */
	public function get($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $key    The name of the property.
	 * @param   mixed   $value  The value of the property to set (optional).
	 *
	 * @return  mixed   Previous value of the property
	 *
	 * @since   12.1
	 */
	public function set($key, $value = null)
	{
		$previous = $this->config->get($key);
		$this->config->set($key, $value);
		return $previous;
	}

	/**
	 * Set/get cachable state for the response.  If $allow is set, sets the cachable state of the
	 * response.  Always returns the current state.
	 *
	 * @param   boolean  $allow  True to allow browser caching.
	 *
	 * @return  boolean
	 *
	 * @since   12.1
	 */
	function allowCache($allow = null)
	{
		if ($allow !== null)
		{
			$this->response->cachable = (bool) $allow;
		}
		return $this->response->cachable;
	}

	/**
	 * Method to set a response header.  If the replace flag is set then all headers
	 * with the given name will be replaced by the new one.  The headers are stored
	 * in an internal array to be sent when the site is sent to the browser.
	 *
	 * @param   string   $name     The name of the header to set.
	 * @param   string   $value    The value of the header to set.
	 * @param   boolean  $replace  True to replace any headers with the same name.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	function setHeader($name, $value, $replace = false)
	{
		// Sanitize the input values.
		$name = (string) $name;
		$value = (string) $value;

		// If the replace flag is set, unset all known headers with the given name.
		if ($replace)
		{
			foreach ($this->response->headers as $key => $header)
			{
				if ($name == $header['name'])
				{
					unset($this->response->headers[$key]);
				}
			}
		}

		// Add the header to the internal array.
		$this->response->headers[] = array('name' => $name, 'value' => $value);
	}

	/**
	 * Method to get the array of response headers to be sent when the response is sent
	 * to the client.
	 *
	 * @return  array
	 *
	 * @since   12.1
	 */
	function getHeaders()
	{
		return $this->response->headers;
	}

	/**
	 * Method to clear any set response headers.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	function clearHeaders()
	{
		$this->response->headers = array();
	}

	/**
	 * Send the response headers.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	function sendHeaders()
	{
		if (!headers_sent())
		{
			foreach ($this->response->headers as $header)
			{
				if ('status' == strtolower($header['name']))
				{
					// 'status' headers indicate an HTTP status, and need to be handled slightly differently
					header(ucfirst(strtolower($header['name'])) . ': ' . $header['value'], null, (int) $header['value']);
				}
				else
				{
					header($header['name'] . ': ' . $header['value']);
				}
			}
		}
	}

	/**
	 * Set body content.  If body content already defined, this will replace it.
	 *
	 * @param   string  $content  The content to set as the response body.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	function setBody($content)
	{
		$this->response->body = array((string) $content);
	}

	/**
	 * Prepend content to the body content
	 *
	 * @param   string  $content  The content to prepend to the response body.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	function prependBody($content)
	{
		array_unshift($this->response->body, (string) $content);
	}

	/**
	 * Append content to the body content
	 *
	 * @param   string  $content  The content to append to the response body.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	function appendBody($content)
	{
		array_push($this->response->body, (string) $content);
	}

	/**
	 * Return the body content
	 *
	 * @param   boolean  $asArray  True to return the body as an array of strings.
	 *
	 * @return  mixed  The response body either as an array or concatenated string.
	 *
	 * @since   12.1
	 */
	public function getBody($asArray = false)
	{
		return $asArray ? $this->response->body : implode((array) $this->response->body);
	}

	/**
	 * Sends all headers prior to returning the response body string.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function __toString()
	{
		$data = $this->getBody();

		// Don't compress something if the server is going todo it anyway. Waste of time.
		if ($this->get('gzip') && !ini_get('zlib.output_compression') && ini_get('output_handler') != 'ob_gzhandler')
		{
			$data = $this->compress($data);
		}

		// Send the content-type header.
		$this->setHeader('Content-Type', $this->mimeType . '; charset=' . $this->charSet);

		// If the response is set to uncachable, we need to set some appropriate headers so browsers don't cache the response.
		if (!$this->response->cachable)
		{
			// Expires in the past.
			$this->setHeader('Expires', 'Mon, 1 Jan 2001 00:00:00 GMT', true);
			// Always modified.
			$this->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT', true);
			$this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', false);
			// HTTP 1.0
			$this->setHeader('Pragma', 'no-cache');
		}
		else
		{
			// Expires.
			$this->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + 900) . ' GMT');
			// Last modified.
			if ($this->modifiedDate instanceof JDate)
			{
				$this->setHeader('Last-Modified', $this->modifiedDate->format('D, d M Y H:i:s'));
			}
		}

		$this->sendHeaders();

		return $data;
	}

	/**
	 * Compress the data
	 *
	 * Checks the accept encoding of the browser and compresses the data before
	 * sending it to the client.
	 *
	 * @param   string  $data  The data to compress for output.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	protected function compress($data)
	{
		if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']))
		{
			return false;
		}

		$encoding = false;

		if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
		{
			$encoding = 'gzip';
		}

		if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip'))
		{
			$encoding = 'x-gzip';
		}

		if (!$encoding)
			return $data;

		if (!extension_loaded('zlib') || ini_get('zlib.output_compression'))
		{
			return $data;
		}

		if (headers_sent())
		{
			return $data;
		}

		if (connection_status() !== 0) {
			return $data;
		}

		// Ideal level.
		$level = 4;

		$gzdata = gzencode($data, $level);

		$this->setHeader('Content-Encoding', $encoding);
		$this->setHeader('X-Content-Encoded-By', 'Joomla');

		return $gzdata;
	}

	/**
	 * Method to detect the requested URI from server environment variables.
	 *
	 * @return  string  The requested URI
	 *
	 * @since   12.1
	 */
	protected function detectRequestURI()
	{
		// Initialise variables.
		$uri = '';

		// First we need to detect the URI scheme.
		if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off'))
		{
			$scheme = 'https://';
		}
		else
		{
			$scheme = 'http://';
		}

		/*
		 * There are some differences in the way that Apache and IIS populate server environment variables.  To
		 * properly detect the requested URI we need to adjust our algorithm based on whether or not we are getting
		 * information from Apache or IIS.
		 */

		// If PHP_SELF and REQUEST_URI are both populated then we will assume "Apache Mode".
		if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI']))
		{
			// The URI is built from the HTTP_HOST and REQUEST_URI environment variables in an Apache environment.
			$uri = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}
		// If not in "Apache Mode" we will assume that we are in an IIS environment and proceed.
		else
		{
			// IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable... thanks, MS
			$uri = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

			// If the QUERY_STRING variable exists append it to the URI string.
			if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']))
			{
				$uri .= '?' . $_SERVER['QUERY_STRING'];
			}
		}

		return trim($uri);
	}

	protected function detectClientPlatform($userAgent)
	{
		// Set the client platform default.
		$mobile = false;
		$platform = '';

		// Attempt to detect the client platform.
		if (stripos($userAgent, 'Windows') !== false)
		{
			$platform = 'windows';

			// Let's look at the specific mobile options in the windows space.
			if (stripos($userAgent, 'Windows Phone') !== false)
			{
				$mobile = true;
				$platform = 'windows_phone';
			}
			elseif (stripos($userAgent, 'Windows CE') !== false)
			{
				$mobile = true;
				$platform = 'windows_ce';
			}
		}
		// Interestingly 'iPhone' is present in all iOS devices so far including iPad and iPods.
		elseif (stripos($userAgent, 'iPhone') !== false)
		{
			$mobile = true;
			$platform = 'iphone';

			// Let's look at the specific mobile options in the windows space.
			if (stripos($userAgent, 'iPad') !== false)
			{
				$platform = 'ipad';
			}
			elseif (stripos($userAgent, 'iPod') !== false)
			{
				$platform = 'ipod';
			}
		}
		// This has to come after the iPhone check because mac strings are also present in iOS devices.
		elseif (preg_match('/macintosh|mac os x/i', $userAgent))
		{
			$platform = 'mac';
		}
		elseif (stripos($userAgent, 'Blackberry') !== false)
		{
			$mobile = true;
			$platform = 'blackberry';
		}
		elseif (stripos($userAgent, 'Android') !== false)
		{
			$mobile = true;
			$platform = 'android';
		}
		elseif (stripos($userAgent, 'Linux') !== false)
		{
			$platform = 'linux';
		}

		return array(
			'mobile' => $mobile,
			'platform' => $platform
		);
	}

	protected function detectClientEngine($userAgent)
	{
		$engine = '';

		// Attempt to detect the client engine -- starting with the most popular ... for now.
		if (stripos($userAgent, 'MSIE') !== false || stripos($userAgent, 'Trident') !== false)
		{
			$engine = 'trident';
		}
		// Evidently blackberry uses WebKit and doesn't necessarily report it.  Bad RIM.
		elseif (stripos($userAgent, 'AppleWebKit') !== false || stripos($userAgent, 'blackberry') !== false)
		{
			$engine = 'webkit';
		}
		// We have to check for like Gecko because some other browsers spoof Gecko.
		elseif (stripos($userAgent, 'Gecko') !== false && stripos($userAgent, 'like Gecko') === false)
		{
			$engine = 'gecko';
		}
		// Sometims Opera browsers don't say Presto.
		elseif (stripos($userAgent, 'Opera') !== false || stripos($userAgent, 'Presto') !== false)
		{
			$engine = 'presto';
		}
		// *sigh*
		elseif (stripos($userAgent, 'KHTML') !== false)
		{
			$engine = 'khtml';
		}
		// Lesser known engine but it finishes off the major list from Wikipedia :-)
		elseif (stripos($userAgent, 'Amaya') !== false)
		{
			$engine = 'amaya';
		}

		return $engine;
	}

	protected function detectClientBrowser($userAgent)
	{
		// Attempt to detect the browser type.  Obviously we are only worried about major browsers.
		$browser = '';
		$version = '';
		if ((stripos($userAgent, 'MSIE') !== false) && (stripos($userAgent, 'Opera') === false))
		{
			$browser = 'Internet_Explorer';
		}
		elseif ((stripos($userAgent, 'Firefox') !== false) && (stripos($userAgent, 'like Firefox') === false))
		{
			$browser = 'Firefox';
		}
		elseif (stripos($userAgent, 'Chrome') !== false)
		{
			$browser = 'Chrome';
		}
		elseif (stripos($userAgent, 'Safari') !== false)
		{
			$browser = 'Safari';
		}
		elseif (stripos($userAgent, 'Opera') !== false)
		{
			$browser = 'Opera';
		}

		// If we detected a known browser let's attempt to determine the version.
		if ($browser)
		{
			// Build the REGEX pattern to match the browser version string within the user agent string.
			$patternBrowser = ($browser == 'Internet_Explorer') ? 'MSIE' : $browser;
			$pattern = '#(?<browser>Version|'.$patternBrowser.')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

			// Attempt to find version strings in the user agent string.
			$matches = array();
			if (preg_match_all($pattern, $userAgent, $matches))
			{
				// Do we have both a Version and browser match?
				if (count($matches['browser']) > 1)
				{
					// See whether Version or browser came first, and use the number accordingly.
					if (strripos($userAgent, 'Version') < strripos($userAgent, $browser))
					{
						$version = $matches['version'][0];
					}
					else
					{
						$version = $matches['version'][1];
					}
				}
				// We only have a Version or a browser so use what we have.
				else
				{
					$version = $matches['version'][0];
				}
			}
		}

		return array(
			'browser' => $browser,
			'version' => $version
		);
	}

	/**
	 * Method to load a PHP configuration class file based on convention and return the instantiated data object.  You
	 * will extend this method in child classes to provide configuration data from whatever data source is relevant
	 * for your specific application.
	 *
	 * @return  mixed  Either an array or object to be loaded into the configuration object.
	 *
	 * @since   12.1
	 */
	protected function fetchConfigurationData()
	{
		// Instantiate variables.
		$config = array();

		// Handle the convention-based default case for configuration file.
		if (defined('JPATH_BASE'))
		{
			// Set the configuration file name and check to see if it exists.
			$file = JPATH_BASE . '/configuration.php';
			if (is_file($file))
			{
				// Import the configuration file.
				include_once $file;

				// Instantiate the configuration object if it exists.
				if (class_exists('JConfig'))
				{
					$config = new JConfig();
				}
			}
		}

		return $config;
	}

	protected function loadClientInformation($userAgent = null)
	{
		// Get the user agent from server environment.
		$userAgent = empty($userAgent) ? $_SERVER['HTTP_USER_AGENT'] : $userAgent;

		// Set the client user agent.
		$this->set('client.agent', $userAgent);

		// Attempt to detect the client platform.
		$data = $this->detectClientPlatform($userAgent);
		$this->set('client.mobile', $data['mobile']);
		$this->set('client.platform', $data['platform']);

		// Attempt to detect the client engine.
		$data = $this->detectClientEngine($userAgent);
		$this->set('client.engine', $data);

		// Attempt to detect the client browser.
		$data = $this->detectClientBrowser($userAgent);
		$this->set('client.browser', $data['browser']);
		$this->set('client.version', $data['version']);
	}

	/**
	 * Method to create a language for the Web application.  The logic and options for creating this
	 * object are adequately generic for default cases but for many applications it will make sense
	 * to override this method and create language objects based on more specific needs.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function loadLanguage()
	{
		$this->language = JFactory::getLanguage();
	}

	/**
	 * Method to create a session for the Web application.  The logic and options for creating this
	 * object are adequately generic for default cases but for many applications it will make sense
	 * to override this method and create session objects based on more specific needs.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function loadSession()
	{
		// Generate a session name.
		$name = md5($this->get('secret') . $this->get('session_name', get_class($this)));

		// Calculate the session lifetime.
		$lifetime = (($this->get('sess_lifetime')) ? $this->get('sess_lifetime') * 60 : 900);

		// Get the session handler from the configuration.
		$handler = $this->get('sess_handler', 'none');

		// Initialize the options for JSession.
		$options = array(
			'name' => $name,
			'expire' => $lifetime,
			'force_ssl' => $this->get('force_ssl')
		);

		// Instantiate the session object.
		$session = JSession::getInstance($handler, $options);
		if ($session->getState() == 'expired')
		{
			$session->restart();
		}

		// If the session is new, load the user and registry objects.
		if ($session->isNew())
		{
			$session->set('registry', new JRegistry());
			$session->set('user', new JUser());
		}

		// Set the session object.
		$this->session = $session;
	}

	/**
	 * Method to load the system URI strings for the application.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function loadSystemURIs()
	{
		// Set the request URI.
		$this->set('uri.request', $this->detectRequestURI());

		// Check to see if an explicit site URI has been set.
		$siteUri = trim($this->get('site_uri'));
		if ($siteUri != '')
		{
			// Parse the site URI and set the host and path segments of the URI.
			$uri = JUri::getInstance($siteUri);

			$host = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$path = rtrim($uri->toString(array('path')), '/\\');
		}

		/*
		 * No explicit site URL was set so we will do our best to determine the base URIs from
		 * the requested URI and the server environment variables.
		 */
		else
		{
			// Parse the request URI to determine the base.
			$uri = JUri::getInstance($this->get('uri.request'));

			$host = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));

			// Apache CGI
			if (strpos(php_sapi_name(), 'cgi') !== false && !empty($_SERVER['REQUEST_URI']))
			{
				$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			}
			// Others
			else
			{
				$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
			}
		}

		// Set the base URI both as just a path and as the full URI.
		$this->set('uri.base.full', $host . $path . '/');
		$this->set('uri.base.host', $host);
		$this->set('uri.base.path', $path);

		// Get an explicitly set media URI is present.
		$mediaURI = trim($this->get('media_uri'));
		if ($mediaURI)
		{
			if (strpos($mediaURI, '://') !== false)
			{
				$this->set('uri.media.full', $mediaURI);
				$this->set('uri.media.path', $mediaURI);
			}
			else
			{
				$this->set('uri.media.full', $this->get('uri.base.host') . $mediaURI);
				$this->set('uri.media.path', $mediaURI);
			}
		}
		// No explicit media URI was set, build it dynamically from the base uri.
		else
		{
			$this->set('uri.media.full', $this->get('uri.base.full') . 'media/');
			$this->set('uri.media.path', $this->get('uri.base.path') . 'media/');
		}
	}

	/**
	 * Method to setup the internal response object.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setupResponse()
	{
		// Setup the site response object.
		$this->response = new stdClass();
		$this->response->cachable = false;
		$this->response->headers = array();
		$this->response->body = array();
	}
}
