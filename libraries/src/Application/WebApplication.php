<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Input\Input;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Base class for a Joomla! Web application.
 *
 * @since  2.5.0
 * @note   As of 4.0 this class will be abstract
 */
class WebApplication extends BaseApplication
{
	/**
	 * @var    string  Character encoding string.
	 * @since  1.7.3
	 */
	public $charSet = 'utf-8';

	/**
	 * @var    string  Response mime type.
	 * @since  1.7.3
	 */
	public $mimeType = 'text/html';

	/**
	 * @var    \JDate  The body modified date for response headers.
	 * @since  1.7.3
	 */
	public $modifiedDate;

	/**
	 * @var    \JApplicationWebClient  The application client object.
	 * @since  1.7.3
	 */
	public $client;

	/**
	 * @var    \JDocument  The application document object.
	 * @since  1.7.3
	 */
	protected $document;

	/**
	 * @var    \JLanguage  The application language object.
	 * @since  1.7.3
	 */
	protected $language;

	/**
	 * @var    \JSession  The application session object.
	 * @since  1.7.3
	 */
	protected $session;

	/**
	 * @var    object  The application response object.
	 * @since  1.7.3
	 */
	protected $response;

	/**
	 * @var    WebApplication  The application instance.
	 * @since  1.7.3
	 */
	protected static $instance;

	/**
	 * A map of integer HTTP 1.1 response codes to the full HTTP Status for the headers.
	 *
	 * @var    object
	 * @since  3.4
	 * @link   http://tools.ietf.org/pdf/rfc7231.pdf
	 */
	private $responseMap = array(
		100 => 'HTTP/1.1 100 Continue',
		101 => 'HTTP/1.1 101 Switching Protocols',
		102 => 'HTTP/1.1 102 Processing',
		200 => 'HTTP/1.1 200 OK',
		201 => 'HTTP/1.1 201 Created',
		202 => 'HTTP/1.1 202 Accepted',
		203 => 'HTTP/1.1 203 Non-Authoritative Information',
		204 => 'HTTP/1.1 204 No Content',
		205 => 'HTTP/1.1 205 Reset Content',
		206 => 'HTTP/1.1 206 Partial Content',
		207 => 'HTTP/1.1 207 Multi-Status',
		208 => 'HTTP/1.1 208 Already Reported',
		226 => 'HTTP/1.1 226 IM Used',
		300 => 'HTTP/1.1 300 Multiple Choices',
		301 => 'HTTP/1.1 301 Moved Permanently',
		302 => 'HTTP/1.1 302 Found',
		303 => 'HTTP/1.1 303 See other',
		304 => 'HTTP/1.1 304 Not Modified',
		305 => 'HTTP/1.1 305 Use Proxy',
		306 => 'HTTP/1.1 306 (Unused)',
		307 => 'HTTP/1.1 307 Temporary Redirect',
		308 => 'HTTP/1.1 308 Permanent Redirect',
		400 => 'HTTP/1.1 400 Bad Request',
		401 => 'HTTP/1.1 401 Unauthorized',
		402 => 'HTTP/1.1 402 Payment Required',
		403 => 'HTTP/1.1 403 Forbidden',
		404 => 'HTTP/1.1 404 Not Found',
		405 => 'HTTP/1.1 405 Method Not Allowed',
		406 => 'HTTP/1.1 406 Not Acceptable',
		407 => 'HTTP/1.1 407 Proxy Authentication Required',
		408 => 'HTTP/1.1 408 Request Timeout',
		409 => 'HTTP/1.1 409 Conflict',
		410 => 'HTTP/1.1 410 Gone',
		411 => 'HTTP/1.1 411 Length Required',
		412 => 'HTTP/1.1 412 Precondition Failed',
		413 => 'HTTP/1.1 413 Payload Too Large',
		414 => 'HTTP/1.1 414 URI Too Long',
		415 => 'HTTP/1.1 415 Unsupported Media Type',
		416 => 'HTTP/1.1 416 Range Not Satisfiable',
		417 => 'HTTP/1.1 417 Expectation Failed',
		418 => 'HTTP/1.1 418 I\'m a teapot',
		421 => 'HTTP/1.1 421 Misdirected Request',
		422 => 'HTTP/1.1 422 Unprocessable Entity',
		423 => 'HTTP/1.1 423 Locked',
		424 => 'HTTP/1.1 424 Failed Dependency',
		426 => 'HTTP/1.1 426 Upgrade Required',
		428 => 'HTTP/1.1 428 Precondition Required',
		429 => 'HTTP/1.1 429 Too Many Requests',
		431 => 'HTTP/1.1 431 Request Header Fields Too Large',
		451 => 'HTTP/1.1 451 Unavailable For Legal Reasons',
		500 => 'HTTP/1.1 500 Internal Server Error',
		501 => 'HTTP/1.1 501 Not Implemented',
		502 => 'HTTP/1.1 502 Bad Gateway',
		503 => 'HTTP/1.1 503 Service Unavailable',
		504 => 'HTTP/1.1 504 Gateway Timeout',
		505 => 'HTTP/1.1 505 HTTP Version Not Supported',
		506 => 'HTTP/1.1 506 Variant Also Negotiates',
		507 => 'HTTP/1.1 507 Insufficient Storage',
		508 => 'HTTP/1.1 508 Loop Detected',
		510 => 'HTTP/1.1 510 Not Extended',
		511 => 'HTTP/1.1 511 Network Authentication Required',
	);

