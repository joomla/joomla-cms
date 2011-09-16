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
 * @since       11.3
 */
class JWeb
{
	/**
	 * @var    JInput  The application input object.
	 * @since  11.3
	 */
	public $input;

	/**
	 * @var    string  Character encoding string.
	 * @since  11.3
	 */
	public $charSet = 'utf-8';

	/**
	 * @var    string  Response mime type.
	 * @since  11.3
	 */
	public $mimeType = 'text/html';

	/**
	 * @var    JDate  The body modified date for response headers.
	 * @since  11.3
	 */
	public $modifiedDate;

	/**
	 * @var    JRegistry  The application configuration object.
	 * @since  11.3
	 */
	protected $config;

	/**
	 * @var    JDispatcher  The application dispatcher object.
	 * @since  11.3
	 */
	protected $dispatcher;

	/**
	 * @var    JDocument  The application document object.
	 * @since  11.3
	 */
	protected $document;

	/**
	 * @var    JLanguage  The application language object.
	 * @since  11.3
	 */
	protected $language;

	/**
	 * @var    JSession  The application session object.
	 * @since  11.3
	 */
	protected $session;

	/**
	 * @var    object  The application response object.
	 * @since  11.3
	 */
	protected $response;

	/**
	 * @var    JWeb  The application instance.
	 * @since  11.3
	 */
	protected static $instance;

	/**
	 * Class constructor.
	 *
	 * @return  void
	 *
	 * @since   11.3
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
		$this->response = new stdClass();
		$this->response->cachable = false;
		$this->response->headers = array();
		$this->response->body = array();

		// Set the system URIs.
		$this->loadSystemURIs();

		// Set the client information.
		$this->loadClientInformation();
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
	 * @since   11.3
	 */
	public static function & getInstance($name = null)
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
	 * @param   mixed  $session     An optional argument to provide dependency injection for the application's
	 *                              session object.  If the argument is a JSession object that object will become
	 *                              the application's session object, if it is false then there will be no session
	 *                              object, and if it is null then the default session object will be created based
	 *                              on the application's loadSession() method.
	 * @param   mixed  $document    An optional argument to provide dependency injection for the application's
	 *                              document object.  If the argument is a JDocument object that object will become
	 *                              the application's document object, if it is false then there will be no document
	 *                              object, and if it is null then the default document object will be created based
	 *                              on the application's loadDocument() method.
	 * @param   mixed  $language    An optional argument to provide dependency injection for the application's
	 *                              language object.  If the argument is a JLanguage object that object will become
	 *                              the application's language object, if it is false then there will be no language
	 *                              object, and if it is null then the default language object will be created based
	 *                              on the application's loadLanguage() method.
	 * @param   mixed  $dispatcher  An optional argument to provide dependency injection for the application's
	 *                              event dispatcher.  If the argument is a JDispatcher object that object will become
	 *                              the application's event dispatcher, if it is null then the default event dispatcher
	 *                              will be created based on the application's loadDispatcher() method.
	 *
	 * @return  JWeb  Instance of $this to allow chaining.
	 *
	 * @see     loadSession()
	 * @see     loadDocument()
	 * @see     loadLanguage()
	 * @see     loadDispatcher()
	 * @since   11.3
	 */
	public function initialise($session = null, $document = null, $language = null, $dispatcher = null)
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

