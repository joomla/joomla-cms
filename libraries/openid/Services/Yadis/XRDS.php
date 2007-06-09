<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * This module contains the XRDS parsing code.
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
 * Require the XPath implementation.
 */
require_once 'Services/Yadis/XML.php';

/**
 * This match mode means a given service must match ALL filters passed
 * to the Services_Yadis_XRDS::services() call.
 */
define('SERVICES_YADIS_MATCH_ALL', 101);

/**
 * This match mode means a given service must match ANY filters (at
 * least one) passed to the Services_Yadis_XRDS::services() call.
 */
define('SERVICES_YADIS_MATCH_ANY', 102);

/**
 * The priority value used for service elements with no priority
 * specified.
 */
define('SERVICES_YADIS_MAX_PRIORITY', pow(2, 30));

function Services_Yadis_getNSMap()
{
    return array('xrds' => 'xri://$xrds',
                 'xrd' => 'xri://$xrd*($v*2.0)');
}

/**
 * @access private
 */
function Services_Yadis_array_scramble($arr)
{
    $result = array();

    while (count($arr)) {
        $index = array_rand($arr, 1);
        $result[] = $arr[$index];
        unset($arr[$index]);
    }

    return $result;
}

/**
 * This class represents a <Service> element in an XRDS document.
 * Objects of this type are returned by
 * Services_Yadis_XRDS::services() and
 * Services_Yadis_Yadis::services().  Each object corresponds directly
 * to a <Service> element in the XRDS and supplies a
 * getElements($name) method which you should use to inspect the
 * element's contents.  See {@link Services_Yadis_Yadis} for more
 * information on the role this class plays in Yadis discovery.
 *
 * @package Yadis
 */
class Services_Yadis_Service {

    /**
     * Creates an empty service object.
     */
    function Services_Yadis_Service()
    {
        $this->element = null;
        $this->parser = null;
    }

    /**
     * Return the URIs in the "Type" elements, if any, of this Service
     * element.
     *
     * @return array $type_uris An array of Type URI strings.
     */
    function getTypes()
    {
        $t = array();
        foreach ($this->getElements('xrd:Type') as $elem) {
            $c = $this->parser->content($elem);
            if ($c) {
                $t[] = $c;
            }
        }
        return $t;
    }

    /**
     * Return the URIs in the "URI" elements, if any, of this Service
     * element.  The URIs are returned sorted in priority order.
     *
     * @return array $uris An array of URI strings.
     */
    function getURIs()
    {
        $uris = array();
        $last = array();

        foreach ($this->getElements('xrd:URI') as $elem) {
            $uri_string = $this->parser->content($elem);
            $attrs = $this->parser->attributes($elem);
            if ($attrs &&
                array_key_exists('priority', $attrs)) {
                $priority = intval($attrs['priority']);
                if (!array_key_exists($priority, $uris)) {
                    $uris[$priority] = array();
                }

                $uris[$priority][] = $uri_string;
            } else {
                $last[] = $uri_string;
            }
        }

        $keys = array_keys($uris);
        sort($keys);

        // Rebuild array of URIs.
        $result = array();
        foreach ($keys as $k) {
            $new_uris = Services_Yadis_array_scramble($uris[$k]);
            $result = array_merge($result, $new_uris);
        }

        $result = array_merge($result,
                              Services_Yadis_array_scramble($last));

        return $result;
    }

    /**
     * Returns the "priority" attribute value of this <Service>
     * element, if the attribute is present.  Returns null if not.
     *
     * @return mixed $result Null or integer, depending on whether
     * this Service element has a 'priority' attribute.
     */
    function getPriority()
    {
        $attributes = $this->parser->attributes($this->element);

        if (array_key_exists('priority', $attributes)) {
            return intval($attributes['priority']);
        }

        return null;
    }

    /**
     * Used to get XML elements from this object's <Service> element.
     *
     * This is what you should use to get all custom information out
     * of this element. This is used by service filter functions to
     * determine whether a service element contains specific tags,
     * etc.  NOTE: this only considers elements which are direct
     * children of the <Service> element for this object.
     *
     * @param string $name The name of the element to look for
     * @return array $list An array of elements with the specified
     * name which are direct children of the <Service> element.  The
     * nodes returned by this function can be passed to $this->parser
     * methods (see {@link Services_Yadis_XMLParser}).
     */
    function getElements($name)
    {
        return $this->parser->evalXPath($name, $this->element);
    }
}

/**
 * This class performs parsing of XRDS documents.
 *
 * You should not instantiate this class directly; rather, call
 * parseXRDS statically:
 *
 * <pre>  $xrds = Services_Yadis_XRDS::parseXRDS($xml_string);</pre>
 *
 * If the XRDS can be parsed and is valid, an instance of
 * Services_Yadis_XRDS will be returned.  Otherwise, null will be
 * returned.  This class is used by the Services_Yadis_Yadis::discover
 * method.
 *
 * @package Yadis
 */
class Services_Yadis_XRDS {

    /**
     * Instantiate a Services_Yadis_XRDS object.  Requires an XPath
     * instance which has been used to parse a valid XRDS document.
     */
    function Services_Yadis_XRDS(&$xmlParser, &$xrdNodes)
    {
        $this->parser =& $xmlParser;
        $this->xrdNode = $xrdNodes[count($xrdNodes) - 1];
        $this->allXrdNodes =& $xrdNodes;
        $this->serviceList = array();
        $this->_parse();
    }

