<?php

/**
 * This is the PHP OpenID library by JanRain, Inc.
 *
 * This module contains core utility functionality used by the
 * library.  See Consumer.php and Server.php for the consumer and
 * server implementations.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */

/**
 * Require the fetcher code.
 */
require_once "Services/Yadis/PlainHTTPFetcher.php";
require_once "Services/Yadis/ParanoidHTTPFetcher.php";
require_once "Auth/OpenID/BigMath.php";

/**
 * Status code returned by the server when the only option is to show
 * an error page, since we do not have enough information to redirect
 * back to the consumer. The associated value is an error message that
 * should be displayed on an HTML error page.
 *
 * @see Auth_OpenID_Server
 */
define('Auth_OpenID_LOCAL_ERROR', 'local_error');

/**
 * Status code returned when there is an error to return in key-value
 * form to the consumer. The caller should return a 400 Bad Request
 * response with content-type text/plain and the value as the body.
 *
 * @see Auth_OpenID_Server
 */
define('Auth_OpenID_REMOTE_ERROR', 'remote_error');

/**
 * Status code returned when there is a key-value form OK response to
 * the consumer. The value associated with this code is the
 * response. The caller should return a 200 OK response with
 * content-type text/plain and the value as the body.
 *
 * @see Auth_OpenID_Server
 */
define('Auth_OpenID_REMOTE_OK', 'remote_ok');

/**
 * Status code returned when there is a redirect back to the
 * consumer. The value is the URL to redirect back to. The caller
 * should return a 302 Found redirect with a Location: header
 * containing the URL.
 *
 * @see Auth_OpenID_Server
 */
define('Auth_OpenID_REDIRECT', 'redirect');

/**
 * Status code returned when the caller needs to authenticate the
 * user. The associated value is a {@link Auth_OpenID_ServerRequest}
 * object that can be used to complete the authentication. If the user
 * has taken some authentication action, use the retry() method of the
 * {@link Auth_OpenID_ServerRequest} object to complete the request.
 *
 * @see Auth_OpenID_Server
 */
define('Auth_OpenID_DO_AUTH', 'do_auth');

/**
 * Status code returned when there were no OpenID arguments
 * passed. This code indicates that the caller should return a 200 OK
 * response and display an HTML page that says that this is an OpenID
 * server endpoint.
 *
 * @see Auth_OpenID_Server
 */
define('Auth_OpenID_DO_ABOUT', 'do_about');

/**
 * Defines for regexes and format checking.
 */
define('Auth_OpenID_letters',
       "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");

define('Auth_OpenID_digits',
       "0123456789");

define('Auth_OpenID_punct',
       "!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~");

if (Auth_OpenID_getMathLib() === null) {
    define('Auth_OpenID_NO_MATH_SUPPORT', true);
}

/**
 * The OpenID utility function class.
 *
 * @package OpenID
 * @access private
 */
class Auth_OpenID {

    /**
     * These namespaces are automatically fixed in query arguments by
     * Auth_OpenID::fixArgs.
     */
    function getOpenIDNamespaces()
    {
        return array('openid',
                     'sreg');
    }

    /**
     * Rename query arguments back to 'openid.' from 'openid_'
     *
     * @access private
     * @param array $args An associative array of URL query arguments
     */
    function fixArgs($args)
    {
        foreach (array_keys($args) as $key) {
            $fixed = $key;
            if (preg_match('/^openid/', $key)) {
                foreach (Auth_OpenID::getOpenIDNamespaces() as $ns) {
                    if (preg_match('/'.$ns.'_/', $key)) {
                        $fixed = preg_replace('/'.$ns.'_/', $ns.'.', $fixed);
                    }
                }

                if ($fixed != $key) {
                    $val = $args[$key];
                    unset($args[$key]);
                    $args[$fixed] = $val;
                }
            }
        }

        return $args;
    }

    /**
     * Create dir_name as a directory if it does not exist. If it
     * exists, make sure that it is, in fact, a directory.  Returns
     * true if the operation succeeded; false if not.
     *
     * @access private
     */
    function ensureDir($dir_name)
    {
        if (is_dir($dir_name) || @mkdir($dir_name)) {
            return true;
        } else {
            if (Auth_OpenID::ensureDir(dirname($dir_name))) {
                return is_dir($dir_name) || @mkdir($dir_name);
            } else {
                return false;
            }
        }
    }

    /**
     * Convenience function for getting array values.
     *
     * @access private
     */
    function arrayGet($arr, $key, $fallback = null)
    {
        if (is_array($arr)) {
            if (array_key_exists($key, $arr)) {
                return $arr[$key];
            } else {
                return $fallback;
            }
        } else {
            trigger_error("Auth_OpenID::arrayGet expected " .
                          "array as first parameter", E_USER_WARNING);
            return false;
        }
    }

