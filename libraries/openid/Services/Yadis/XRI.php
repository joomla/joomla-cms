<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Routines for XRI resolution.
 *
 * @package Yadis
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */

require_once 'Services/Yadis/Misc.php';
require_once 'Services/Yadis/Yadis.php';
require_once 'Auth/OpenID.php';

function Services_Yadis_getDefaultProxy()
{
    return 'http://proxy.xri.net/';
}

function Services_Yadis_getXRIAuthorities()
{
    return array('!', '=', '@', '+', '$', '(');
}

function Services_Yadis_getEscapeRE()
{
    $parts = array();
    foreach (array_merge(Services_Yadis_getUCSChars(),
                         Services_Yadis_getIPrivateChars()) as $pair) {
        list($m, $n) = $pair;
        $parts[] = sprintf("%s-%s", chr($m), chr($n));
    }

    return sprintf('/[%s]/', implode('', $parts));
}

function Services_Yadis_getXrefRE()
{
    return '/\((.*?)\)/';
}

function Services_Yadis_identifierScheme($identifier)
{
    if (Services_Yadis_startswith($identifier, 'xri://') ||
        (in_array($identifier[0], Services_Yadis_getXRIAuthorities()))) {
        return "XRI";
    } else {
        return "URI";
    }
}

function Services_Yadis_toIRINormal($xri)
{
    if (!Services_Yadis_startswith($xri, 'xri://')) {
        $xri = 'xri://' . $xri;
    }

    return Services_Yadis_escapeForIRI($xri);
}

function _escape_xref($xref_match)
{
    $xref = $xref_match[0];
    $xref = str_replace('/', '%2F', $xref);
    $xref = str_replace('?', '%3F', $xref);
    $xref = str_replace('#', '%23', $xref);
    return $xref;
}

function Services_Yadis_escapeForIRI($xri)
{
    $xri = str_replace('%', '%25', $xri);
    $xri = preg_replace_callback(Services_Yadis_getXrefRE(),
                                 '_escape_xref', $xri);
    return $xri;
}

function Services_Yadis_toURINormal($xri)
{
    return Services_Yadis_iriToURI(Services_Yadis_toIRINormal($xri));
}

function Services_Yadis_iriToURI($iri)
{
    if (1) {
        return $iri;
    } else {
        // According to RFC 3987, section 3.1, "Mapping of IRIs to URIs"
        return preg_replace_callback(Services_Yadis_getEscapeRE(),
                                     'Services_Yadis_pct_escape_unicode', $iri);
    }
}


function Services_Yadis_XRIAppendArgs($url, $args)
{
    // Append some arguments to an HTTP query.  Yes, this is just like
    // OpenID's appendArgs, but with special seasoning for XRI
    // queries.

    if (count($args) == 0) {
        return $url;
    }

    // Non-empty array; if it is an array of arrays, use multisort;
    // otherwise use sort.
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

    // According to XRI Resolution section "QXRI query parameters":
    //
    // "If the original QXRI had a null query component (only a
    //  leading question mark), or a query component consisting of
    //  only question marks, one additional leading question mark MUST
    //  be added when adding any XRI resolution parameters."
    if (strpos(rtrim($url, '?'), '?') !== false) {
        $sep = '&';
    } else {
        $sep = '?';
    }

    return $url . $sep . Auth_OpenID::httpBuildQuery($args);
}

function Services_Yadis_providerIsAuthoritative($providerID, $canonicalID)
{
    $lastbang = strrpos($canonicalID, '!');
    $p = substr($canonicalID, 0, $lastbang);
    return $p == $providerID;
}

function Services_Yadis_rootAuthority($xri)
{
    // Return the root authority for an XRI.

    $root = null;

    if (Services_Yadis_startswith($xri, 'xri://')) {
        $xri = substr($xri, 6);
    }

    $authority = explode('/', $xri, 2);
    $authority = $authority[0];
    if ($authority[0] == '(') {
        // Cross-reference.
        // XXX: This is incorrect if someone nests cross-references so
        //   there is another close-paren in there.  Hopefully nobody
        //   does that before we have a real xriparse function.
        //   Hopefully nobody does that *ever*.
        $root = substr($authority, 0, strpos($authority, ')') + 1);
    } else if (in_array($authority[0], Services_Yadis_getXRIAuthorities())) {
        // Other XRI reference.
        $root = $authority[0];
    } else {
        // IRI reference.
        $_segments = explode("!", $authority);
        $segments = array();
        foreach ($_segments as $s) {
            $segments = array_merge($segments, explode("*", $s));
        }
        $root = $segments[0];
    }

    return Services_Yadis_XRI($root);
}

function Services_Yadis_XRI($xri)
{
    if (!Services_Yadis_startswith($xri, 'xri://')) {
        $xri = 'xri://' . $xri;
    }
    return $xri;
}

function Services_Yadis_getCanonicalID($iname, $xrds)
{
    // Returns FALSE or a canonical ID value.

    // Now nodes are in reverse order.
    $xrd_list = array_reverse($xrds->allXrdNodes);
    $parser =& $xrds->parser;
    $node = $xrd_list[0];

    $canonicalID_nodes = $parser->evalXPath('xrd:CanonicalID', $node);

    if (!$canonicalID_nodes) {
        return false;
    }

    $canonicalID = $canonicalID_nodes[count($canonicalID_nodes) - 1];
    $canonicalID = Services_Yadis_XRI($parser->content($canonicalID));

    $childID = $canonicalID;

    for ($i = 1; $i < count($xrd_list); $i++) {
        $xrd = $xrd_list[$i];

        $parent_sought = substr($childID, 0, strrpos($childID, '!'));
        $parent_list = array();

        foreach ($parser->evalXPath('xrd:CanonicalID', $xrd) as $c) {
            $parent_list[] = Services_Yadis_XRI($parser->content($c));
        }

        if (!in_array($parent_sought, $parent_list)) {
            // raise XRDSFraud.
            return false;
        }

        $childID = $parent_sought;
    }

    $root = Services_Yadis_rootAuthority($iname);
    if (!Services_Yadis_providerIsAuthoritative($root, $childID)) {
        // raise XRDSFraud.
        return false;
    }

    return $canonicalID;
}

?>