<?php

/**
 * URI normalization routines.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */

require_once 'Services/Yadis/Misc.php';

// from appendix B of rfc 3986 (http://www.ietf.org/rfc/rfc3986.txt)
$__uri_pattern = '&^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?&';
$__authority_pattern = '/^([^@]*@)?([^:]*)(:.*)?/';
$__pct_encoded_pattern = '/%([0-9A-Fa-f]{2})/';

$_unreserved = array();
for ($i = 0; $i < 256; $i++) {
    $_unreserved[$i] = false;
}

for ($i = ord('A'); $i <= ord('Z'); $i++) {
    $_unreserved[$i] = true;
}

for ($i = ord('0'); $i <= ord('9'); $i++) {
    $_unreserved[$i] = true;
}

for ($i = ord('a'); $i <= ord('z'); $i++) {
    $_unreserved[$i] = true;
}

$_unreserved[ord('-')] = true;
$_unreserved[ord('.')] = true;
$_unreserved[ord('_')] = true;
$_unreserved[ord('~')] = true;

$parts = array();
foreach (array_merge($__UCSCHAR, $__IPRIVATE) as $pair) {
    list($m, $n) = $pair;
    $parts[] = sprintf("%s-%s", chr($m), chr($n));
}

$_escapeme_re = sprintf('[%s]', implode('', $parts));

function _pct_encoded_replace_unreserved($mo)
{
    global $_unreserved;

    $i = intval($mo[1], 16);
    if ($_unreserved[$i]) {
        return chr($i);
    } else {
        return strtoupper($mo[0]);
    }

    return $mo[0];
}

function _pct_encoded_replace($mo)
{
    return chr(intval($mo[1], 16));
}

function remove_dot_segments($path)
{
    $result_segments = array();
    
    while ($path) {
        if (_startswith($path, '../')) {
            $path = substr($path, 3);
        } else if (_startswith($path, './')) {
            $path = substr($path, 2);
        } else if (_startswith($path, '/./')) {
            $path = substr($path, 2);
        } else if ($path == '/.') {
            $path = '/';
        } else if (_startswith($path, '/../')) {
            $path = substr($path, 3);
            if ($result_segments) {
                array_pop($result_segments);
            }
        } else if ($path == '/..') {
            $path = '/';
            if ($result_segments) {
                array_pop($result_segments);
            }
        } else if (($path == '..') ||
                   ($path == '.')) {
            $path = '';
        } else {
            $i = 0;
            if ($path[0] == '/') {
                $i = 1;
            }
            $i = strpos($path, '/', $i);
            if ($i === false) {
                $i = strlen($path);
            }
            $result_segments[] = substr($path, 0, $i);
            $path = substr($path, $i);
        }
    }

    return implode('', $result_segments);
}

function Auth_OpenID_urinorm($uri)
{
    global $__uri_pattern, $__authority_pattern, $__pct_encoded_pattern;

    $uri_matches = array();
    preg_match($__uri_pattern, $uri, $uri_matches);

    if (count($uri_matches) < 9) {
        for ($i = count($uri_matches); $i <= 9; $i++) {
            $uri_matches[] = '';
        }
    }

    $scheme = $uri_matches[2];
    if ($scheme) {
        $scheme = strtolower($scheme);
    }

    $scheme = $uri_matches[2];
    if ($scheme === '') {
        // No scheme specified
        return null;
    }

    $scheme = strtolower($scheme);
    if (!in_array($scheme, array('http', 'https'))) {
        // Not an absolute HTTP or HTTPS URI
        return null;
    }

    $authority = $uri_matches[4];
    if ($authority === '') {
        // Not an absolute URI
        return null;
    }

    $authority_matches = array();
    preg_match($__authority_pattern, $authority, $authority_matches);
    if (count($authority_matches) === 0) {
        // URI does not have a valid authority
        return null;
    }

    if (count($authority_matches) < 4) {
        for ($i = count($authority_matches); $i <= 4; $i++) {
            $authority_matches[] = '';
        }
    }

    list($_whole, $userinfo, $host, $port) = $authority_matches;

    if ($userinfo === null) {
        $userinfo = '';
    }

    if (strpos($host, '%') !== -1) {
        $host = strtolower($host);
        $host = preg_replace_callback(
                  $__pct_encoded_pattern, '_pct_encoded_replace', $host);
        // NO IDNA.
        // $host = unicode($host, 'utf-8').encode('idna');
    } else {
        $host = strtolower($host);
    }

    if ($port) {
        if (($port == ':') ||
            ($scheme == 'http' && $port == ':80') ||
            ($scheme == 'https' && $port == ':443')) {
            $port = '';
        }
    } else {
        $port = '';
    }

    $authority = $userinfo . $host . $port;

    $path = $uri_matches[5];
    $path = preg_replace_callback(
               $__pct_encoded_pattern,
               '_pct_encoded_replace_unreserved', $path);

    $path = remove_dot_segments($path);
    if (!$path) {
        $path = '/';
    }

    $query = $uri_matches[6];
    if ($query === null) {
        $query = '';
    }

    $fragment = $uri_matches[8];
    if ($fragment === null) {
        $fragment = '';
    }

    return $scheme . '://' . $authority . $path . $query . $fragment;
}

?>
