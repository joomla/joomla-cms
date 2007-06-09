<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once 'Services/Yadis/XRDS.php';
require_once 'Services/Yadis/XRI.php';

class Services_Yadis_ProxyResolver {
    function Services_Yadis_ProxyResolver(&$fetcher, $proxy_url = null)
    {
        $this->fetcher =& $fetcher;
        $this->proxy_url = $proxy_url;
        if (!$this->proxy_url) {
            $this->proxy_url = Services_Yadis_getDefaultProxy();
        }
    }

    function queryURL($xri, $service_type = null)
    {
        // trim off the xri:// prefix
        $qxri = substr(Services_Yadis_toURINormal($xri), 6);
        $hxri = $this->proxy_url . $qxri;
        $args = array(
                      '_xrd_r' => 'application/xrds+xml'
                      );

        if ($service_type) {
            $args['_xrd_t'] = $service_type;
        } else {
            // Don't perform service endpoint selection.
            $args['_xrd_r'] .= ';sep=false';
        }

        $query = Services_Yadis_XRIAppendArgs($hxri, $args);
        return $query;
    }

    function query($xri, $service_types, $filters = array())
    {
        $services = array();
        $canonicalID = null;
        foreach ($service_types as $service_type) {
            $url = $this->queryURL($xri, $service_type);
            $response = $this->fetcher->get($url);
            if ($response->status != 200) {
                continue;
            }
            $xrds = Services_Yadis_XRDS::parseXRDS($response->body);
            if (!$xrds) {
                continue;
            }
            $canonicalID = Services_Yadis_getCanonicalID($xri,
                                                         $xrds);

            if ($canonicalID === false) {
                return null;
            }

            $some_services = $xrds->services($filters);
            $services = array_merge($services, $some_services);
            // TODO:
            //  * If we do get hits for multiple service_types, we're
            //    almost certainly going to have duplicated service
            //    entries and broken priority ordering.
        }
        return array($canonicalID, $services);
    }
}

?>