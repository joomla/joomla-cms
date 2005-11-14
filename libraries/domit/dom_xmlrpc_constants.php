<?php
/**
* Constants for DOM XML-RPC
* @package dom-xmlrpc
* @copyright (C) 2004 John Heinstein. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author John Heinstein <johnkarl@nbnet.nb.ca>
* @link http://www.engageinteractive.com/dom_xmlrpc/ DOM XML-RPC Home Page
* DOM XML-RPC is Free Software
**/

/** An XML-RPC item  tag*/
define('DOM_XMLRPC_TYPE_ITEM', 'item');
/** An XML-RPC int tag */
define('DOM_XMLRPC_TYPE_INT', 'int');
/** An  alternate XML-RPC int tag */
define('DOM_XMLRPC_TYPE_I4', 'i4');
/** An XML-RPC double tag */
define('DOM_XMLRPC_TYPE_DOUBLE', 'double');
/** An XML-RPC boolean tag */
define('DOM_XMLRPC_TYPE_BOOLEAN', 'boolean');
/** An XML-RPC string tag */
define('DOM_XMLRPC_TYPE_STRING', 'string');
/** An XML-RPC (iso8601) date-time tag */
define('DOM_XMLRPC_TYPE_DATETIME', 'dateTime.iso8601');
/** An XML-RPC base63 encoded value tag */
define('DOM_XMLRPC_TYPE_BASE64', 'base64');
/** An XML-RPC method call tag */
define('DOM_XMLRPC_TYPE_METHODCALL', 'methodCall');
/** An XML-RPC method name tag */
define('DOM_XMLRPC_TYPE_METHODNAME', 'methodName');
/** An XML-RPC params tag */
define('DOM_XMLRPC_TYPE_PARAMS', 'params');
/** An XML-RPC param tag */
define('DOM_XMLRPC_TYPE_PARAM', 'param');
/** An XML-RPC value tag */
define('DOM_XMLRPC_TYPE_VALUE', 'value');
/** An XML-RPC struct tag */
define('DOM_XMLRPC_TYPE_STRUCT', 'struct');
/** An XML-RPC member tag */
define('DOM_XMLRPC_TYPE_MEMBER', 'member');
/** An XML-RPC name tag */
define('DOM_XMLRPC_TYPE_NAME', 'name');
/** An XML-RPC array tag */
define('DOM_XMLRPC_TYPE_ARRAY', 'array');
/** An XML-RPC data tag */
define('DOM_XMLRPC_TYPE_DATA', 'data');
/** An XML-RPC method response tag */
define('DOM_XMLRPC_TYPE_METHODRESPONSE', 'methodResponse');
/** An XML-RPC fault tag */
define('DOM_XMLRPC_TYPE_FAULT', 'fault');

/** An XML-RPC scalar */
define('DOM_XMLRPC_TYPE_SCALAR', 'scalar'); //not part of spec but used as a catch-all

/** An XML-RPC faultCode identifier */
define('DOM_XMLRPC_NODEVALUE_FAULTCODE', 'faultCode');
/** An XML-RPC faultString identifier */
define('DOM_XMLRPC_NODEVALUE_FAULTSTRING', 'faultString');
/** An XML-RPC faultString identifier */
define('DOM_XMLRPC_RESPONSE_TYPE_STRING', 'string');
/** A DOM XML-RPC array identifier */
define('DOM_XMLRPC_RESPONSE_TYPE_ARRAY', 'array');
/** A DOM XML-RPC DOMIT! document */
define('DOM_XMLRPC_RESPONSE_TYPE_XML_DOMIT', 'xml_domit');
/** A DOM XML-RPC DOMIT! Lite document */
define('DOM_XMLRPC_RESPONSE_TYPE_XML_DOMIT_LITE', 'xml_domit_lite');
/** A DOM XML-RPC DOM-XML! document */
define('DOM_XMLRPC_RESPONSE_TYPE_XML_DOMXML', 'xml_domxml');

/** Anonymous object marshalling */
define('DOM_XMLRPC_OBJECT_MARSHALLING_ANONYMOUS', 'anonymous');
/** Named object marshalling */
define('DOM_XMLRPC_OBJECT_MARSHALLING_NAMED', 'named');
/** Serialized object marshalling */
define('DOM_XMLRPC_OBJECT_MARSHALLING_SERIALIZED', 'serialized');
/** Unserialized PHP Object marshalling */
define('DOM_XMLRPC_PHPOBJECT', '__phpobject__');
/** Sertialized PHP Object marshalling */
define('DOM_XMLRPC_SERIALIZED', '__serialized__');
?>