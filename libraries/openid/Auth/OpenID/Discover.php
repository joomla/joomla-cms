<?php

/**
 * The OpenID and Yadis discovery implementation for OpenID 1.2.
 */

require_once "Auth/OpenID.php";
require_once "Auth/OpenID/Parse.php";
require_once "Services/Yadis/XRIRes.php";
require_once "Services/Yadis/Yadis.php";

define('_OPENID_1_0_NS', 'http://openid.net/xmlns/1.0');
define('_OPENID_1_2_TYPE', 'http://openid.net/signon/1.2');
define('_OPENID_1_1_TYPE', 'http://openid.net/signon/1.1');
define('_OPENID_1_0_TYPE', 'http://openid.net/signon/1.0');

/**
 * Object representing an OpenID service endpoint.
 */
class Auth_OpenID_ServiceEndpoint {
    function Auth_OpenID_ServiceEndpoint()
    {
        $this->identity_url = null;
        $this->server_url = null;
        $this->type_uris = array();
        $this->delegate = null;
        $this->canonicalID = null;
        $this->used_yadis = false; // whether this came from an XRDS
    }

    function usesExtension($extension_uri)
    {
        return in_array($extension_uri, $this->type_uris);
    }

    function parseService($yadis_url, $uri, $type_uris, $service_element)
    {
        // Set the state of this object based on the contents of the
        // service element.
        $this->type_uris = $type_uris;
        $this->identity_url = $yadis_url;
        $this->server_url = $uri;
        $this->delegate = Auth_OpenID_ServiceEndpoint::findDelegate(
                                                         $service_element);
        $this->used_yadis = true;
    }

    function findDelegate($service)
    {
        // Extract a openid:Delegate value from a Yadis Service
        // element.  If no delegate is found, returns null.

        // Try to register new namespace.
        $service->parser->registerNamespace('openid',
                                            'http://openid.net/xmlns/1.0');

        // XXX: should this die if there is more than one delegate
        // element?
        $delegates = $service->getElements("openid:Delegate");

        if ($delegates) {
            return $service->parser->content($delegates[0]);
        } else {
            return null;
        }
    }

    function getServerID()
    {
        // Return the identifier that should be sent as the
        // openid.identity_url parameter to the server.
        if ($this->delegate === null) {
            if ($this->canonicalID) {
                return $this->canonicalID;
            } else {
                return $this->identity_url;
            }
        } else {
            return $this->delegate;
        }
    }

    function fromHTML($uri, $html)
    {
        // Parse the given document as HTML looking for an OpenID <link
        // rel=...>
        $urls = Auth_OpenID_legacy_discover($html);
        if ($urls === false) {
            return null;
        }

        list($delegate_url, $server_url) = $urls;

        $service = new Auth_OpenID_ServiceEndpoint();
        $service->identity_url = $uri;
        $service->delegate = $delegate_url;
        $service->server_url = $server_url;
        $service->type_uris = array(_OPENID_1_0_TYPE);
        return $service;
    }
}

function filter_MatchesAnyOpenIDType(&$service)
{
    $uris = $service->getTypes();

    foreach ($uris as $uri) {
        if (in_array($uri,
                     array(_OPENID_1_0_TYPE,
                           _OPENID_1_1_TYPE,
                           _OPENID_1_2_TYPE))) {
            return true;
        }
    }

    return false;
}

function Auth_OpenID_makeOpenIDEndpoints($uri, $endpoints)
{
    $s = array();

    if (!$endpoints) {
        return $s;
    }

    foreach ($endpoints as $service) {
        $type_uris = $service->getTypes();
        $uris = $service->getURIs();

        // If any Type URIs match and there is an endpoint URI
        // specified, then this is an OpenID endpoint
        if ($type_uris &&
            $uris) {

            foreach ($uris as $service_uri) {
                $openid_endpoint = new Auth_OpenID_ServiceEndpoint();
                $openid_endpoint->parseService($uri,
                                               $service_uri,
                                               $type_uris,
                                               $service);

                $s[] = $openid_endpoint;
            }
        }
    }

    return $s;
}

function Auth_OpenID_discoverWithYadis($uri, &$fetcher)
{
    // Discover OpenID services for a URI. Tries Yadis and falls back
    // on old-style <link rel='...'> discovery if Yadis fails.

    // Might raise a yadis.discover.DiscoveryFailure if no document
    // came back for that URI at all.  I don't think falling back to
    // OpenID 1.0 discovery on the same URL will help, so don't bother
    // to catch it.
    $openid_services = array();

    $http_response = null;
    $response = Services_Yadis_Yadis::discover($uri, $http_response,
                                                $fetcher);

    if ($response) {
        $identity_url = $response->uri;
        $openid_services =
            $response->xrds->services(array('filter_MatchesAnyOpenIDType'));
    }

    if (!$openid_services) {
        return @Auth_OpenID_discoverWithoutYadis($uri,
                                                 $fetcher);
    }

    if (!$openid_services) {
        $body = $response->body;

        // Try to parse the response as HTML to get OpenID 1.0/1.1
        // <link rel="...">
        $service = Auth_OpenID_ServiceEndpoint::fromHTML($identity_url,
                                                         $body);

        if ($service !== null) {
            $openid_services = array($service);
        }
    } else {
        $openid_services = Auth_OpenID_makeOpenIDEndpoints($response->uri,
                                                           $openid_services);
    }

    return array($identity_url, $openid_services, $http_response);
}

function _Auth_OpenID_discoverServiceList($uri, &$fetcher)
{
    list($url, $services, $resp) = Auth_OpenID_discoverWithYadis($uri,
                                                                 $fetcher);

    return $services;
}

function _Auth_OpenID_discoverXRIServiceList($uri, &$fetcher)
{
    list($url, $services, $resp) = _Auth_OpenID_discoverXRI($uri,
                                                            $fetcher);
    return $services;
}

function Auth_OpenID_discoverWithoutYadis($uri, &$fetcher)
{
    $http_resp = @$fetcher->get($uri);

    if ($http_resp->status != 200) {
        return array(null, array(), $http_resp);
    }

    $identity_url = $http_resp->final_url;

    // Try to parse the response as HTML to get OpenID 1.0/1.1 <link
    // rel="...">
    $endpoint =& new Auth_OpenID_ServiceEndpoint();
    $service = $endpoint->fromHTML($identity_url, $http_resp->body);
    if ($service === null) {
        $openid_services = array();
    } else {
        $openid_services = array($service);
    }

    return array($identity_url, $openid_services, $http_resp);
}

function _Auth_OpenID_discoverXRI($iname, &$fetcher)
{
    $services = new Services_Yadis_ProxyResolver($fetcher);
    list($canonicalID, $service_list) = $services->query($iname,
                                                  array(_OPENID_1_0_TYPE,
                                                        _OPENID_1_1_TYPE,
                                                        _OPENID_1_2_TYPE),
                                     array('filter_MatchesAnyOpenIDType'));

    $endpoints = Auth_OpenID_makeOpenIDEndpoints($iname, $service_list);

    for ($i = 0; $i < count($endpoints); $i++) {
        $endpoints[$i]->canonicalID = $canonicalID;
    }

    // FIXME: returned xri should probably be in some normal form
    return array($iname, $endpoints, null);
}

function Auth_OpenID_discover($uri, &$fetcher)
{
    return @Auth_OpenID_discoverWithYadis($uri, $fetcher);
}

?>