		// If a document object is given use it.
		if ($document instanceof JDocument)
		{
			$this->document = $document;
		}
		// We don't have a document, nor do we want one.
		elseif ($document === false)
		{
			// Do nothing.
		}
		// Create the document based on the application logic.
		else
		{
			$this->loadDocument();
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

		// If a dispatcher object is given use it.
		if ($dispatcher instanceof JDispatcher)
		{
			$this->dispatcher = $dispatcher;
		}
		// Create the dispatcher based on the application logic.
		else
		{
			$this->loadDispatcher();
		}

		// Trigger the onAfterInitialise event.
		$this->triggerEvent('onAfterInitialise');

		return $this;
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function execute()
	{
		// Perform application routines.
		$this->doExecute();

		// Trigger the onAfterExecute event.
		$this->triggerEvent('onAfterExecute');

		// If we have an application document object, render it.
		if ($this->document instanceof JDocument)
		{
			// Trigger the onBeforeRender event.
			$this->triggerEvent('onBeforeRender');

			// Render the application output.
			$this->render();

			// Trigger the onAfterRender event.
			$this->triggerEvent('onAfterRender');
		}

		// If gzip compression is enabled in configuration and the server is compliant; compress the output.
		if ($this->get('gzip') && !ini_get('zlib.output_compression') && (ini_get('output_handler') != 'ob_gzhandler'))
		{
			$this->compress();
		}

		// Trigger the onBeforeRender event.
		$this->triggerEvent('onBeforeRespond');

		// Send the application response.
		$this->respond();

		// Trigger the onBeforeRender event.
		$this->triggerEvent('onAfterRespond');
	}

	/**
	 * Method to run the Web application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of action that populates a JDocument object so that output
	 * can be rendered to the client.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function doExecute()
	{
		// Your application routines go here.
	}

	/**
	 * Rendering is the process of pushing the document buffers into the template
	 * placeholders, retrieving data from the document and pushing it into
	 * the application response buffer.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function render()
	{
		// Setup the document options.
		$options = array(
			'template' => $this->get('theme'),
			'file' => 'index.php',
			'params' => ''
		);

		// Handle the convention-based default case for themes path.
		if (defined('JPATH_BASE'))
		{
			$options['directory'] = JPATH_BASE . '/themes';
		}
		else
		{
			$options['directory'] = dirname(__FILE__) . '/themes';
		}

		// Parse the document.
		$this->document->parse($options);

		// Render the document.
		$data = $this->document->render($this->get('cache_enabled'), $options);

		// Set the application output data.
		$this->setBody($data);
	}

	/**
	 * Checks the accept encoding of the browser and compresses the data before
	 * sending it to the client if possible.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function compress()
	{
		// Get the output data.
		$data = $this->getBody();

		// Detect the client supported encoding.
		$encoding = $this->detectClientAcceptedEncoding();

		// If no supported encoding is detected just return the data.
		if (!$encoding)
		{
			return;
		}

		// Verify that the server supports gzip compression before we attempt to gzip encode the data.
		if (!extension_loaded('zlib') || ini_get('zlib.output_compression'))
		{
			return;
		}

		// Verify that headers have not yet been sent, and that our connection is still alive.
		if (headers_sent() || (connection_status() !== 0))
		{
			return;
		}

		// Ideal level.
		$level = 4;

		// Attemp to gzip encode the data.
		$gzdata = gzencode($data, $level);

		// If there was a problem encoding the data just return the unencoded data.
		if ($gzdata === false)
		{
			return;
		}

		// Set the encoding headers.
		$this->setHeader('Content-Encoding', $encoding);
		$this->setHeader('X-Content-Encoded-By', 'Joomla');

		// Replace the output with the encoded data.
		$this->setBody($gzdata);
	}

	/**
	 * Method to send the application response to the client.  All headers will be sent prior to the main
	 * application output data.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function respond()
	{
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

		echo $this->getBody();
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
	 * @since   11.3
	 */
	public function redirect($url, $moved = false)
	{
		// Import library dependencies.
		jimport('phputf8.utils.ascii');

		// Check for relative internal links.
		if (preg_match('#^index\.php#', $url))
		{
			$url = $this->get('uri.base.full') . $url;
		}

		// Perform a basic sanity check to make sure we don't have any CRLF garbage.
		$url = preg_split("/[\r\n]/", $url);
		$url = $url[0];

		/*
		 * Here we need to check and see if the URL is relative or absolute.  Essentially, do we need to
		 * prepend the URL with our base URL for a proper redirect.  The rudimentary way we are looking
		 * at this is to simply check whether or not the URL string has a valid scheme or not.
		 */
		if (!preg_match('#^[a-z]+\://#i', $url))
		{
			// Get a JURI instance for the requested URI.
			$uri = JURI::getInstance($this->get('uri.request'));

			// Get a base URL to prepend from the requested URI.
			$prefix = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));

			// We just need the prefix since we have a path relative to the root.
			if ($url[0] == '/')
			{
				$url = $prefix . $url;
			}
			// It's relative to where we are now, so lets add that.
			else
			{
				$parts = explode('/', $uri->toString(array('path')));
				array_pop($parts);
				$path = implode('/', $parts) . '/';
				$url = $prefix . $path . $url;
			}
		}

