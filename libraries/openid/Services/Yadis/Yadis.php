<?php

/**
 * The core PHP Yadis implementation.
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
 * Need both fetcher types so we can use the right one based on the
 * presence or absence of CURL.
 */
require_once "Services/Yadis/PlainHTTPFetcher.php";
require_once "Services/Yadis/ParanoidHTTPFetcher.php";

/**
 * Need this for parsing HTML (looking for META tags).
 */
require_once "Services/Yadis/ParseHTML.php";

/**
 * Need this to parse the XRDS document during Yadis discovery.
 */
require_once "Services/Yadis/XRDS.php";

/**
 * This is the core of the PHP Yadis library.  This is the only class
 * a user needs to use to perform Yadis discovery.  This class
 * performs the discovery AND stores the result of the discovery.
 *
 * First, require this library into your program source:
 *
 * <pre>  require_once "Services/Yadis/Yadis.php";</pre>
 *
 * To perform Yadis discovery, first call the "discover" method
 * statically with a URI parameter:
 *
 * <pre>  $http_response = array();
 *  $fetcher = Services_Yadis_Yadis::getHTTPFetcher();
 *  $yadis_object = Services_Yadis_Yadis::discover($uri,
 *                                    $http_response, $fetcher);</pre>
 *
 * If the discovery succeeds, $yadis_object will be an instance of
 * {@link Services_Yadis_Yadis}.  If not, it will be null.  The XRDS
 * document found during discovery should have service descriptions,
 * which can be accessed by calling
 *
 * <pre>  $service_list = $yadis_object->services();</pre>
 *
 * which returns an array of objects which describe each service.
 * These objects are instances of Services_Yadis_Service.  Each object
 * describes exactly one whole Service element, complete with all of
 * its Types and URIs (no expansion is performed).  The common use
 * case for using the service objects returned by services() is to
 * write one or more filter functions and pass those to services():
 *
 * <pre>  $service_list = $yadis_object->services(
 *                               array("filterByURI",
 *                                     "filterByExtension"));</pre>
 *
 * The filter functions (whose names appear in the array passed to
 * services()) take the following form:
 *
 * <pre>  function myFilter(&$service) {
 *       // Query $service object here.  Return true if the service
 *       // matches your query; false if not.
 *  }</pre>
 *
 * This is an example of a filter which uses a regular expression to
 * match the content of URI tags (note that the Services_Yadis_Service
 * class provides a getURIs() method which you should use instead of
 * this contrived example):
 *
 * <pre>
 *  function URIMatcher(&$service) {
 *      foreach ($service->getElements('xrd:URI') as $uri) {
 *          if (preg_match("/some_pattern/",
 *                         $service->parser->content($uri))) {
 *              return true;
 *          }
 *      }
 *      return false;
 *  }</pre>
 *
 * The filter functions you pass will be called for each service
 * object to determine which ones match the criteria your filters
 * specify.  The default behavior is that if a given service object
 * matches ANY of the filters specified in the services() call, it
 * will be returned.  You can specify that a given service object will
 * be returned ONLY if it matches ALL specified filters by changing
 * the match mode of services():
 *
 * <pre>  $yadis_object->services(array("filter1", "filter2"),
 *                          SERVICES_YADIS_MATCH_ALL);</pre>
 *
 * See {@link SERVICES_YADIS_MATCH_ALL} and {@link
 * SERVICES_YADIS_MATCH_ANY}.
 *
 * Services described in an XRDS should have a library which you'll
 * probably be using.  Those libraries are responsible for defining
 * filters that can be used with the "services()" call.  If you need
 * to write your own filter, see the documentation for {@link
 * Services_Yadis_Service}.
 *
 * @package Yadis
 */
class Services_Yadis_Yadis {

    /**
     * Returns an HTTP fetcher object.  If the CURL extension is
     * present, an instance of {@link Services_Yadis_ParanoidHTTPFetcher}
     * is returned.  If not, an instance of
     * {@link Services_Yadis_PlainHTTPFetcher} is returned.
     */
    function getHTTPFetcher($timeout = 20)
    {
        if (Services_Yadis_Yadis::curlPresent()) {
            $fetcher = new Services_Yadis_ParanoidHTTPFetcher($timeout);
        } else {
            $fetcher = new Services_Yadis_PlainHTTPFetcher($timeout);
        }
        return $fetcher;
    }

    function curlPresent()
    {
        return function_exists('curl_init');
    }