	/**
	 * A map of HTTP Response headers which may only send a single value, all others
	 * are considered to allow multiple
	 *
	 * @var    object
	 * @since  3.5.2
	 * @link   https://tools.ietf.org/html/rfc7230
	 */
	private $singleValueResponseHeaders = array(
		'status', // This is not a valid header name, but the representation used by Joomla to identify the HTTP Response Code
		'content-length',
		'host',
		'content-type',
		'content-location',
		'date',
		'location',
		'retry-after',
		'server',
		'mime-version',
		'last-modified',
		'etag',
		'accept-ranges',
		'content-range',
		'age',
		'expires',
		'clear-site-data',
		'pragma',
		'strict-transport-security',
		'content-security-policy',
		'content-security-policy-report-only',
		'x-frame-options',
		'x-xss-protection',
		'x-content-type-options',
		'referrer-policy',
		'expect-ct',
		'feature-policy', // @deprecated - see: https://scotthelme.co.uk/goodbye-feature-policy-and-hello-permissions-policy/
		'permissions-policy',
	);

	/**
	 * Class constructor.
	 *
	 * @param   Input                   $input   An optional argument to provide dependency injection for the application's
	 *                                           input object.  If the argument is a \JInput object that object will become
	 *                                           the application's input object, otherwise a default input object is created.
	 * @param   Registry                $config  An optional argument to provide dependency injection for the application's
	 *                                           config object.  If the argument is a Registry object that object will become
	 *                                           the application's config object, otherwise a default config object is created.
	 * @param   \JApplicationWebClient  $client  An optional argument to provide dependency injection for the application's
	 *                                           client object.  If the argument is a \JApplicationWebClient object that object will become
	 *                                           the application's client object, otherwise a default client object is created.
	 *
	 * @since   1.7.3
	 */
	public function __construct(Input $input = null, Registry $config = null, \JApplicationWebClient $client = null)
	{
		// If an input object is given use it.
		if ($input instanceof Input)
		{
			$this->input = $input;
		}
		// Create the input based on the application logic.
		else
		{
			$this->input = new Input;
		}

		// If a config object is given use it.
		if ($config instanceof Registry)
		{
			$this->config = $config;
		}
		// Instantiate a new configuration object.
		else
		{
			$this->config = new Registry;
		}

		// If a client object is given use it.
		if ($client instanceof \JApplicationWebClient)
		{
			$this->client = $client;
		}
		// Instantiate a new web client object.
		else
		{
			$this->client = new \JApplicationWebClient;
		}

		// Load the configuration object.
		$this->loadConfiguration($this->fetchConfigurationData());

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());

		// Setup the response object.
		$this->response = new \stdClass;
		$this->response->cachable = false;
		$this->response->headers = array();
		$this->response->body = array();

