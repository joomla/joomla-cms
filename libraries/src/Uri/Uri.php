<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Uri;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Uri Class
 *
 * This class serves two purposes. First it parses a URI and provides a common interface
 * for the Joomla Platform to access and manipulate a URI.  Second it obtains the URI of
 * the current executing script from the server regardless of server.
 *
 * @since  1.7.0
 */
class Uri extends \Joomla\Uri\Uri
{
    /**
     * @var    Uri[]  An array of Uri instances.
     * @since  1.7.0
     */
    protected static $instances = [];

    /**
     * @var    array  The current calculated base url segments.
     * @since  1.7.0
     */
    protected static $base = [];

    /**
     * @var    array  The current calculated root url segments.
     * @since  1.7.0
     */
    protected static $root = [];

    /**
     * @var    string  The current url.
     * @since  1.7.0
     */
    protected static $current;

    /**
     * Returns the global Uri object, only creating it if it doesn't already exist.
     *
     * @param   string  $uri  The URI to parse.  [optional: if null uses script URI]
     *
     * @return  Uri  The URI object.
     *
     * @since   1.7.0
     */
    public static function getInstance($uri = 'SERVER')
    {
        if (empty(static::$instances[$uri])) {
            // Are we obtaining the URI from the server?
            if ($uri === 'SERVER') {
                // Determine if the request was over SSL (HTTPS).
                if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) !== 'off')) {
                    $https = 's://';
                } elseif (
                    (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                    && !empty($_SERVER['HTTP_X_FORWARDED_PROTO'])
                    && (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) !== 'http'))
                ) {
                    $https = 's://';
                } else {
                    $https = '://';
                }

                /*
                 * Since we are assigning the URI from the server variables, we first need
                 * to determine if we are running on apache or IIS.  If PHP_SELF and REQUEST_URI
                 * are present, we will assume we are running on apache.
                 */

                if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI'])) {
                    // To build the entire URI we need to prepend the protocol, and the http host
                    // to the URI string.
                    $theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                } else {
                    /*
                     * Since we do not have REQUEST_URI to work with, we will assume we are
                     * running on IIS and will therefore need to work some magic with the SCRIPT_NAME and
                     * QUERY_STRING environment variables.
                     *
                     * IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable... thanks, MS
                     */
                    $theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

                    // If the query string exists append it to the URI string
                    if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                        $theURI .= '?' . $_SERVER['QUERY_STRING'];
                    }
                }

                // Extra cleanup to remove invalid chars in the URL to prevent injections through the Host header
                $theURI = str_replace(["'", '"', '<', '>'], ['%27', '%22', '%3C', '%3E'], $theURI);
            } else {
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
     * @since   1.7.0
     */
    public static function base($pathonly = false)
    {
        // Get the base request path.
        if (empty(static::$base)) {
            $config    = Factory::getContainer()->get('config');
            $uri       = static::getInstance();
            $live_site = ($uri->isSsl()) ? str_replace('http://', 'https://', $config->get('live_site', '')) : $config->get('live_site', '');

            if (trim($live_site) != '') {
                $uri                    = static::getInstance($live_site);
                static::$base['prefix'] = $uri->toString(['scheme', 'host', 'port']);
                static::$base['path']   = rtrim($uri->toString(['path']), '/\\');

                if (\defined('JPATH_BASE') && \defined('JPATH_ADMINISTRATOR') && JPATH_BASE == JPATH_ADMINISTRATOR) {
                    static::$base['path'] .= '/administrator';
                }

                if (\defined('JPATH_BASE') && \defined('JPATH_API') && JPATH_BASE == JPATH_API) {
                    static::$base['path'] .= '/api';
                }
            } else {
                static::$base['prefix'] = $uri->toString(['scheme', 'host', 'port']);

                if (str_contains(PHP_SAPI, 'cgi') && !\ini_get('cgi.fix_pathinfo') && !empty($_SERVER['REQUEST_URI'])) {
                    // PHP-CGI on Apache with "cgi.fix_pathinfo = 0"

                    // We shouldn't have user-supplied PATH_INFO in PHP_SELF in this case
                    // because PHP will not work with PATH_INFO at all.
                    $script_name = $_SERVER['PHP_SELF'];
                } else {
                    // Others
                    $script_name = $_SERVER['SCRIPT_NAME'];
                }

                // Extra cleanup to remove invalid chars in the URL to prevent injections through broken server implementation
                $script_name = str_replace(["'", '"', '<', '>'], ['%27', '%22', '%3C', '%3E'], $script_name);

                static::$base['path'] = rtrim(\dirname($script_name), '/\\');
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
     * @since   1.7.0
     */
    public static function root($pathonly = false, $path = null)
    {
        // Get the scheme
        if (empty(static::$root)) {
            $uri                    = static::getInstance(static::base());
            static::$root['prefix'] = $uri->toString(['scheme', 'host', 'port']);
            static::$root['path']   = rtrim($uri->toString(['path']), '/\\');
        }

        // Get the scheme
        if (isset($path)) {
            static::$root['path'] = $path;
        }

        return $pathonly === false ? static::$root['prefix'] . static::$root['path'] . '/' : static::$root['path'];
    }

    /**
     * Returns the URL for the request, minus the query.
     *
     * @return  string
     *
     * @since   1.7.0
     */
    public static function current()
    {
        // Get the current URL.
        if (empty(static::$current)) {
            $uri             = static::getInstance();
            static::$current = $uri->toString(['scheme', 'host', 'port', 'path']);
        }

        return static::$current;
    }

    /**
     * Method to reset class static members for testing and other various issues.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public static function reset()
    {
        static::$instances = [];
        static::$base      = [];
        static::$root      = [];
        static::$current   = '';
    }

    /**
     * Checks if the supplied URL is internal
     *
     * @param   string  $url  The URL to check.
     *
     * @return  boolean  True if Internal.
     *
     * @since   1.7.0
     */
    public static function isInternal($url)
    {
        $url = str_replace('\\', '/', $url);

        $uri  = static::getInstance($url);
        $base = $uri->toString(['scheme', 'host', 'port', 'path']);
        $host = $uri->toString(['scheme', 'host', 'port']);

        // @see UriTest
        if (
            empty($host) && str_starts_with($uri->path, 'index.php')
            || !empty($host) && preg_match('#^' . preg_quote(static::base(), '#') . '#', $base)
            || !empty($host) && $host === static::getInstance(static::base())->host && str_contains($uri->path, 'index.php')
            || !empty($host) && $base === $host && preg_match('#^' . preg_quote($base, '#') . '#', static::base())
        ) {
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
     * @since   1.7.0
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
     * @since   1.7.0
     * @note    The parent method is protected, this exposes it as public for B/C
     */
    public function parse($uri)
    {
        return parent::parse($uri);
    }
}
