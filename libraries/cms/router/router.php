<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Mask for the raw routing mode
 *
 * @deprecated  4.0
 */
const JROUTER_MODE_RAW = 0;

/**
 * Mask for the SEF routing mode
 *
 * @deprecated  4.0
 */
const JROUTER_MODE_SEF = 1;

/**
 * Class to create and parse routes
 *
 * @since  1.5
 */
class JRouter
{
	/**
	 * Mask for the before process stage
	 *
	 * @var    string
	 * @since  3.4
	 */
	const PROCESS_BEFORE = 'preprocess';

	/**
	 * Mask for the during process stage
	 *
	 * @var    string
	 * @since  3.4
	 */
	const PROCESS_DURING = '';

	/**
	 * Mask for the after process stage
	 *
	 * @var    string
	 * @since  3.4
	 */
	const PROCESS_AFTER = 'postprocess';

	/**
	 * The rewrite mode
	 *
	 * @var    integer
	 * @since  1.5
	 * @deprecated  4.0
	 */
	protected $mode = null;

	/**
	 * The rewrite mode
	 *
	 * @var    integer
	 * @since  1.5
	 * @deprecated  4.0
	 */
	protected $_mode = null;

	/**
	 * An array of variables
	 *
	 * @var     array
	 * @since   1.5
	 */
	protected $vars = array();

	/**
	 * An array of variables
	 *
	 * @var     array
	 * @since  1.5
	 * @deprecated  4.0 Will convert to $vars
	 */
	protected $_vars = array();

	/**
	 * An array of rules
	 *
	 * @var    array
	 * @since  1.5
	 */
	protected $rules = array(
		'buildpreprocess' => array(),
		'build' => array(),
		'buildpostprocess' => array(),
		'parsepreprocess' => array(),
		'parse' => array(),
		'parsepostprocess' => array(),
	);

	/**
	 * An array of rules
	 *
	 * @var    array
	 * @since  1.5
	 * @deprecated  4.0 Will convert to $rules
	 */
	protected $_rules = array(
		'buildpreprocess' => array(),
		'build' => array(),
		'buildpostprocess' => array(),
		'parsepreprocess' => array(),
		'parse' => array(),
		'parsepostprocess' => array(),
	);

	/**
	 * Caching of processed URIs
	 *
	 * @var    array
	 * @since  3.3
	 */
	protected $cache = array();

	/**
	 * JRouter instances container.
	 *
	 * @var    JRouter[]
	 * @since  1.7
	 */
	protected static $instances = array();

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   1.5
	 */
	public function __construct($options = array())
	{
		if (array_key_exists('mode', $options))
		{
			$this->_mode = $options['mode'];
		}
		else
		{
			$this->_mode = JROUTER_MODE_RAW;
		}
	}

	/**
	 * Returns the global JRouter object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param   string  $client   The name of the client
	 * @param   array   $options  An associative array of options
	 *
	 * @return  JRouter  A JRouter object.
	 *
	 * @since   1.5
	 * @throws  RuntimeException
	 */
	public static function getInstance($client, $options = array())
	{
		if (empty(self::$instances[$client]))
		{
			// Create a JRouter object
			$classname = 'JRouter' . ucfirst($client);

			if (!class_exists($classname))
			{
				// @deprecated 4.0 Everything in this block is deprecated but the warning is only logged after the file_exists
				// Load the router object
				$info = JApplicationHelper::getClientInfo($client, true);

				if (is_object($info))
				{
					$path = $info->path . '/includes/router.php';

					JLoader::register($classname, $path);

					if (class_exists($classname))
					{
						JLog::add('Non-autoloadable JRouter subclasses are deprecated, support will be removed in 4.0.', JLog::WARNING, 'deprecated');
					}
				}
			}

			if (class_exists($classname))
			{
				self::$instances[$client] = new $classname($options);
			}
			else
			{
				throw new RuntimeException(JText::sprintf('JLIB_APPLICATION_ERROR_ROUTER_LOAD', $client), 500);
			}
		}

		return self::$instances[$client];
	}

