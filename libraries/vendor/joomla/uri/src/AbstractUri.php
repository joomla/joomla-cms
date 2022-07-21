<?php
/**
 * Part of the Joomla Framework Uri Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Uri;

/**
 * Uri Class
 *
 * Abstract base for out uri classes.
 *
 * This class should be considered an implementation detail. Typehint against UriInterface.
 *
 * @since  1.0
 */
abstract class AbstractUri implements UriInterface
{
	/**
	 * @var    string  Original URI
	 * @since  1.0
	 */
	protected $uri;

	/**
	 * @var    string  Protocol
	 * @since  1.0
	 */
	protected $scheme;

	/**
	 * @var    string  Host
	 * @since  1.0
	 */
	protected $host;

	/**
	 * @var    integer  Port
	 * @since  1.0
	 */
	protected $port;

	/**
	 * @var    string  Username
	 * @since  1.0
	 */
	protected $user;

	/**
	 * @var    string  Password
	 * @since  1.0
	 */
	protected $pass;

	/**
	 * @var    string  Path
	 * @since  1.0
	 */
	protected $path;

	/**
	 * @var    string  Query
	 * @since  1.0
	 */
	protected $query;

	/**
	 * @var    string  Anchor
	 * @since  1.0
	 */
	protected $fragment;

	/**
	 * @var    array  Query variable hash
	 * @since  1.0
	 */
	protected $vars = array();

	/**
	 * Constructor.
	 * You can pass a URI string to the constructor to initialise a specific URI.
	 *
	 * @param   string  $uri  The optional URI string
	 *
	 * @since   1.0
	 */
	public function __construct($uri = null)
	{
		if ($uri !== null)
		{
			$this->parse($uri);
		}
	}