    /**
     * @access private
     */
    function _getHeader($header_list, $names)
    {
        foreach ($header_list as $name => $value) {
            foreach ($names as $n) {
                if (strtolower($name) == strtolower($n)) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * @access private
     */
    function _getContentType($content_type_header)
    {
        if ($content_type_header) {
            $parts = explode(";", $content_type_header);
            return strtolower($parts[0]);
        }
    }

    /**
     * This should be called statically and will build a Yadis
     * instance if the discovery process succeeds.  This implements
     * Yadis discovery as specified in the Yadis specification.
     *
     * @param string $uri The URI on which to perform Yadis discovery.
     *
     * @param array $http_response An array reference where the HTTP
     * response object will be stored (see {@link
     * Services_Yadis_HTTPResponse}.
     *
     * @param Services_Yadis_HTTPFetcher $fetcher An instance of a
     * Services_Yadis_HTTPFetcher subclass.
     *
     * @param array $extra_ns_map An array which maps namespace names
     * to namespace URIs to be used when parsing the Yadis XRDS
     * document.
     *
     * @param integer $timeout An optional fetcher timeout, in seconds.
     *
     * @return mixed $obj Either null or an instance of
     * Services_Yadis_Yadis, depending on whether the discovery
     * succeeded.
     */
    function discover($uri, &$http_response, &$fetcher,
                      $extra_ns_map = null, $timeout = 20)
    {
        if (!$uri) {
            return null;
        }

        $request_uri = $uri;
        $headers = array("Accept: application/xrds+xml");

        if (!$fetcher) {
            $fetcher = Services_Yadis_Yadis::getHTTPFetcher($timeout);
        }

        $response = $fetcher->get($uri, $headers);
        $http_response = $response;

        if (!$response) {
            return null;
        }

        if ($response->status != 200) {
          return null;
        }

        $xrds_uri = $response->final_url;
        $uri = $response->final_url;
        $body = $response->body;

        $xrds_header_uri = Services_Yadis_Yadis::_getHeader(
                                                    $response->headers,
                                                    array('x-xrds-location',
                                                          'x-yadis-location'));

        $content_type = Services_Yadis_Yadis::_getHeader($response->headers,
                                                         array('content-type'));

        if ($xrds_header_uri) {
            $xrds_uri = $xrds_header_uri;
            $response = $fetcher->get($xrds_uri);
            $http_response = $response;
            if (!$response) {
                return null;
            } else {
                $body = $response->body;
                $headers = $response->headers;
                $content_type = Services_Yadis_Yadis::_getHeader($headers,
                                                       array('content-type'));
            }
        }

        if (Services_Yadis_Yadis::_getContentType($content_type) !=
            'application/xrds+xml') {
            // Treat the body as HTML and look for a META tag.
            $parser = new Services_Yadis_ParseHTML();
            $new_uri = $parser->getHTTPEquiv($body);
            $xrds_uri = null;
            if ($new_uri) {
                $response = $fetcher->get($new_uri);
                if ($response->status != 200) {
                  return null;
                }
                $http_response = $response;
                $body = $response->body;
                $xrds_uri = $new_uri;
                $content_type = Services_Yadis_Yadis::_getHeader(
                                                         $response->headers,
                                                         array('content-type'));
            }
        }

        $xrds = Services_Yadis_XRDS::parseXRDS($body, $extra_ns_map);

        if ($xrds !== null) {
            $y = new Services_Yadis_Yadis();

            $y->request_uri = $request_uri;
            $y->xrds = $xrds;
            $y->uri = $uri;
            $y->xrds_uri = $xrds_uri;
            $y->body = $body;
            $y->content_type = $content_type;

            return $y;
        } else {
            return null;
        }
    }

    /**
     * Instantiates an empty Services_Yadis_Yadis object.  This
     * constructor should not be used by any user of the library.
     * This constructor results in a completely useless object which
     * must be populated with valid discovery information.  Instead of
     * using this constructor, call
     * Services_Yadis_Yadis::discover($uri).
     */
    function Services_Yadis_Yadis()
    {
        $this->request_uri = null;
        $this->uri = null;
        $this->xrds = null;
        $this->xrds_uri = null;
        $this->body = null;
        $this->content_type = null;
    }

    /**
     * Returns the list of service objects as described by the XRDS
     * document, if this yadis object represents a successful Yadis
     * discovery.
     *
     * @return array $services An array of {@link Services_Yadis_Service}
     * objects
     */
    function services()
    {
        if ($this->xrds) {
            return $this->xrds->services();
        }

        return null;
    }
}

?>