	/**
	 * Function to convert a route to an internal URI
	 *
	 * @param   JUri  &$uri  The uri.
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public function parse(&$uri)
	{
		// Do the preprocess stage of the URL build process
		$vars = $this->processParseRules($uri, self::PROCESS_BEFORE);

		// Process the parsed variables based on custom defined rules
		// This is the main parse stage
		$vars += $this->_processParseRules($uri);

		// Parse RAW URL
		if ($this->_mode == JROUTER_MODE_RAW)
		{
			$vars += $this->_parseRawRoute($uri);
		}

		// Parse SEF URL
		if ($this->_mode == JROUTER_MODE_SEF)
		{
			$vars += $this->_parseSefRoute($uri);
		}

		// Do the postprocess stage of the URL build process
		$vars += $this->processParseRules($uri, self::PROCESS_AFTER);

		// Check if all parts of the URL have been parsed.
		// Otherwise we have an invalid URL
		if (strlen($uri->getPath()) > 0 && array_key_exists('option', $vars)
			&& JComponentHelper::getParams($vars['option'])->get('sef_advanced', 0))
		{
			throw new Exception('URL invalid', 404);
		}

		return array_merge($this->getVars(), $vars);
	}

	/**
	 * Function to convert an internal URI to a route
	 *
	 * @param   string  $url  The internal URL or an associative array
	 *
	 * @return  JUri  The absolute search engine friendly URL object
	 *
	 * @since   1.5
	 */
	public function build($url)
	{
		$key = md5(serialize($url));

		if (isset($this->cache[$key]))
		{
			return clone $this->cache[$key];
		}

		// Create the URI object
		$uri = $this->createUri($url);

		// Do the preprocess stage of the URL build process
		$this->processBuildRules($uri, self::PROCESS_BEFORE);

		// Process the uri information based on custom defined rules.
		// This is the main build stage
		$this->_processBuildRules($uri);

		// Build RAW URL
		if ($this->_mode == JROUTER_MODE_RAW)
		{
			$this->_buildRawRoute($uri);
		}

		// Build SEF URL : mysite/route/index.php?var=x
		if ($this->_mode == JROUTER_MODE_SEF)
		{
			$this->_buildSefRoute($uri);
		}

		// Do the postprocess stage of the URL build process
		$this->processBuildRules($uri, self::PROCESS_AFTER);

		$this->cache[$key] = clone $uri;

		return $uri;
	}

	/**
	 * Get the router mode
	 *
	 * @return  integer
	 *
	 * @since   1.5
	 * @deprecated  4.0
	 */
	public function getMode()
	{
		return $this->_mode;
	}

	/**
	 * Set the router mode
	 *
	 * @param   integer  $mode  The routing mode.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  4.0
	 */
	public function setMode($mode)
	{
		$this->_mode = $mode;
	}

	/**
	 * Set a router variable, creating it if it doesn't exist
	 *
	 * @param   string   $key     The name of the variable
	 * @param   mixed    $value   The value of the variable
	 * @param   boolean  $create  If True, the variable will be created if it doesn't exist yet
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function setVar($key, $value, $create = true)
	{
		if ($create || array_key_exists($key, $this->_vars))
		{
			$this->_vars[$key] = $value;
		}
	}

	/**
	 * Set the router variable array
	 *
	 * @param   array    $vars   An associative array with variables
	 * @param   boolean  $merge  If True, the array will be merged instead of overwritten
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function setVars($vars = array(), $merge = true)
	{
		if ($merge)
		{
			$this->_vars = array_merge($this->_vars, $vars);
		}
		else
		{
			$this->_vars = $vars;
		}
	}

	/**
	 * Get a router variable
	 *
	 * @param   string  $key  The name of the variable
	 *
	 * @return  mixed  Value of the variable
	 *
	 * @since   1.5
	 */
	public function getVar($key)
	{
		$result = null;

		if (isset($this->_vars[$key]))
		{
			$result = $this->_vars[$key];
		}

		return $result;
	}

	/**
	 * Get the router variable array
	 *
	 * @return  array  An associative array of router variables
	 *
	 * @since   1.5
	 */
	public function getVars()
	{
		return $this->_vars;
	}

