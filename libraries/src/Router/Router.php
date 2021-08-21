<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Router;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\CMS\Uri\Uri;

/**
 * Class to create and parse routes
 *
 * @since  1.5
 */
class Router
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
	 * An array of variables
	 *
	 * @var     array
	 * @since   1.5
	 */
	protected $vars = array();

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
	 * Caching of processed URIs
	 *
	 * @var    array
	 * @since  3.3
	 */
	protected $cache = array();

	/**
	 * Router instances container.
	 *
	 * @var    Router[]
	 * @since  1.7
	 */
	protected static $instances = array();

	/**
	 * Returns the global Router object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param   string  $client   The name of the client
	 * @param   array   $options  An associative array of options
	 *
	 * @return  Router  A Router object.
	 *
	 * @since   1.5
	 * @throws  \RuntimeException
	 */
	public static function getInstance($client, $options = array())
	{
		if (empty(self::$instances[$client]))
		{
			// Create a Router object
			$classname = 'JRouter' . ucfirst($client);

			if (!class_exists($classname))
			{
				throw new \RuntimeException(Text::sprintf('JLIB_APPLICATION_ERROR_ROUTER_LOAD', $client), 500);
			}

			// Check for a possible service from the container otherwise manually instantiate the class
			if (Factory::getContainer()->has($classname))
			{
				self::$instances[$client] = Factory::getContainer()->get($classname);
			}
			else
			{
				self::$instances[$client] = new $classname;
			}
		}

		return self::$instances[$client];
	}

	/**
	 * Function to convert a route to an internal URI
	 *
	 * @param   Uri   &$uri     The uri.
	 * @param   bool  $setVars  Set the parsed data in the internal
	 *                          storage for current-request-URLs
	 *
	 * @return  array
	 *
	 * @since   1.5
	 * @throws  \Exception
	 */
	public function parse(&$uri, $setVars = false)
	{
		// Do the preprocess stage of the URL parse process
		$this->processParseRules($uri, self::PROCESS_BEFORE);

		// Do the main stage of the URL parse process
		$this->processParseRules($uri);

		// Do the postprocess stage of the URL parse process
		$this->processParseRules($uri, self::PROCESS_AFTER);

		// Check if all parts of the URL have been parsed.
		// Otherwise we have an invalid URL
		if (\strlen($uri->getPath()) > 0)
		{
			throw new RouteNotFoundException(Text::_('JERROR_PAGE_NOT_FOUND'));
		}

		if ($setVars)
		{
			$this->setVars($uri->getQuery(true));

			return $this->getVars();
		}

		return $uri->getQuery(true);
	}

	/**
	 * Function to convert an internal URI to a route
	 *
	 * @param   string|array|Uri  $url  The internal URL or an associative array
	 *
	 * @return  Uri  The absolute search engine friendly URL object
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

		if ($url instanceof Uri)
		{
			$uri = $url;
		}
		else
		{
			$uri = $this->createUri($url);
		}

		// Do the preprocess stage of the URL build process
		$this->processBuildRules($uri, self::PROCESS_BEFORE);

		// Do the main stage of the URL build process
		$this->processBuildRules($uri);

		// Do the postprocess stage of the URL build process
		$this->processBuildRules($uri, self::PROCESS_AFTER);

		$this->cache[$key] = clone $uri;

		return $uri;
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
		if ($create || \array_key_exists($key, $this->vars))
		{
			$this->vars[$key] = $value;
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
			$this->vars = array_merge($this->vars, $vars);
		}
		else
		{
			$this->vars = $vars;
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

		if (isset($this->vars[$key]))
		{
			$result = $this->vars[$key];
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
		return $this->vars;
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
	public function attachBuildRule(callable $callback, $stage = self::PROCESS_DURING)
	{
		if (!\array_key_exists('build' . $stage, $this->rules))
		{
			throw new \InvalidArgumentException(sprintf('The %s stage is not registered. (%s)', $stage, __METHOD__));
		}

		$this->rules['build' . $stage][] = $callback;
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
	public function attachParseRule(callable $callback, $stage = self::PROCESS_DURING)
	{
		if (!\array_key_exists('parse' . $stage, $this->rules))
		{
			throw new \InvalidArgumentException(sprintf('The %s stage is not registered. (%s)', $stage, __METHOD__));
		}

		$this->rules['parse' . $stage][] = $callback;
	}

	/**
	 * Remove a rule
	 *
	 * @param   string    $type   Type of rule to remove (parse or build)
	 * @param   callable  $rule   The rule to be removed.
	 * @param   string    $stage  The stage of the parse process that
	 *                             this should be added to. Possible values:
	 *                             'preprocess', '' for the main parse process,
	 *                             'postprocess'
	 *
	 * @return   boolean  Was a rule removed?
	 *
	 * @since   4.0.0
	 * @throws  \InvalidArgumentException
	 */
	public function detachRule($type, $rule, $stage = self::PROCESS_DURING)
	{
		if (!\in_array($type, array('parse', 'build')))
		{
			throw new \InvalidArgumentException(sprintf('The %s type is not supported. (%s)', $type, __METHOD__));
		}

		if (!\array_key_exists($type . $stage, $this->rules))
		{
			throw new \InvalidArgumentException(sprintf('The %s stage is not registered. (%s)', $stage, __METHOD__));
		}

		foreach ($this->rules[$type . $stage] as $id => $r)
		{
			if ($r == $rule)
			{
				unset($this->rules[$type . $stage][$id]);

				return true;
			}
		}

		return false;
	}

	/**
	 * Get all currently attached rules
	 *
	 * @return  array  All currently attached rules in an array
	 *
	 * @since   4.0.0
	 */
	public function getRules()
	{
		return $this->rules;
	}

	/**
	 * Process the parsed router variables based on custom defined rules
	 *
	 * @param   \Joomla\CMS\Uri\Uri  &$uri   The URI to parse
	 * @param   string               $stage  The stage that should be processed.
	 *                                       Possible values: 'preprocess', 'postprocess'
	 *                                       and '' for the main parse stage
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function processParseRules(&$uri, $stage = self::PROCESS_DURING)
	{
		if (!\array_key_exists('parse' . $stage, $this->rules))
		{
			throw new \InvalidArgumentException(sprintf('The %s stage is not registered. (%s)', $stage, __METHOD__));
		}

		foreach ($this->rules['parse' . $stage] as $rule)
		{
			$rule($this, $uri);
		}
	}

	/**
	 * Process the build uri query data based on custom defined rules
	 *
	 * @param   \Joomla\CMS\Uri\Uri  &$uri   The URI
	 * @param   string               $stage  The stage that should be processed.
	 *                                       Possible values: 'preprocess', 'postprocess'
	 *                                       and '' for the main build stage
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function processBuildRules(&$uri, $stage = self::PROCESS_DURING)
	{
		if (!\array_key_exists('build' . $stage, $this->rules))
		{
			throw new \InvalidArgumentException(sprintf('The %s stage is not registered. (%s)', $stage, __METHOD__));
		}

		foreach ($this->rules['build' . $stage] as $rule)
		{
			\call_user_func_array($rule, array(&$this, &$uri));
		}
	}

	/**
	 * Create a uri based on a full or partial URL string
	 *
	 * @param   string  $url  The URI or an associative array
	 *
	 * @return  Uri
	 *
	 * @since   3.2
	 */
	protected function createUri($url)
	{
		if (!\is_array($url) && substr($url, 0, 1) !== '&')
		{
			return new Uri($url);
		}

		$uri = new Uri('index.php');

		if (\is_string($url))
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
}