    /**
     * Parse an XML string (XRDS document) and return either a
     * Services_Yadis_XRDS object or null, depending on whether the
     * XRDS XML is valid.
     *
     * @param string $xml_string An XRDS XML string.
     * @return mixed $xrds An instance of Services_Yadis_XRDS or null,
     * depending on the validity of $xml_string
     */
    function &parseXRDS($xml_string, $extra_ns_map = null)
    {
        $_null = null;

        if (!$xml_string) {
            return $_null;
        }

        $parser = Services_Yadis_getXMLParser();

        $ns_map = Services_Yadis_getNSMap();

        if ($extra_ns_map && is_array($extra_ns_map)) {
            $ns_map = array_merge($ns_map, $extra_ns_map);
        }

        if (!($parser && $parser->init($xml_string, $ns_map))) {
            return $_null;
        }

        // Try to get root element.
        $root = $parser->evalXPath('/xrds:XRDS[1]');
        if (!$root) {
            return $_null;
        }

        if (is_array($root)) {
            $root = $root[0];
        }

        $attrs = $parser->attributes($root);

        if (array_key_exists('xmlns:xrd', $attrs) &&
            $attrs['xmlns:xrd'] != 'xri://$xrd*($v*2.0)') {
            return $_null;
        } else if (array_key_exists('xmlns', $attrs) &&
                   preg_match('/xri/', $attrs['xmlns']) &&
                   $attrs['xmlns'] != 'xri://$xrd*($v*2.0)') {
            return $_null;
        }

        // Get the last XRD node.
        $xrd_nodes = $parser->evalXPath('/xrds:XRDS[1]/xrd:XRD');

        if (!$xrd_nodes) {
            return $_null;
        }

        $xrds = new Services_Yadis_XRDS($parser, $xrd_nodes);
        return $xrds;
    }

    /**
     * @access private
     */
    function _addService($priority, $service)
    {
        $priority = intval($priority);

        if (!array_key_exists($priority, $this->serviceList)) {
            $this->serviceList[$priority] = array();
        }

        $this->serviceList[$priority][] = $service;
    }

    /**
     * Creates the service list using nodes from the XRDS XML
     * document.
     *
     * @access private
     */
    function _parse()
    {
        $this->serviceList = array();

        $services = $this->parser->evalXPath('xrd:Service', $this->xrdNode);

        foreach ($services as $node) {
            $s =& new Services_Yadis_Service();
            $s->element = $node;
            $s->parser =& $this->parser;

            $priority = $s->getPriority();

            if ($priority === null) {
                $priority = SERVICES_YADIS_MAX_PRIORITY;
            }

            $this->_addService($priority, $s);
        }
    }

    /**
     * Returns a list of service objects which correspond to <Service>
     * elements in the XRDS XML document for this object.
     *
     * Optionally, an array of filter callbacks may be given to limit
     * the list of returned service objects.  Furthermore, the default
     * mode is to return all service objects which match ANY of the
     * specified filters, but $filter_mode may be
     * SERVICES_YADIS_MATCH_ALL if you want to be sure that the
     * returned services match all the given filters.  See {@link
     * Services_Yadis_Yadis} for detailed usage information on filter
     * functions.
     *
     * @param mixed $filters An array of callbacks to filter the
     * returned services, or null if all services are to be returned.
     * @param integer $filter_mode SERVICES_YADIS_MATCH_ALL or
     * SERVICES_YADIS_MATCH_ANY, depending on whether the returned
     * services should match ALL or ANY of the specified filters,
     * respectively.
     * @return mixed $services An array of {@link
     * Services_Yadis_Service} objects if $filter_mode is a valid
     * mode; null if $filter_mode is an invalid mode (i.e., not
     * SERVICES_YADIS_MATCH_ANY or SERVICES_YADIS_MATCH_ALL).
     */
    function services($filters = null,
                      $filter_mode = SERVICES_YADIS_MATCH_ANY)
    {

        $pri_keys = array_keys($this->serviceList);
        sort($pri_keys, SORT_NUMERIC);

        // If no filters are specified, return the entire service
        // list, ordered by priority.
        if (!$filters ||
            (!is_array($filters))) {

            $result = array();
            foreach ($pri_keys as $pri) {
                $result = array_merge($result, $this->serviceList[$pri]);
            }

            return $result;
        }

        // If a bad filter mode is specified, return null.
        if (!in_array($filter_mode, array(SERVICES_YADIS_MATCH_ANY,
                                          SERVICES_YADIS_MATCH_ALL))) {
            return null;
        }

        // Otherwise, use the callbacks in the filter list to
        // determine which services are returned.
        $filtered = array();

        foreach ($pri_keys as $priority_value) {
            $service_obj_list = $this->serviceList[$priority_value];

            foreach ($service_obj_list as $service) {

                $matches = 0;

                foreach ($filters as $filter) {
                    if (call_user_func_array($filter, array($service))) {
                        $matches++;

                        if ($filter_mode == SERVICES_YADIS_MATCH_ANY) {
                            $pri = $service->getPriority();
                            if ($pri === null) {
                                $pri = SERVICES_YADIS_MAX_PRIORITY;
                            }

                            if (!array_key_exists($pri, $filtered)) {
                                $filtered[$pri] = array();
                            }

                            $filtered[$pri][] = $service;
                            break;
                        }
                    }
                }

                if (($filter_mode == SERVICES_YADIS_MATCH_ALL) &&
                    ($matches == count($filters))) {

                    $pri = $service->getPriority();
                    if ($pri === null) {
                        $pri = SERVICES_YADIS_MAX_PRIORITY;
                    }

                    if (!array_key_exists($pri, $filtered)) {
                        $filtered[$pri] = array();
                    }
                    $filtered[$pri][] = $service;
                }
            }
        }

        $pri_keys = array_keys($filtered);
        sort($pri_keys, SORT_NUMERIC);

        $result = array();
        foreach ($pri_keys as $pri) {
            $result = array_merge($result, $filtered[$pri]);
        }

        return $result;
    }
}

?>