	/**
	 * Attach a build rule
	 *
	 * @param   callable  $callback  The function to be called
	 * @param   string    $stage     The stage of the build process that
	 *                               this should be added to. Possible values:
	 *                               'preprocess', '' for the main build process,
	 *                               'postprocess'
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function attachBuildRule($callback, $stage = self::PROCESS_DURING)
	{
		if (!array_key_exists('build' . $stage, $this->_rules))
		{
			throw new InvalidArgumentException(sprintf('The %s stage is not registered. (%s)', $stage, __METHOD__));
		}

		$this->_rules['build' . $stage][] = $callback;
	}

	/**
	 * Attach a parse rule
	 *
	 * @param   callable  $callback  The function to be called.
	 * @param   string    $stage     The stage of the parse process that
	 *                               this should be added to. Possible values:
	 *                               'preprocess', '' for the main parse process,
	 *                               'postprocess'
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function attachParseRule($callback, $stage = self::PROCESS_DURING)
	{
		if (!array_key_exists('parse' . $stage, $this->_rules))
		{
			throw new InvalidArgumentException(sprintf('The %s stage is not registered. (%s)', $stage, __METHOD__));
		}

		$this->_rules['parse' . $stage][] = $callback;
	}

	/**
	 * Function to convert a raw route to an internal URI
	 *
	 * @param   JUri  &$uri  The raw route
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 * @deprecated  4.0  Attach your logic as rule to the main parse stage
	 */
	protected function _parseRawRoute(&$uri)
	{
		return $this->parseRawRoute($uri);
	}

	/**
	 * Function to convert a raw route to an internal URI
	 *
	 * @param   JUri  &$uri  The raw route
	 *
	 * @return  array  Array of variables
	 *
	 * @since   3.2
	 * @deprecated  4.0  Attach your logic as rule to the main parse stage
	 */
	protected function parseRawRoute(&$uri)
	{
		return array();
	}

	/**
	 * Function to convert a sef route to an internal URI
	 *
	 * @param   JUri  &$uri  The sef URI
	 *
	 * @return  string  Internal URI
	 *
	 * @since   1.5
	 * @deprecated  4.0  Attach your logic as rule to the main parse stage
	 */
	protected function _parseSefRoute(&$uri)
	{
		return $this->parseSefRoute($uri);
	}

	/**
	 * Function to convert a sef route to an internal URI
	 *
	 * @param   JUri  &$uri  The sef URI
	 *
	 * @return  array  Array of variables
	 *
	 * @since   3.2
	 * @deprecated  4.0  Attach your logic as rule to the main parse stage
	 */
	protected function parseSefRoute(&$uri)
	{
		return array();
	}

	/**
	 * Function to build a raw route
	 *
	 * @param   JUri  &$uri  The internal URL
	 *
	 * @return  string  Raw Route
	 *
	 * @since   1.5
	 * @deprecated  4.0  Attach your logic as rule to the main build stage
	 */
	protected function _buildRawRoute(&$uri)
	{
		return $this->buildRawRoute($uri);
	}

	/**
	 * Function to build a raw route
	 *
	 * @param   JUri  &$uri  The internal URL
	 *
	 * @return  string  Raw Route
	 *
	 * @since   3.2
	 * @deprecated  4.0  Attach your logic as rule to the main build stage
	 */
	protected function buildRawRoute(&$uri)
	{
	}

	/**
	 * Function to build a sef route
	 *
	 * @param   JUri  &$uri  The uri
	 *
	 * @return  string  The SEF route
	 *
	 * @since   1.5
	 * @deprecated  4.0  Attach your logic as rule to the main build stage
	 */
	protected function _buildSefRoute(&$uri)
	{
		return $this->buildSefRoute($uri);
	}

	/**
	 * Function to build a sef route
	 *
	 * @param   JUri  &$uri  The uri
	 *
	 * @return  string  The SEF route
	 *
	 * @since   3.2
	 * @deprecated  4.0  Attach your logic as rule to the main build stage
	 */
	protected function buildSefRoute(&$uri)
	{
	}

	/**
	 * Process the parsed router variables based on custom defined rules
	 *
	 * @param   JUri  &$uri  The URI to parse
	 *
	 * @return  array  The array of processed URI variables
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use processParseRules() instead
	 */
	protected function _processParseRules(&$uri)
	{
		return $this->processParseRules($uri);
	}