	/**
	 * Magic method to get the string representation of the URI object.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Returns full uri string.
	 *
	 * @param   array  $parts  An array of strings specifying the parts to render.
	 *
	 * @return  string  The rendered URI string.
	 *
	 * @since   1.0
	 */
	public function toString(array $parts = array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'))
	{
		$bitmask = 0;

		foreach ($parts as $part)
		{
			$const = 'static::' . strtoupper($part);

			if (\defined($const))
			{
				$bitmask |= \constant($const);
			}
		}

		return $this->render($bitmask);
	}

	/**
	 * Returns full uri string.
	 *
	 * @param   integer  $parts  A bitmask specifying the parts to render.
	 *
	 * @return  string  The rendered URI string.
	 *
	 * @since   1.2.0
	 */
	public function render($parts = self::ALL)
	{
		// Make sure the query is created
		$query = $this->getQuery();

		$uri = '';
		$uri .= $parts & static::SCHEME ? (!empty($this->scheme) ? $this->scheme . '://' : '') : '';
		$uri .= $parts & static::USER ? $this->user : '';
		$uri .= $parts & static::PASS ? (!empty($this->pass) ? ':' : '') . $this->pass . (!empty($this->user) ? '@' : '') : '';
		$uri .= $parts & static::HOST ? $this->host : '';
		$uri .= $parts & static::PORT ? (!empty($this->port) ? ':' : '') . $this->port : '';
		$uri .= $parts & static::PATH ? $this->path : '';
		$uri .= $parts & static::QUERY ? (!empty($query) ? '?' . $query : '') : '';
		$uri .= $parts & static::FRAGMENT ? (!empty($this->fragment) ? '#' . $this->fragment : '') : '';

		return $uri;
	}

	/**
	 * Checks if variable exists.
	 *
	 * @param   string  $name  Name of the query variable to check.
	 *
	 * @return  boolean  True if the variable exists.
	 *
	 * @since   1.0
	 */
	public function hasVar($name)
	{
		return array_key_exists($name, $this->vars);
	}

	/**
	 * Returns a query variable by name.
	 *
	 * @param   string  $name     Name of the query variable to get.
	 * @param   string  $default  Default value to return if the variable is not set.
	 *
	 * @return  mixed   Value of the specified query variable.
	 *
	 * @since   1.0
	 */
	public function getVar($name, $default = null)
	{
		if (array_key_exists($name, $this->vars))
		{
			return $this->vars[$name];
		}

		return $default;
	}

	/**
	 * Returns flat query string.
	 *
	 * @param   boolean  $toArray  True to return the query as a key => value pair array.
	 *
	 * @return  string|array   Query string or Array of parts in query string depending on the function param
	 *
	 * @since   1.0
	 */
	public function getQuery($toArray = false)
	{
		if ($toArray)
		{
			return $this->vars;
		}

		// If the query is empty build it first
		if ($this->query === null)
		{
			$this->query = self::buildQuery($this->vars);
		}

		return $this->query;
	}

	/**
	 * Get URI scheme (protocol)
	 * ie. http, https, ftp, etc...
	 *
	 * @return  string  The URI scheme.
	 *
	 * @since   1.0
	 */
	public function getScheme()
	{
		return $this->scheme;
	}

	/**
	 * Get URI username
	 * Returns the username, or null if no username was specified.
	 *
	 * @return  string  The URI username.
	 *
	 * @since   1.0
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Get URI password
	 * Returns the password, or null if no password was specified.
	 *
	 * @return  string  The URI password.
	 *
	 * @since   1.0
	 */
	public function getPass()
	{
		return $this->pass;
	}

	/**
	 * Get URI host
	 * Returns the hostname/ip or null if no hostname/ip was specified.
	 *
	 * @return  string  The URI host.
	 *
	 * @since   1.0
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Get URI port
	 * Returns the port number, or null if no port was specified.
	 *
	 * @return  integer  The URI port number.
	 *
	 * @since   1.0
	 */
	public function getPort()
	{
		return (isset($this->port)) ? $this->port : null;
	}

	/**
	 * Gets the URI path string.
	 *
	 * @return  string  The URI path string.
	 *
	 * @since   1.0
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Get the URI anchor string
	 * Everything after the "#".
	 *
	 * @return  string  The URI anchor string.
	 *
	 * @since   1.0
	 */
	public function getFragment()
	{
		return $this->fragment;
	}

	/**
	 * Checks whether the current URI is using HTTPS.
	 *
	 * @return  boolean  True if using SSL via HTTPS.
	 *
	 * @since   1.0
	 */
	public function isSsl()
	{
		return $this->getScheme() == 'https' ? true : false;
	}

	/**
	 * Build a query from an array (reverse of the PHP parse_str()).
	 *
	 * @param   array  $params  The array of key => value pairs to return as a query string.
	 *
	 * @return  string  The resulting query string.
	 *
	 * @see     parse_str()
	 * @since   1.0
	 */
	protected static function buildQuery(array $params)
	{
		return urldecode(http_build_query($params, '', '&'));
	}

	/**
	 * Parse a given URI and populate the class fields.
	 *
	 * @param   string  $uri  The URI string to parse.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 */
	protected function parse($uri)
	{
		// Set the original URI to fall back on
		$this->uri = $uri;

		/*
		 * Parse the URI and populate the object fields. If URI is parsed properly,
		 * set method return value to true.
		 */

		$parts = UriHelper::parse_url($uri);

		$retval = ($parts) ? true : false;

		// We need to replace &amp; with & for parse_str to work right...
		if (isset($parts['query']) && strpos($parts['query'], '&amp;') !== false)
		{
			$parts['query'] = str_replace('&amp;', '&', $parts['query']);
		}

		$this->scheme   = isset($parts['scheme']) ? $parts['scheme'] : null;
		$this->user     = isset($parts['user']) ? $parts['user'] : null;
		$this->pass     = isset($parts['pass']) ? $parts['pass'] : null;
		$this->host     = isset($parts['host']) ? $parts['host'] : null;
		$this->port     = isset($parts['port']) ? $parts['port'] : null;
		$this->path     = isset($parts['path']) ? $parts['path'] : null;
		$this->query    = isset($parts['query']) ? $parts['query'] : null;
		$this->fragment = isset($parts['fragment']) ? $parts['fragment'] : null;

		// Parse the query
		if (isset($parts['query']))
		{
			parse_str($parts['query'], $this->vars);
		}

		return $retval;
	}

	/**
	 * Resolves //, ../ and ./ from a path and returns
	 * the result. Eg:
	 *
	 * /foo/bar/../boo.php	=> /foo/boo.php
	 * /foo/bar/../../boo.php => /boo.php
	 * /foo/bar/.././/boo.php => /foo/boo.php
	 *
	 * @param   string  $path  The URI path to clean.
	 *
	 * @return  string  Cleaned and resolved URI path.
	 *
	 * @since   1.0
	 */
	protected function cleanPath($path)
	{
		$path = explode('/', preg_replace('#(/+)#', '/', $path));

		for ($i = 0, $n = \count($path); $i < $n; $i++)
		{
			if ($path[$i] == '.' || $path[$i] == '..')
			{
				if (($path[$i] == '.') || ($path[$i] == '..' && $i == 1 && $path[0] == ''))
				{
					unset($path[$i]);
					$path = array_values($path);
					$i--;
					$n--;
				}
				elseif ($path[$i] == '..' && ($i > 1 || ($i == 1 && $path[0] != '')))
				{
					unset($path[$i], $path[$i - 1]);

					$path = array_values($path);
					$i -= 2;
					$n -= 2;
				}
			}
		}

		return implode('/', $path);
	}
}