		// Set the system URIs.
		$this->loadSystemUris();
	}

	/**
	 * Returns a reference to the global WebApplication object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as: $web = WebApplication::getInstance();
	 *
	 * @param   string  $name  The name (optional) of the JApplicationWeb class to instantiate.
	 *
	 * @return  WebApplication
	 *
	 * @since   1.7.3
	 */
	public static function getInstance($name = null)
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
			if (class_exists($name) && (is_subclass_of($name, '\\Joomla\\CMS\\Application\\WebApplication')))
			{
				self::$instance = new $name;
			}
			else
			{
				self::$instance = new WebApplication;
			}
		}

		return self::$instance;
	}

	/**
	 * Initialise the application.
	 *
	 * @param   mixed  $session     An optional argument to provide dependency injection for the application's
	 *                              session object.  If the argument is a \JSession object that object will become
	 *                              the application's session object, if it is false then there will be no session
	 *                              object, and if it is null then the default session object will be created based
	 *                              on the application's loadSession() method.
	 * @param   mixed  $document    An optional argument to provide dependency injection for the application's
	 *                              document object.  If the argument is a \JDocument object that object will become
	 *                              the application's document object, if it is false then there will be no document
	 *                              object, and if it is null then the default document object will be created based
	 *                              on the application's loadDocument() method.
	 * @param   mixed  $language    An optional argument to provide dependency injection for the application's
	 *                              language object.  If the argument is a \JLanguage object that object will become
	 *                              the application's language object, if it is false then there will be no language
	 *                              object, and if it is null then the default language object will be created based
	 *                              on the application's loadLanguage() method.
	 * @param   mixed  $dispatcher  An optional argument to provide dependency injection for the application's
	 *                              event dispatcher.  If the argument is a \JEventDispatcher object that object will become
	 *                              the application's event dispatcher, if it is null then the default event dispatcher
	 *                              will be created based on the application's loadDispatcher() method.
	 *
	 * @return  WebApplication  Instance of $this to allow chaining.
	 *
	 * @deprecated  4.0
	 * @see     WebApplication::loadSession()
	 * @see     WebApplication::loadDocument()
	 * @see     WebApplication::loadLanguage()
	 * @see     WebApplication::loadDispatcher()
	 * @since   1.7.3
	 */
	public function initialise($session = null, $document = null, $language = null, $dispatcher = null)
	{
		// Create the session based on the application logic.
		if ($session !== false)
		{
			$this->loadSession($session);
		}

		// Create the document based on the application logic.
		if ($document !== false)
		{
			$this->loadDocument($document);
		}

		// Create the language based on the application logic.
		if ($language !== false)
		{
			$this->loadLanguage($language);
		}

		$this->loadDispatcher($dispatcher);

		return $this;
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   1.7.3
	 */
	public function execute()
	{
		// Trigger the onBeforeExecute event.
		$this->triggerEvent('onBeforeExecute');

		// Perform application routines.
		$this->doExecute();

		// Trigger the onAfterExecute event.
		$this->triggerEvent('onAfterExecute');

		// If we have an application document object, render it.
		if ($this->document instanceof \JDocument)
		{
			// Trigger the onBeforeRender event.
			$this->triggerEvent('onBeforeRender');

			// Render the application output.
			$this->render();

			// Trigger the onAfterRender event.
			$this->triggerEvent('onAfterRender');
		}

		// If gzip compression is enabled in configuration and the server is compliant, compress the output.
		if ($this->get('gzip') && !ini_get('zlib.output_compression') && (ini_get('output_handler') != 'ob_gzhandler'))
		{
			$this->compress();
		}

		// Trigger the onBeforeRespond event.
		$this->triggerEvent('onBeforeRespond');

		// Send the application response.
		$this->respond();

		// Trigger the onAfterRespond event.
		$this->triggerEvent('onAfterRespond');
	}

	/**
	 * Rendering is the process of pushing the document buffers into the template
	 * placeholders, retrieving data from the document and pushing it into
	 * the application response buffer.
	 *
	 * @return  void
	 *
	 * @since   1.7.3
	 */
	protected function render()
	{
		// Setup the document options.
		$options = array(
			'template' => $this->get('theme'),
			'file' => $this->get('themeFile', 'index.php'),
			'params' => $this->get('themeParams'),
		);

		if ($this->get('themes.base'))
		{
			$options['directory'] = $this->get('themes.base');
		}
		// Fall back to constants.
		else
		{
			$options['directory'] = defined('JPATH_THEMES') ? JPATH_THEMES : (defined('JPATH_BASE') ? JPATH_BASE : __DIR__) . '/themes';
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
	 * @since   1.7.3
	 */
	protected function compress()
	{
		// Supported compression encodings.
		$supported = array(
			'x-gzip' => 'gz',
			'gzip' => 'gz',
			'deflate' => 'deflate',
		);

		// Get the supported encoding.
		$encodings = array_intersect($this->client->encodings, array_keys($supported));

		// If no supported encoding is detected do nothing and return.
		if (empty($encodings))
		{
			return;
		}

		// Verify that headers have not yet been sent, and that our connection is still alive.
		if ($this->checkHeadersSent() || !$this->checkConnectionAlive())
		{
			return;
		}

		// Iterate through the encodings and attempt to compress the data using any found supported encodings.
		foreach ($encodings as $encoding)
		{
			if (($supported[$encoding] == 'gz') || ($supported[$encoding] == 'deflate'))
			{
				// Verify that the server supports gzip compression before we attempt to gzip encode the data.
				if (!extension_loaded('zlib') || ini_get('zlib.output_compression'))
				{
					continue;
				}

				// Attempt to gzip encode the data with an optimal level 4.
				$data = $this->getBody();
				$gzdata = gzencode($data, 4, ($supported[$encoding] == 'gz') ? FORCE_GZIP : FORCE_DEFLATE);

				// If there was a problem encoding the data just try the next encoding scheme.
				if ($gzdata === false)
				{
					continue;
				}

				// Set the encoding headers.
				$this->setHeader('Content-Encoding', $encoding);
				$this->setHeader('Vary', 'Accept-Encoding');

				// Header will be removed at 4.0
				if ($this->get('MetaVersion'))
				{
					$this->setHeader('X-Content-Encoded-By', 'Joomla');
				}

				// Replace the output with the encoded data.
				$this->setBody($gzdata);

				// Compression complete, let's break out of the loop.
				break;
			}
		}
	}

	/**
	 * Method to send the application response to the client.  All headers will be sent prior to the main
	 * application output data.
	 *
	 * @return  void
	 *
	 * @since   1.7.3
	 */
	protected function respond()
	{
		// Send the content-type header.
		$this->setHeader('Content-Type', $this->mimeType . '; charset=' . $this->charSet);

		// If the response is set to uncachable, we need to set some appropriate headers so browsers don't cache the response.
		if (!$this->response->cachable)
		{
			// Expires in the past.
			$this->setHeader('Expires', 'Wed, 17 Aug 2005 00:00:00 GMT', true);

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
			if ($this->modifiedDate instanceof \JDate)
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
	 * @param   string   $url     The URL to redirect to. Can only be http/https URL.
	 * @param   integer  $status  The HTTP 1.1 status code to be provided. 303 is assumed by default.
	 *
	 * @return  void
	 *
	 * @since   1.7.3
	 */
	public function redirect($url, $status = 303)
	{
		// Check for relative internal links.
		if (preg_match('#^index\.php#', $url))
		{
			// We changed this from "$this->get('uri.base.full') . $url" due to the inability to run the system tests with the original code
			$url = \JUri::base() . $url;
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
			// Get a \JUri instance for the requested URI.
			$uri = \JUri::getInstance($this->get('uri.request'));

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
		if ($this->checkHeadersSent())
		{
			echo "<script>document.location.href=" . json_encode(str_replace("'", '&apos;', $url)) . ";</script>\n";
		}
		else
		{
			// We have to use a JavaScript redirect here because MSIE doesn't play nice with utf-8 URLs.
			if (($this->client->engine == \JApplicationWebClient::TRIDENT) && !StringHelper::is_ascii($url))
			{
				$html = '<html><head>';
				$html .= '<meta http-equiv="content-type" content="text/html; charset=' . $this->charSet . '" />';
				$html .= '<script>document.location.href=' . json_encode(str_replace("'", '&apos;', $url)) . ';</script>';
				$html .= '</head><body></body></html>';

				echo $html;
			}
			else
			{
				// Check if we have a boolean for the status variable for compatibility with old $move parameter
				// @deprecated 4.0
				if (is_bool($status))
				{
					$status = $status ? 301 : 303;
				}

				// Now check if we have an integer status code that maps to a valid redirect. If we don't then set a 303
				// @deprecated 4.0 From 4.0 if no valid status code is given an InvalidArgumentException will be thrown
				if (!is_int($status) || !$this->isRedirectState($status))
				{
					$status = 303;
				}

				// All other cases use the more efficient HTTP header for redirection.
				$this->setHeader('Status', $status, true);
				$this->setHeader('Location', $url, true);
			}
		}

		// Trigger the onBeforeRespond event.
		$this->triggerEvent('onBeforeRespond');

		// Set appropriate headers
		$this->respond();

		// Trigger the onAfterRespond event.
		$this->triggerEvent('onAfterRespond');

		//  Close the application after the redirect.
		$this->close();
	}

	/**
	 * Checks if a state is a redirect state
	 *
	 * @param   integer  $state  The HTTP 1.1 status code.
	 *
	 * @return  boolean
	 *
	 * @since   3.8.0
	 */
	protected function isRedirectState($state)
	{
		$state = (int) $state;

		return ($state > 299 && $state < 400);
	}

	/**
	 * Load an object or array into the application configuration object.
	 *
	 * @param   mixed  $data  Either an array or object to be loaded into the configuration object.
	 *
	 * @return  WebApplication  Instance of $this to allow chaining.
	 *
	 * @since   1.7.3
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
	 * Set/get cachable state for the response.  If $allow is set, sets the cachable state of the
	 * response.  Always returns the current state.
	 *
	 * @param   boolean  $allow  True to allow browser caching.
	 *
	 * @return  boolean
	 *
	 * @since   1.7.3
	 */
	public function allowCache($allow = null)
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
	 * @return  WebApplication  Instance of $this to allow chaining.
	 *
	 * @since   1.7.3
	 */
	public function setHeader($name, $value, $replace = false)
	{
		// Sanitize the input values.
		$name = (string) $name;
		$value = (string) $value;

		// Create an array of duplicate header names
		$keys = false;

		if ($this->response->headers)
		{
			$names = array();

			foreach ($this->response->headers as $key => $header)
			{
				$names[$key] = $header['name'];
			}

			// Find existing headers by name
			$keys = array_keys($names, $name);
		}

		// Remove if $replace is true and there are duplicate names
		if ($replace && $keys)
		{
			$this->response->headers = array_diff_key($this->response->headers, array_flip($keys));
		}

		/*
		 * If no keys found, safe to insert (!$keys)
		 * If ($keys && $replace) it's a replacement and previous have been deleted
		 * If ($keys && !in_array...) it's a multiple value header
		 */
		$single = in_array(strtolower($name), $this->singleValueResponseHeaders);

		if ($value && (!$keys || ($keys && ($replace || !$single))))
		{
			// Add the header to the internal array.
			$this->response->headers[] = array('name' => $name, 'value' => $value);
		}

		return $this;
	}

	/**
	 * Method to get the array of response headers to be sent when the response is sent
	 * to the client.
	 *
	 * @return  array	 *
	 *
	 * @since   1.7.3
	 */
	public function getHeaders()
	{
		return $this->response->headers;
	}

	/**
	 * Method to clear any set response headers.
	 *
	 * @return  WebApplication  Instance of $this to allow chaining.
	 *
	 * @since   1.7.3
	 */
	public function clearHeaders()
	{
		$this->response->headers = array();

		return $this;
	}

	/**
	 * Send the response headers.
	 *
	 * @return  WebApplication  Instance of $this to allow chaining.
	 *
	 * @since   1.7.3
	 */
	public function sendHeaders()
	{
		if (!$this->checkHeadersSent())
		{
			// Creating an array of headers, making arrays of headers with multiple values
			$val = array();

			foreach ($this->response->headers as $header)
			{
				if ('status' == strtolower($header['name']))
				{
					// 'status' headers indicate an HTTP status, and need to be handled slightly differently
					$status = $this->getHttpStatusValue($header['value']);
					$this->header($status, true, (int) $header['value']);
				}
				else
				{
					$val[$header['name']] = !isset($val[$header['name']]) ? $header['value'] : implode(', ', array($val[$header['name']], $header['value']));
					$this->header($header['name'] . ': ' . $val[$header['name']], true);
				}
			}
		}

		return $this;
	}

	/**
	 * Check if a given value can be successfully mapped to a valid http status value
	 *
	 * @param   string  $value  The given status as int or string
	 *
	 * @return  string
	 *
	 * @since   3.8.0
	 */
	protected function getHttpStatusValue($value)
	{
		$code = (int) $value;

		if (array_key_exists($code, $this->responseMap))
		{
			return $this->responseMap[$code];
		}

		return 'HTTP/1.1 ' . $code;
	}

	/**
	 * Set body content.  If body content already defined, this will replace it.
	 *
	 * @param   string  $content  The content to set as the response body.
	 *
	 * @return  WebApplication  Instance of $this to allow chaining.
	 *
	 * @since   1.7.3
	 */
	public function setBody($content)
	{
		$this->response->body = array((string) $content);

		return $this;
	}

	/**
	 * Prepend content to the body content
	 *
	 * @param   string  $content  The content to prepend to the response body.
	 *
	 * @return  WebApplication  Instance of $this to allow chaining.
	 *
	 * @since   1.7.3
	 */
	public function prependBody($content)
	{
		array_unshift($this->response->body, (string) $content);

		return $this;
	}

	/**
	 * Append content to the body content
	 *
	 * @param   string  $content  The content to append to the response body.
	 *
	 * @return  WebApplication  Instance of $this to allow chaining.
	 *
	 * @since   1.7.3
	 */
	public function appendBody($content)
	{
		$this->response->body[] = (string) $content;

		return $this;
	}

	/**
	 * Return the body content
	 *
	 * @param   boolean  $asArray  True to return the body as an array of strings.
	 *
	 * @return  mixed  The response body either as an array or concatenated string.
	 *
	 * @since   1.7.3
	 */
	public function getBody($asArray = false)
	{
		return $asArray ? $this->response->body : implode((array) $this->response->body);
	}

	/**
	 * Method to get the application document object.
	 *
	 * @return  \JDocument  The document object
	 *
	 * @since   1.7.3
	 */
	public function getDocument()
	{
		return $this->document;
	}

	/**
	 * Method to get the application language object.
	 *
	 * @return  \JLanguage  The language object
	 *
	 * @since   1.7.3
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Method to get the application session object.
	 *
	 * @return  \JSession  The session object
	 *
	 * @since   1.7.3
	 */
	public function getSession()
	{
		return $this->session;
	}

	/**
	 * Method to check the current client connection status to ensure that it is alive.  We are
	 * wrapping this to isolate the connection_status() function from our code base for testing reasons.
	 *
	 * @return  boolean  True if the connection is valid and normal.
	 *
	 * @see     connection_status()
	 * @since   1.7.3
	 */
	protected function checkConnectionAlive()
	{
		return connection_status() === CONNECTION_NORMAL;
	}

	/**
	 * Method to check to see if headers have already been sent.  We are wrapping this to isolate the
	 * headers_sent() function from our code base for testing reasons.
	 *
	 * @return  boolean  True if the headers have already been sent.
	 *
	 * @see     headers_sent()
	 * @since   1.7.3
	 */
	protected function checkHeadersSent()
	{
		return headers_sent();
	}

	/**
	 * Method to detect the requested URI from server environment variables.
	 *
	 * @return  string  The requested URI
	 *
	 * @since   1.7.3
	 */
	protected function detectRequestUri()
	{
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
		// Define variable to return
		$uri = '';

		// If PHP_SELF and REQUEST_URI are both populated then we will assume "Apache Mode".
		if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI']))
		{
			// The URI is built from the HTTP_HOST and REQUEST_URI environment variables in an Apache environment.
			$uri = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}
		// If not in "Apache Mode" we will assume that we are in an IIS environment and proceed.
		elseif (isset($_SERVER['HTTP_HOST']))
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

	/**
	 * Method to load a PHP configuration class file based on convention and return the instantiated data object.  You
	 * will extend this method in child classes to provide configuration data from whatever data source is relevant
	 * for your specific application.
	 *
	 * @param   string  $file   The path and filename of the configuration file. If not provided, configuration.php
	 *                          in JPATH_CONFIGURATION will be used.
	 * @param   string  $class  The class name to instantiate.
	 *
	 * @return  mixed   Either an array or object to be loaded into the configuration object.
	 *
	 * @since   1.7.3
	 * @throws  \RuntimeException
	 */
	protected function fetchConfigurationData($file = '', $class = '\JConfig')
	{
		// Instantiate variables.
		$config = array();

		if (empty($file))
		{
			$file = JPATH_CONFIGURATION . '/configuration.php';

			// Applications can choose not to have any configuration data
			// by not implementing this method and not having a config file.
			if (!file_exists($file))
			{
				$file = '';
			}
		}

		if (!empty($file))
		{
			\JLoader::register($class, $file);

			if (class_exists($class))
			{
				$config = new $class;
			}
			else
			{
				throw new \RuntimeException('Configuration class does not exist.');
			}
		}

		return $config;
	}

	/**
	 * Flush the media version to refresh versionable assets
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function flushAssets()
	{
		$version = new \JVersion;
		$version->refreshMediaVersion();
	}

	/**
	 * Method to send a header to the client.  We are wrapping this to isolate the header() function
	 * from our code base for testing reasons.
	 *
	 * @param   string   $string   The header string.
	 * @param   boolean  $replace  The optional replace parameter indicates whether the header should
	 *                             replace a previous similar header, or add a second header of the same type.
	 * @param   integer  $code     Forces the HTTP response code to the specified value. Note that
	 *                             this parameter only has an effect if the string is not empty.
	 *
	 * @return  void
	 *
	 * @see     header()
	 * @since   1.7.3
	 */
	protected function header($string, $replace = true, $code = null)
	{
		$string = str_replace(chr(0), '', $string);
		header($string, $replace, $code);
	}

	/**
	 * Determine if we are using a secure (SSL) connection.
	 *
	 * @return  boolean  True if using SSL, false if not.
	 *
	 * @since   3.0.1
	 */
	public function isSSLConnection()
	{
		return (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) || getenv('SSL_PROTOCOL_VERSION');
	}

	/**
	 * Allows the application to load a custom or default document.
	 *
	 * The logic and options for creating this object are adequately generic for default cases
	 * but for many applications it will make sense to override this method and create a document,
	 * if required, based on more specific needs.
	 *
	 * @param   \JDocument  $document  An optional document object. If omitted, the factory document is created.
	 *
	 * @return  WebApplication This method is chainable.
	 *
	 * @since   1.7.3
	 */
	public function loadDocument(\JDocument $document = null)
	{
		$this->document = ($document === null) ? \JFactory::getDocument() : $document;

		return $this;
	}

	/**
	 * Allows the application to load a custom or default language.
	 *
	 * The logic and options for creating this object are adequately generic for default cases
	 * but for many applications it will make sense to override this method and create a language,
	 * if required, based on more specific needs.
	 *
	 * @param   \JLanguage  $language  An optional language object. If omitted, the factory language is created.
	 *
	 * @return  WebApplication This method is chainable.
	 *
	 * @since   1.7.3
	 */
	public function loadLanguage(\JLanguage $language = null)
	{
		$this->language = ($language === null) ? \JFactory::getLanguage() : $language;

		return $this;
	}

	/**
	 * Allows the application to load a custom or default session.
	 *
	 * The logic and options for creating this object are adequately generic for default cases
	 * but for many applications it will make sense to override this method and create a session,
	 * if required, based on more specific needs.
	 *
	 * @param   \JSession  $session  An optional session object. If omitted, the session is created.
	 *
	 * @return  WebApplication This method is chainable.
	 *
	 * @since   1.7.3
	 */
	public function loadSession(\JSession $session = null)
	{
		if ($session !== null)
		{
			$this->session = $session;

			return $this;
		}

		// Generate a session name.
		$name = md5($this->get('secret') . $this->get('session_name', get_class($this)));

		// Calculate the session lifetime.
		$lifetime = (($this->get('sess_lifetime')) ? $this->get('sess_lifetime') * 60 : 900);

		// Get the session handler from the configuration.
		$handler = $this->get('sess_handler', 'none');

		// Initialize the options for \JSession.
		$options = array(
			'name' => $name,
			'expire' => $lifetime,
			'force_ssl' => $this->get('force_ssl'),
		);

		$this->registerEvent('onAfterSessionStart', array($this, 'afterSessionStart'));

		// Instantiate the session object.
		$session = \JSession::getInstance($handler, $options);
		$session->initialise($this->input, $this->dispatcher);

		if ($session->getState() == 'expired')
		{
			$session->restart();
		}
		else
		{
			$session->start();
		}

		// Set the session object.
		$this->session = $session;

		return $this;
	}

	/**
	 * After the session has been started we need to populate it with some default values.
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 */
	public function afterSessionStart()
	{
		$session = \JFactory::getSession();

		if ($session->isNew())
		{
			$session->set('registry', new Registry);
			$session->set('user', new \JUser);
		}
	}

	/**
	 * Method to load the system URI strings for the application.
	 *
	 * @param   string  $requestUri  An optional request URI to use instead of detecting one from the
	 *                               server environment variables.
	 *
	 * @return  void
	 *
	 * @since   1.7.3
	 */
	protected function loadSystemUris($requestUri = null)
	{
		// Set the request URI.
		if (!empty($requestUri))
		{
			$this->set('uri.request', $requestUri);
		}
		else
		{
			$this->set('uri.request', $this->detectRequestUri());
		}

		// Check to see if an explicit base URI has been set.
		$siteUri = trim($this->get('site_uri'));

		if ($siteUri != '')
		{
			$uri = \JUri::getInstance($siteUri);
			$path = $uri->toString(array('path'));
		}
		// No explicit base URI was set so we need to detect it.
		else
		{
			// Start with the requested URI.
			$uri = \JUri::getInstance($this->get('uri.request'));

			// If we are working from a CGI SAPI with the 'cgi.fix_pathinfo' directive disabled we use PHP_SELF.
			if (strpos(php_sapi_name(), 'cgi') !== false && !ini_get('cgi.fix_pathinfo') && !empty($_SERVER['REQUEST_URI']))
			{
				// We aren't expecting PATH_INFO within PHP_SELF so this should work.
				$path = dirname($_SERVER['PHP_SELF']);
			}
			// Pretty much everything else should be handled with SCRIPT_NAME.
			else
			{
				$path = dirname($_SERVER['SCRIPT_NAME']);
			}
		}

		$host = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));

		// Check if the path includes "index.php".
		if (strpos($path, 'index.php') !== false)
		{
			// Remove the index.php portion of the path.
			$path = substr_replace($path, '', strpos($path, 'index.php'), 9);
		}

		$path = rtrim($path, '/\\');

		// Set the base URI both as just a path and as the full URI.
		$this->set('uri.base.full', $host . $path . '/');
		$this->set('uri.base.host', $host);
		$this->set('uri.base.path', $path . '/');

		// Set the extended (non-base) part of the request URI as the route.
		if (stripos($this->get('uri.request'), $this->get('uri.base.full')) === 0)
		{
			$this->set('uri.route', substr_replace($this->get('uri.request'), '', 0, strlen($this->get('uri.base.full'))));
		}

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
				// Normalise slashes.
				$mediaURI = trim($mediaURI, '/\\');
				$mediaURI = !empty($mediaURI) ? '/' . $mediaURI . '/' : '/';
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