	/**
	 * Process the parsed router variables based on custom defined rules
	 *
	 * @param   JUri    &$uri   The URI to parse
	 * @param   string  $stage  The stage that should be processed.
	 *                          Possible values: 'preprocess', 'postprocess'
	 *                          and '' for the main parse stage
	 *
	 * @return  array  The array of processed URI variables
	 *
	 * @since   3.2
	 */
	protected function processParseRules(&$uri, $stage = self::PROCESS_DURING)
	{
		if (!array_key_exists('parse' . $stage, $this->_rules))
		{
			throw new InvalidArgumentException(sprintf('The %s stage is not registered. (%s)', $stage, __METHOD__));
		}

		$vars = array();

		foreach ($this->_rules['parse' . $stage] as $rule)
		{
			$vars += (array) call_user_func_array($rule, array(&$this, &$uri));
		}

		return $vars;
	}

	/**
	 * Process the build uri query data based on custom defined rules
	 *
	 * @param   JUri  &$uri  The URI
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use processBuildRules() instead
	 */
	protected function _processBuildRules(&$uri)
	{
		$this->processBuildRules($uri);
	}

	/**
	 * Process the build uri query data based on custom defined rules
	 *
	 * @param   JUri    &$uri   The URI
	 * @param   string  $stage  The stage that should be processed.
	 *                          Possible values: 'preprocess', 'postprocess'
	 *                          and '' for the main build stage
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function processBuildRules(&$uri, $stage = self::PROCESS_DURING)
	{
		if (!array_key_exists('build' . $stage, $this->_rules))
		{
			throw new InvalidArgumentException(sprintf('The %s stage is not registered. (%s)', $stage, __METHOD__));
		}

		foreach ($this->_rules['build' . $stage] as $rule)
		{
			call_user_func_array($rule, array(&$this, &$uri));
		}
	}

	/**
	 * Create a uri based on a full or partial URL string
	 *
	 * @param   string  $url  The URI
	 *
	 * @return  JUri
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use createUri() instead
	 * @codeCoverageIgnore
	 */
	protected function _createUri($url)
	{
		return $this->createUri($url);
	}

	/**
	 * Create a uri based on a full or partial URL string
	 *
	 * @param   string  $url  The URI or an associative array
	 *
	 * @return  JUri
	 *
	 * @since   3.2
	 */
	protected function createUri($url)
	{
		if (!is_array($url) && substr($url, 0, 1) != '&')
		{
			return new JUri($url);
		}

		$uri = new JUri('index.php');

		if (is_string($url))
		{
			$vars = array();

			if (strpos($url, '&amp;') !== false)
			{
				$url = str_replace('&amp;', '&', $url);
			}

			parse_str($url, $vars);
		}
		else
		{
			$vars = $url;
		}

		$vars = array_merge($this->getVars(), $vars);

		foreach ($vars as $key => $var)
		{
			if ($var == '')
			{
				unset($vars[$key]);
			}
		}

		$uri->setQuery($vars);

		return $uri;
	}

	/**
	 * Encode route segments
	 *
	 * @param   array  $segments  An array of route segments
	 *
	 * @return  array  Array of encoded route segments
	 *
	 * @since   1.5
	 * @deprecated  4.0  This should be performed in the component router instead
	 * @codeCoverageIgnore
	 */
	protected function _encodeSegments($segments)
	{
		return $this->encodeSegments($segments);
	}

	/**
	 * Encode route segments
	 *
	 * @param   array  $segments  An array of route segments
	 *
	 * @return  array  Array of encoded route segments
	 *
	 * @since   3.2
	 * @deprecated  4.0  This should be performed in the component router instead
	 */
	protected function encodeSegments($segments)
	{
		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	/**
	 * Decode route segments
	 *
	 * @param   array  $segments  An array of route segments
	 *
	 * @return  array  Array of decoded route segments
	 *
	 * @since   1.5
	 * @deprecated  4.0  This should be performed in the component router instead
	 * @codeCoverageIgnore
	 */
	protected function _decodeSegments($segments)
	{
		return $this->decodeSegments($segments);
	}

	/**
	 * Decode route segments
	 *
	 * @param   array  $segments  An array of route segments
	 *
	 * @return  array  Array of decoded route segments
	 *
	 * @since   3.2
	 * @deprecated  4.0  This should be performed in the component router instead
	 */
	protected function decodeSegments($segments)
	{
		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}

		return $segments;
	}
}