		// If the headers have already been sent we need to send the redirect statement via JavaScript.
		if (headers_sent())
		{
			echo "<script>document.location.href='$url';</script>\n";
		}
		else
		{
			// We have to use a JavaScript redirect here because MSIE doesn't play nice with utf-8 URLs.
			if (($this->get('client.engine') == 'trident') && !utf8_is_ascii($url))
			{
				$html = '<html><head>';
				$html .= '<meta http-equiv="content-type" content="text/html; charset=' . $this->charSet . '" />';
				$html .= '<script>document.location.href=\'' . $url . '\';</script>';
				$html .= '</head><body></body></html>';

				echo $html;
			}
			/*
			 * For WebKit based browsers do not send a 303, as it causes subresource reloading.  You can view the
			 * bug report at: https://bugs.webkit.org/show_bug.cgi?id=38690
			 */
			elseif (!$moved and ($this->get('client.engine') == 'webkit'))
			{
				$html = '<html><head>';
				$html .= '<meta http-equiv="refresh" content="0; url=' . $url . '" />';
				$html .= '<meta http-equiv="content-type" content="text/html; charset=' . $this->charSet . '" />';
				$html .= '</head><body></body></html>';

				echo $html;
			}
			else
			{
				// All other cases use the more efficient HTTP header for redirection.
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
	 * @since   11.3
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
	 * @return  JWeb  Instance of $this to allow chaining.
	 *
	 * @since   11.3
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

		return $this;
	}

	/**
	 * Registers a handler to a particular event group.
	 *
	 * @param   string    $event    The event name.
	 * @param   callback  $handler  The handler, a function or an instance of a event object.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	function registerEvent($event, $handler)
	{
		$this->dispatcher->register($event, $handler);
	}

	/**
	 * Calls all handlers associated with an event group.
	 *
	 * @param   string  $event  The event name.
	 * @param   array   $args   An array of arguments (optional).
	 *
	 * @return  array   An array of results from each function call.
	 *
	 * @since   11.3
	 */
	function triggerEvent($event, $args = null)
	{
		return $this->dispatcher->trigger($event, $args);
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $key      The name of the property.
	 * @param   mixed   $default  The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 *
	 * @since   11.3
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
	 * @since   11.3
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
	 * @since   11.3
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
	 * @return  JWeb  Instance of $this to allow chaining.
	 *
	 * @since   11.3
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

		return $this;
	}

	/**
	 * Method to get the array of response headers to be sent when the response is sent
	 * to the client.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	function getHeaders()
	{
		return $this->response->headers;
	}

	/**
	 * Method to clear any set response headers.
	 *
	 * @return  JWeb  Instance of $this to allow chaining.
	 *
	 * @since   11.3
	 */
	function clearHeaders()
	{
		$this->response->headers = array();

		return $this;
	}

	/**
	 * Send the response headers.
	 *
	 * @return  JWeb  Instance of $this to allow chaining.
	 *
	 * @since   11.3
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

		return $this;
	}

	/**
	 * Set body content.  If body content already defined, this will replace it.
	 *
	 * @param   string  $content  The content to set as the response body.
	 *
	 * @return  JWeb  Instance of $this to allow chaining.
	 *
	 * @since   11.3
	 */
	function setBody($content)
	{
		$this->response->body = array((string) $content);

		return $this;
	}

	/**
	 * Prepend content to the body content
	 *
	 * @param   string  $content  The content to prepend to the response body.
	 *
	 * @return  JWeb  Instance of $this to allow chaining.
	 *
	 * @since   11.3
	 */
	function prependBody($content)
	{
		array_unshift($this->response->body, (string) $content);

		return $this;
	}

	/**
	 * Append content to the body content
	 *
	 * @param   string  $content  The content to append to the response body.
	 *
	 * @return  JWeb  Instance of $this to allow chaining.
	 *
	 * @since   11.3
	 */
	function appendBody($content)
	{
		array_push($this->response->body, (string) $content);

		return $this;
	}

	/**
	 * Return the body content
	 *
	 * @param   boolean  $asArray  True to return the body as an array of strings.
	 *
	 * @return  mixed  The response body either as an array or concatenated string.
	 *
	 * @since   11.3
	 */
	public function getBody($asArray = false)
	{
		return $asArray ? $this->response->body : implode((array) $this->response->body);
	}

	/**
	 * Method to detect the accepted response encoding by the client.  We only support gzip encoding
	 * at this time so that is the only type of response encoding that can be returned besides false
	 * if no supported encoding can be accepted by the client.
	 *
	 * @return  mixed  Boolean false if no supported encoding scheme is accepted by the client.  Encoding
	 *                 string if a supported encoding is accepted.
	 *
	 * @since   11.3
	 */
	protected function detectClientAcceptedEncoding()
	{
		// Initialise variables.
		$encoding = false;

		// If there is no accepted encoding do nothing.
		if (!empty($_SERVER['HTTP_ACCEPT_ENCODING']))
		{
			if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false)
			{
				$encoding = 'x-gzip';
			}
			elseif (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)
			{
				$encoding = 'gzip';
			}
		}

		return $encoding;
	}

	/**
	 * Method to detect the requested URI from server environment variables.
	 *
	 * @return  string  The requested URI
	 *
	 * @since   11.3
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
	 * @since   11.3
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
	 * Method to create an event dispatcher for the Web application.  The logic and options for creating
	 * this object are adequately generic for default cases but for many applications it will make sense
	 * to override this method and create event dispatchers based on more specific needs.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function loadDispatcher()
	{
		$this->dispatcher = JDispatcher::getInstance();
	}

	/**
	 * Method to create a document for the Web application.  The logic and options for creating this
	 * object are adequately generic for default cases but for many applications it will make sense
	 * to override this method and create document objects based on more specific needs.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function loadDocument()
	{
		$this->document = JFactory::getDocument();
	}

	/**
	 * Method to create a language for the Web application.  The logic and options for creating this
	 * object are adequately generic for default cases but for many applications it will make sense
	 * to override this method and create language objects based on more specific needs.
	 *
	 * @return  void
	 *
	 * @since   11.3
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
	 * @since   11.3
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
	 * @since   11.3
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
}
