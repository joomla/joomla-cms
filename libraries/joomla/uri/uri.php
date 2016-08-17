<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Uri
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Uri\Uri;

/**
 * JUri Class
 *
 * This class serves two purposes. First it parses a URI and provides a common interface
 * for the Joomla Platform to access and manipulate a URI.  Second it obtains the URI of
 * the current executing script from the server regardless of server.
 *
 * @since  11.1
 */
class JUri extends Uri
{
	/**
	 * @var    JUri[]  An array of JUri instances.
	 * @since  11.1
	 */
	protected static $instances = array();

	/**
	 * @var    array  The current calculated base url segments.
	 * @since  11.1
	 */
	protected static $base = array();

	/**
	 * @var    array  The current calculated root url segments.
	 * @since  11.1
	 */
	protected static $root = array();

	/**
	 * @var    string  The current url.
	 * @since  11.1
	 */
	protected static $current;

	/**
	 * Returns the global JUri object, only creating it if it doesn't already exist.
	 *
	 * @param   string  $uri  The URI to parse.  [optional: if null uses script URI]
	 *
	 * @return  JUri  The URI object.
	 *
	 * @since   11.1
	 */
	public static function getInstance($uri = 'SERVER')
	{
		if (empty(static::$instances[$uri]))
		{
			// Are we obtaining the URI from the server?
			if ($uri == 'SERVER')
			{
				// Determine if the request was over SSL (HTTPS).
				if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off'))
				{
					$https = 's://';
				}
				else
				{
					$https = '://';
				}

				/*
				 * Since we are assigning the URI from the server variables, we first need
				 * to determine if we are running on apache or IIS.  If PHP_SELF and REQUEST_URI
				 * are present, we will assume we are running on apache.
				 */

				if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI']))
				{
					// To build the entire URI we need to prepend the protocol, and the http host
					// to the URI string.
					$theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				}
				else
				{
					/*
					 * Since we do not have REQUEST_URI to work with, we will assume we are
					 * running on IIS and will therefore need to work some magic with the SCRIPT_NAME and
					 * QUERY_STRING environment variables.
					 *
					 * IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable... thanks, MS
					 */
					$theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

					// If the query string exists append it to the URI string
					if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']))
					{
						$theURI .= '?' . $_SERVER['QUERY_STRING'];
					}
				}

				// Extra cleanup to remove invalid chars in the URL to prevent injections through the Host header
				$theURI = str_replace(array("'", '"', '<', '>'), array("%27", "%22", "%3C", "%3E"), $theURI);
			}
			else
			{
				// We were given a URI
				$theURI = $uri;
			}

			static::$instances[$uri] = new static($theURI);
		}

		return static::$instances[$uri];
	}

	/**
	 * Returns the base URI for the request.
	 *
	 * @param   boolean  $pathonly  If false, prepend the scheme, host and port information. Default is false.
	 *
	 * @return  string  The base URI string
	 *
	 * @since   11.1
	 */
	public static function base($pathonly = false)
	{
		// Get the base request path.
		if (empty(static::$base))
		{
			$config = JFactory::getConfig();
			$uri = static::getInstance();
			$live_site = ($uri->isSsl()) ? str_replace("http://", "https://", $config->get('live_site')) : $config->get('live_site');

			if (trim($live_site) != '')
			{
				$uri = static::getInstance($live_site);
				static::$base['prefix'] = $uri->toString(array('scheme', 'host', 'port'));
				static::$base['path'] = rtrim($uri->toString(array('path')), '/\\');

				if (defined('JPATH_BASE') && defined('JPATH_ADMINISTRATOR'))
				{
					if (JPATH_BASE == JPATH_ADMINISTRATOR)
					{
						static::$base['path'] .= '/administrator';
					}
				}
			}
			else
			{
				static::$base['prefix'] = $uri->toString(array('scheme', 'host', 'port'));

				if (strpos(php_sapi_name(), 'cgi') !== false && !ini_get('cgi.fix_pathinfo') && !empty($_SERVER['REQUEST_URI']))
				{
					// PHP-CGI on Apache with "cgi.fix_pathinfo = 0"

					// We shouldn't have user-supplied PATH_INFO in PHP_SELF in this case
					// because PHP will not work with PATH_INFO at all.
					$script_name = $_SERVER['PHP_SELF'];
				}
				else
				{
					// Others
					$script_name = $_SERVER['SCRIPT_NAME'];
				}

				static::$base['path'] = rtrim(dirname($script_name), '/\\');
			}
		}

		return $pathonly === false ? static::$base['prefix'] . static::$base['path'] . '/' : static::$base['path'];
	}

	/**
	 * Returns the root URI for the request.
	 *
	 * @param   boolean  $pathonly  If false, prepend the scheme, host and port information. Default is false.
	 * @param   string   $path      The path
	 *
	 * @return  string  The root URI string.
	 *
	 * @since   11.1
	 */
	public static function root($pathonly = false, $path = null)
	{
		// Get the scheme
		if (empty(static::$root))
		{
			$uri = static::getInstance(static::base());
			static::$root['prefix'] = $uri->toString(array('scheme', 'host', 'port'));
			static::$root['path'] = rtrim($uri->toString(array('path')), '/\\');
		}

		// Get the scheme
		if (isset($path))
		{
			static::$root['path'] = $path;
		}

		return $pathonly === false ? static::$root['prefix'] . static::$root['path'] . '/' : static::$root['path'];
	}