    /**
     * Implements the PHP 5 'http_build_query' functionality.
     *
     * @access private
     * @param array $data Either an array key/value pairs or an array
     * of arrays, each of which holding two values: a key and a value,
     * sequentially.
     * @return string $result The result of url-encoding the key/value
     * pairs from $data into a URL query string
     * (e.g. "username=bob&id=56").
     */
    function httpBuildQuery($data)
    {
        $pairs = array();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $pairs[] = urlencode($value[0])."=".urlencode($value[1]);
            } else {
                $pairs[] = urlencode($key)."=".urlencode($value);
            }
        }
        return implode("&", $pairs);
    }

    /**
     * "Appends" query arguments onto a URL.  The URL may or may not
     * already have arguments (following a question mark).
     *
     * @param string $url A URL, which may or may not already have
     * arguments.
     * @param array $args Either an array key/value pairs or an array of
     * arrays, each of which holding two values: a key and a value,
     * sequentially.  If $args is an ordinary key/value array, the
     * parameters will be added to the URL in sorted alphabetical order;
     * if $args is an array of arrays, their order will be preserved.
     * @return string $url The original URL with the new parameters added.
     *
     */
    function appendArgs($url, $args)
    {
        if (count($args) == 0) {
            return $url;
        }

        // Non-empty array; if it is an array of arrays, use
        // multisort; otherwise use sort.
        if (array_key_exists(0, $args) &&
            is_array($args[0])) {
            // Do nothing here.
        } else {
            $keys = array_keys($args);
            sort($keys);
            $new_args = array();
            foreach ($keys as $key) {
                $new_args[] = array($key, $args[$key]);
            }
            $args = $new_args;
        }

        $sep = '?';
        if (strpos($url, '?') !== false) {
            $sep = '&';
        }

        return $url . $sep . Auth_OpenID::httpBuildQuery($args);
    }

    /**
     * Turn a string into an ASCII string.
     *
     * Replace non-ascii characters with a %-encoded, UTF-8
     * encoding. This function will fail if the input is a string and
     * there are non-7-bit-safe characters. It is assumed that the
     * caller will have already translated the input into a Unicode
     * character sequence, according to the encoding of the HTTP POST
     * or GET.
     *
     * Do not escape anything that is already 7-bit safe, so we do the
     * minimal transform on the identity URL
     *
     * @access private
     */
    function quoteMinimal($s)
    {
        $res = array();
        for ($i = 0; $i < strlen($s); $i++) {
            $c = $s[$i];
            if ($c >= "\x80") {
                for ($j = 0; $j < count(utf8_encode($c)); $j++) {
                    array_push($res, sprintf("%02X", ord($c[$j])));
                }
            } else {
                array_push($res, $c);
            }
        }
    
        return implode('', $res);
    }

    /**
     * Implements python's urlunparse, which is not available in PHP.
     * Given the specified components of a URL, this function rebuilds
     * and returns the URL.
     *
     * @access private
     * @param string $scheme The scheme (e.g. 'http').  Defaults to 'http'.
     * @param string $host The host.  Required.
     * @param string $port The port.
     * @param string $path The path.
     * @param string $query The query.
     * @param string $fragment The fragment.
     * @return string $url The URL resulting from assembling the
     * specified components.
     */
    function urlunparse($scheme, $host, $port = null, $path = '/',
                                    $query = '', $fragment = '')
    {

        if (!$scheme) {
            $scheme = 'http';
        }

        if (!$host) {
            return false;
        }

        if (!$path) {
            $path = '/';
        }

        $result = $scheme . "://" . $host;

        if ($port) {
            $result .= ":" . $port;
        }

        $result .= $path;

        if ($query) {
            $result .= "?" . $query;
        }

        if ($fragment) {
            $result .= "#" . $fragment;
        }

        return $result;
    }

    /**
     * Given a URL, this "normalizes" it by adding a trailing slash
     * and / or a leading http:// scheme where necessary.  Returns
     * null if the original URL is malformed and cannot be normalized.
     *
     * @access private
     * @param string $url The URL to be normalized.
     * @return mixed $new_url The URL after normalization, or null if
     * $url was malformed.
     */
    function normalizeUrl($url)
    {
        if ($url === null) {
            return null;
        }

        assert(is_string($url));

        $old_url = $url;
        $url = trim($url);

        if (strpos($url, "://") === false) {
            $url = "http://" . $url;
        }

        $parsed = @parse_url($url);

        if ($parsed === false) {
            return null;
        }

        $defaults = array(
                          'scheme' => '',
                          'host' => '',
                          'path' => '',
                          'query' => '',
                          'fragment' => '',
                          'port' => ''
                          );

        $parsed = array_merge($defaults, $parsed);

        if (($parsed['scheme'] == '') ||
            ($parsed['host'] == '')) {
            if ($parsed['path'] == '' &&
                $parsed['query'] == '' &&
                $parsed['fragment'] == '') {
                return null;
            }

            $url = 'http://' + $url;
            $parsed = parse_url($url);

            $parsed = array_merge($defaults, $parsed);
        }

        $tail = array_map(array('Auth_OpenID', 'quoteMinimal'),
                          array($parsed['path'],
                                $parsed['query'],
                                $parsed['fragment']));
        if ($tail[0] == '') {
            $tail[0] = '/';
        }

        $url = Auth_OpenID::urlunparse($parsed['scheme'], $parsed['host'],
                                       $parsed['port'], $tail[0], $tail[1],
                                       $tail[2]);

        assert(is_string($url));

        return $url;
    }
}

?>