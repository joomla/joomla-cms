<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * This module contains the CURL-based HTTP fetcher implementation.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package Yadis
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */

/**
 * Interface import
 */
require_once "Services/Yadis/HTTPFetcher.php";

/**
 * A paranoid {@link Services_Yadis_HTTPFetcher} class which uses CURL
 * for fetching.
 *
 * @package Yadis
 */
class Services_Yadis_ParanoidHTTPFetcher extends Services_Yadis_HTTPFetcher {
    function Services_Yadis_ParanoidHTTPFetcher()
    {
        $this->reset();
    }

    function reset()
    {
        $this->headers = array();
        $this->data = "";
    }

    /**
     * @access private
     */
    function _writeHeader($ch, $header)
    {
        array_push($this->headers, rtrim($header));
        return strlen($header);
    }

    /**
     * @access private
     */
    function _writeData($ch, $data)
    {
        $this->data .= $data;
        return strlen($data);
    }

    function get($url, $extra_headers = null)
    {
        $stop = time() + $this->timeout;
        $off = $this->timeout;

        $redir = true;

        while ($redir && ($off > 0)) {
            $this->reset();

            $c = curl_init();
            if (defined('CURLOPT_NOSIGNAL')) {
                curl_setopt($c, CURLOPT_NOSIGNAL, true);
            }

            if (!$this->allowedURL($url)) {
                trigger_error(sprintf("Fetching URL not allowed: %s", $url),
                              E_USER_WARNING);
                return null;
            }

            curl_setopt($c, CURLOPT_WRITEFUNCTION,
                        array(&$this, "_writeData"));
            curl_setopt($c, CURLOPT_HEADERFUNCTION,
                        array(&$this, "_writeHeader"));

            if ($extra_headers) {
                curl_setopt($c, CURLOPT_HTTPHEADER, $extra_headers);
            }

            curl_setopt($c, CURLOPT_TIMEOUT, $off);
            curl_setopt($c, CURLOPT_URL, $url);

            curl_exec($c);

            $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
            $body = $this->data;
            $headers = $this->headers;

            if (!$code) {
                return null;
            }

            if (in_array($code, array(301, 302, 303, 307))) {
                $url = $this->_findRedirect($headers);
                $redir = true;
            } else {
                $redir = false;
                curl_close($c);

                $new_headers = array();

                foreach ($headers as $header) {
                    if (preg_match("/:/", $header)) {
                        list($name, $value) = explode(": ", $header, 2);
                        $new_headers[$name] = $value;
                    }
                }

                return new Services_Yadis_HTTPResponse($url, $code,
                                                    $new_headers, $body);
            }

            $off = $stop - time();
        }

        trigger_error(sprintf("Timed out fetching: %s", $url),
                      E_USER_WARNING);

        return null;
    }

    function post($url, $body)
    {
        $this->reset();

        if (!$this->allowedURL($url)) {
            trigger_error(sprintf("Fetching URL not allowed: %s", $url),
                          E_USER_WARNING);
            return null;
        }

        $c = curl_init();

        curl_setopt($c, CURLOPT_NOSIGNAL, true);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $body);
        curl_setopt($c, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_WRITEFUNCTION,
                    array(&$this, "_writeData"));

        curl_exec($c);

        $code = curl_getinfo($c, CURLINFO_HTTP_CODE);

        if (!$code) {
            trigger_error("No HTTP code returned", E_USER_WARNING);
            return null;
        }

        $body = $this->data;

        curl_close($c);

        $new_headers = array();

        foreach ($this->headers as $header) {
            if (preg_match("/:/", $header)) {
                list($name, $value) = explode(": ", $header, 2);
                $new_headers[$name] = $value;
            }

        }

        return new Services_Yadis_HTTPResponse($url, $code,
                                               $new_headers, $body);
    }
}

?>