	/**
	 * Returns the URL for the request, minus the query.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public static function current()
	{
		// Get the current URL.
		if (empty(static::$current))
		{
			$uri = static::getInstance();
			static::$current = $uri->toString(array('scheme', 'host', 'port', 'path'));
		}

		return static::$current;
	}

	/**
	 * Method to reset class static members for testing and other various issues.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public static function reset()
	{
		static::$instances = array();
		static::$base = array();
		static::$root = array();
		static::$current = '';
	}

	/**
	 * Set the URI path string. Note we keep this method here so it uses the old _cleanPath function
	 *
	 * @param   string  $path  The URI path string.
	 *
	 * @return  void
	 *
	 * @since       11.1
	 * @deprecated  4.0  Use {@link \Joomla\Uri\Uri::setPath()}
	 * @note        Present to proxy calls to the deprecated {@link JUri::_cleanPath()} method.
	 */
	public function setPath($path)
	{
		$this->path = $this->_cleanPath($path);
	}

	/**
	 * Checks if the supplied URL is internal
	 *
	 * @param   string  $url  The URL to check.
	 *
	 * @return  boolean  True if Internal.
	 *
	 * @since   11.1
	 */
	public static function isInternal($url)
	{
		$uri = static::getInstance($url);

		if ($uri->getScheme())
		{
			/*
			 * The supplied URL contains a scheme, a host and probably also a port.
			 * In order to allow URLs as internal even if they use a different scheme we have to replace the scheme etc. for the following tests accordingly
			 * if the host in the supplied URL is identical to the host in our base.
			 */
			$baseuri = static::getInstance(static::base());

			if ($uri->getHost() == $baseuri->getHost())
			{
				$uScheme = $uri->getScheme();
				$uPort = $uri->getPort();
				$bScheme = $baseuri->getScheme();
				$bPort = $baseuri->getPort();

				if ($uScheme == $bScheme)
				{
					/*
					 * The supplied URL contains the same scheme as our base URL, so the scheme needs not to be changed for the following tests.
					 * But we might have different ports given in the supplied URL and in our base URL:
					 * - supplied URL: no port, our base URL: port 80 (HTTP) or 443 (HTTPS)
					 * - supplied URL: port 80 (HTTP) or 443 (HTTPS), our base URL: no port
					 * In these two cases we have to replace the port in the supplied URL with the port from our base URL.
					 */
					if ((!$uPort && (($bScheme == 'http' && $bPort == 80) || ($bScheme == 'https' && $bPort == 443)))
						|| (!$bPort && (($uScheme == 'http' && $uPort == 80) || ($uScheme == 'https' && $uPort == 443))))
					{
						$uri->setPort($bPort);
					}
				}
				else
				{
					/*
					 * The supplied URL doesn't contain the same scheme as our base URL, so probably the scheme needs to be changed for the following tests.
					 * Whether we change the scheme or not depends upon the following conditions:
					 * - the scheme of the supplied URL is "http" and the port is either not specified or 80 and the scheme of our base URL is "https" and
					 *   the port is either not specified or 443
					 * - the scheme of the supplied URL is "https" and the port is either not specified or 443 and the scheme of our base URL is "http" and
					 *   the port is either not specified or 80
					 * In these two cases we have to replace the scheme and the port in the supplied URL with the scheme and the port from our base URL.
					 */
					if (($uScheme == 'http' && (!$uPort || $uPort == 80) && $bScheme == 'https' && (!$bPort || $bPort == 443))
						|| ($uScheme == 'https' && (!$uPort || $uPort == 443) && $bScheme == 'http' && (!$bPort || $bPort == 80)))
					{
						$uri->setScheme($bScheme);
						$uri->setPort($bPort);
					}
				}
			}
		}

		$base = $uri->toString(array('scheme', 'host', 'port', 'path'));
		$host = $uri->toString(array('scheme', 'host', 'port'));

		// @see JUriTest
		if (empty($host) && strpos($uri->path, 'index.php') === 0
			|| !empty($host) && preg_match('#' . preg_quote(static::base(), '#') . '#', $base)
			|| !empty($host) && $host === static::getInstance(static::base())->host && strpos($uri->path, 'index.php') !== false
			|| !empty($host) && $base === $host && preg_match('#' . preg_quote($base, '#') . '#', static::base()))
		{
			return true;
		}

		return false;
	}

	/**
	 * Build a query from an array (reverse of the PHP parse_str()).
	 *
	 * @param   array  $params  The array of key => value pairs to return as a query string.
	 *
	 * @return  string  The resulting query string.
	 *
	 * @see     parse_str()
	 * @since   11.1
	 * @note    The parent method is protected, this exposes it as public for B/C
	 */
	public static function buildQuery(array $params)
	{
		return parent::buildQuery($params);
	}

	/**
	 * Parse a given URI and populate the class fields.
	 *
	 * @param   string  $uri  The URI string to parse.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @note    The parent method is protected, this exposes it as public for B/C
	 */
	public function parse($uri)
	{
		return parent::parse($uri);
	}

	/**
	 * Resolves //, ../ and ./ from a path and returns
	 * the result. Eg:
	 *
	 * /foo/bar/../boo.php    => /foo/boo.php
	 * /foo/bar/../../boo.php => /boo.php
	 * /foo/bar/.././/boo.php => /foo/boo.php
	 *
	 * @param   string  $path  The URI path to clean.
	 *
	 * @return  string  Cleaned and resolved URI path.
	 *
	 * @since       11.1
	 * @deprecated  4.0   Use {@link \Joomla\Uri\Uri::cleanPath()} instead
	 */
	protected function _cleanPath($path)
	{
		return parent::cleanPath($path);
	}
}
