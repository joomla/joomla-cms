<?php
/**
 * Part of the Joomla Framework Uri Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Uri;

/**
 * Base Joomla Uri Class
 *
 * @since  1.0
 */
abstract class AbstractUri implements UriInterface
{
	/**
	 * Original URI
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $uri;

	/**
	 * Protocol
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $scheme;

	/**
	 * Host
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $host;

	/**
	 * Port
	 *
	 * @var    integer
	 * @since  1.0
	 */
	protected $port;

	/**
	 * Username
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $user;

	/**
	 * Password
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $pass;

	/**
	 * Path
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $path;

	/**
	 * Query
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $query;

	/**
	 * Anchor
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $fragment;

	/**
	 * Query variable hash
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $vars = [];

	/**
	 * Constructor.
	 *
	 * You can pass a URI string to the constructor to initialise a specific URI.
	 *
	 * @param   string  $uri  The optional URI string
	 *
	 * @since   1.0
	 */
	public function __construct($uri = null)
	{
		if (!is_null($uri))
		{
			$this->parse($uri);
		}
	}

	/**
	 * Magic method to get the string representation of the UriInterface object.
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
	 * Returns full URI string.
	 *
	 * @param   array  $parts  An array specifying the parts to render.
	 *
	 * @return  string  The rendered URI string.
	 *
	 * @since   1.0
	 */
	public function toString(array $parts = ['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'])
	{
		// Make sure the query is created
		$query = $this->getQuery();

		$uri = '';
		$uri .= in_array('scheme', $parts) ? (!empty($this->scheme) ? $this->scheme . '://' : '') : '';
		$uri .= in_array('user', $parts) ? $this->user : '';
		$uri .= in_array('pass', $parts) ? (!empty($this->pass) ? ':' : '') . $this->pass . (!empty($this->user) ? '@' : '') : '';
		$uri .= in_array('host', $parts) ? $this->host : '';
		$uri .= in_array('port', $parts) ? (!empty($this->port) ? ':' : '') . $this->port : '';
		$uri .= in_array('path', $parts) ? $this->path : '';
		$uri .= in_array('query', $parts) ? (!empty($query) ? '?' . $query : '') : '';
		$uri .= in_array('fragment', $parts) ? (!empty($this->fragment) ? '#' . $this->fragment : '') : '';

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
	 * @return  mixed   Requested query variable if present otherwise the default value.
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
		if (is_null($this->query))
		{
			$this->query = static::buildQuery($this->vars);
		}

		return $this->query;
	}

	/**
	 * Get the URI scheme (protocol)
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
	 * Get the URI username
	 *
	 * @return  string  The username, or null if no username was specified.
	 *
	 * @since   1.0
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Get the URI password
	 *
	 * @return  string  The password, or null if no password was specified.
	 *
	 * @since   1.0
	 */
	public function getPass()
	{
		return $this->pass;
	}

	/**
	 * Get the URI host
	 *
	 * @return  string  The hostname/IP or null if no hostname/IP was specified.
	 *
	 * @since   1.0
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Get the URI port
	 *
	 * @return  integer  The port number, or null if no port was specified.
	 *
	 * @since   1.0
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * Gets the URI path string
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
		return strtolower($this->getScheme()) === 'https';
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

		if ($parts === false)
		{
			throw new \RuntimeException(sprintf('Could not parse the requested URI %s', $uri));
		}

		$retval = ($parts) ? true : false;

		// We need to replace &amp; with & for parse_str to work right...
		if (isset($parts['query']) && strpos($parts['query'], '&amp;'))
		{
			$parts['query'] = str_replace('&amp;', '&', $parts['query']);
		}

		foreach ($parts as $key => $value)
		{
			$this->$key = $value;
		}

		// Parse the query
		if (isset($parts['query']))
		{
			parse_str($parts['query'], $this->vars);
		}

		return $retval;
	}

	/**
	 * Resolves //, ../ and ./ from a path and returns the result.
	 *
	 * For example:
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

		for ($i = 0, $n = count($path); $i < $n; $i++)
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
					unset($path[$i]);
					unset($path[$i - 1]);
					$path = array_values($path);
					$i -= 2;
					$n -= 2;
				}
			}
		}

		return implode('/', $path);
	}
}
