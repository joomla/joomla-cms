<?php
/**
* @package domit-rss
* @version 0.51
* @copyright (C) 2004 John Heinstein. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author John Heinstein <johnkarl@nbnet.nb.ca>
* @link http://www.engageinteractive.com/domitrss/ DOMIT! RSS Home Page
* DOMIT! RSS is Free Software
**/

/** channel constant */
define('DOMIT_RSS_ELEMENT_CHANNEL', 'channel');
/** item constant */
define('DOMIT_RSS_ELEMENT_ITEM', 'item');
/** title constant */
define('DOMIT_RSS_ELEMENT_TITLE', 'title');
/** link constant */
define('DOMIT_RSS_ELEMENT_LINK', 'link');
/** description constant */
define('DOMIT_RSS_ELEMENT_DESCRIPTION', 'description');

/** version constant */
define('DOMIT_RSS_ATTR_VERSION', 'version');

/** name of array containing list of existing RSS items */
define('DOMIT_RSS_ARRAY_ITEMS', 'item'); //formerly named 'domit_rss_items'
/** name of array containing list of existing RSS channels */
define('DOMIT_RSS_ARRAY_CHANNELS', 'channel'); //formerly named 'domit_rss_channels'
/** name of array containing list of existing RSS categories */
define('DOMIT_RSS_ARRAY_CATEGORIES', 'category'); //formerly named 'domit_rss_categories'

/** DOMIT RSS error, attempt to call an abstract method */
define('DOMIT_RSS_ABSTRACT_METHOD_INVOCATION_ERR', 101);
/** DOMIT RSS error, specified element not present */
define('DOMIT_RSS_ELEMENT_NOT_FOUND_ERR', 102);
/** DOMIT RSS error, specified attribute not present */
define('DOMIT_RSS_ATTR_NOT_FOUND_ERR', 103);
/** DOMIT RSS error, parsing failed */
define('DOMIT_RSS_PARSING_ERR', 104);

//DOMIT! RSS Error Modes
/** continue on error  */
define('DOMIT_RSS_ONERROR_CONTINUE', 1);
/** die on error  */
define('DOMIT_RSS_ONERROR_DIE', 2);
/** die on error  */
define('DOMIT_RSS_ONERROR_RETURN', 3);

/**
* The base class of all DOMIT! RSS objects
*
* @package domit-rss
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_base {
    /** @var Object The underlying DOMIT! node of the element */
	var $node = null;
	/** @var array A list of valid RSS defined child elements */
	var $rssDefinedElements = array();

	/**
	* Retrieves the underlying DOMIT node
	* @return Object The underlying DOMIT node
	*/
	function getNode() {
	    return $this->node;
	} //getNode

	/**
	* Retrieves the text of the named attribute, checking first if the attribute exists
	* @param string The attribute name
	* @return string The attribute value, or an empty string
	*/
	function getAttribute($attr) {
		if ($this->node->hasAttribute($attr)) {
			return $this->node->getAttribute($attr);
		}

		return "";
	} //getAttribute

	/**
	* Checks whether the named attribute exists
	* @param string The attribute name
	* @return boolean True if the attribute exists
	*/
	function hasAttribute($attr) {
	    return (($this->node->nodeType == DOMIT_ELEMENT_NODE) && $this->node->hasAttribute($attr));
	} //hasAttribute

	/**
	* Tests whether the named element is predefined by the RSS spec
	* @param string The element name
	* @return boolean True if the element is predefined by the RSS spec
	*/
	function isRSSDefined($elementName) {
	    $isDefined = false;

	    foreach ($this->rssDefinedElements as $key => $value) {
	        if ($elementName == $value) {
	            $isDefined = true;
	            break;
	        }
	    }

	    return $isDefined;
	} //isRSSDefined

	/**
	* Tests whether the named element has a single child text node
	* @param string The element name
	* @return boolean True if the named element has a single child text node
	*/
	function isSimpleRSSElement($elementName) {
	    $elementName = strtolower($elementName);

		if (isset($this->DOMIT_RSS_indexer[$elementName])) {
	    	return (get_class($this->getElement($elementName)) == 'xml_domit_rss_simpleelement');
		}
		else {
		    return false;
		}
	} //isSimpleRSSElement

	/**
	* Generates a string representation of the node and its children
	* @param boolean True if HTML readable output is desired
	* @param boolean True if illegal xml characters in text nodes and attributes should be converted to entities
	* @return string The string representation
	*/
    function get($htmlSafe = false, $subEntities = false) {
	    return $this->node->toString($htmlSafe, $subEntities);
	} //toString

    /**
	* Generates a normalized (formatted for readability) representation of the node and its children
	* @param boolean True if HTML readable output is desired
	* @param boolean True if illegal xml characters in text nodes and attributes should be converted to entities
	* @return string The formatted string representation
	*/
	function toNormalizedString($htmlSafe = false, $subEntities = false) {
	    return $this->node->toNormalizedString($htmlSafe, $subEntities);
	} //toNormalizedString
} //xml_domit_rss_base


/**
* Represents a collection of custom RSS elements, e.g. a set of dc:creator entries
*
* @package domit-rss
* @subpackage domit-rss-main
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_collection extends xml_domit_rss_elementindexer {
    /** @var array An array holding the collection of custom elements */
	var $elements = array();
	/** @var int The number of custom elements in the collection */
	var $elementCount = 0;

	/**
	* Adds a custom RSS element (DOM node) to the collection
	* @param Object A DOM node representing a custom RSS element
	*/
	function addElement(&$node) {
		$this->elements[] =& $node;
		$this->elementCount++;
	} //addElement

	/**
	* Retrieves the element at the specified index
	* @param int The index of the requested custom RSS element
	* @return Object The DOMIT node representing the requested element
	*/
	function &getElementAt($index) {
		return $this->elements[$index];
	} //getElementAt

	/**
	* Retrieves the element at the specified index (alias for getElementAt)
	* @param int The index of the requested custom RSS element
	* @return Object The DOMIT node representing the requested element
	*/
	function &getElement($index) {
		return $this->getElementAt($index);
	} //getElement

	/**
	* Returns the number of elements in the collection
	* @return int The number of members in the collection
	*/
	function getElementCount() {
	    return $this->elementCount;
	} //getElementCount

	/**
	* Gets a text representation of the collection (applies the toString method to each member and concatenates)
	* @return string The element text
	*/
	function getElementText() {
		$total = $this->getElementCount();
  		$result = '';

        for ($i = 0; $i < $total; $i++) {
            $result .= $currElement->toString();
        }

        return $result;
	} //getElementText
} //xml_domit_rss_collection


/**
* Provides indexing functionality to RSS classes
*
* @package domit-rss
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_elementindexer extends xml_domit_rss_base {
	/** @var Array Name based index to RSS elements */
	var $DOMIT_RSS_indexer = array();
	/** @var Array Numerical index to RSS elements; for optimization purposes, only set if requested by getElementAt */
	var $DOMIT_RSS_numericalIndexer;

	/**
	* Performs generic initialization of elements
	*/
	function _init(){
		$total = $this->node->childCount;

		for($i = 0; $i < $total; $i++) {
			$currNode =& $this->node->childNodes[$i];
			//$this->DOMIT_RSS_indexer[$currNode->nodeName] =& $currNode;
			$this->addIndexedElement($currNode);
		}
	} //_init

	/**
	* Adds a custom element (one not defined by the RSS specs, e..g., dc:creator) to the indexer
	* @param Object A DOMIT! node representing the custom element
	*/
	function addIndexedElement(&$node) {
	    $tagName = strtolower($node->nodeName);

	    if (isset($this->DOMIT_RSS_indexer[$tagName])) {
	        if (strtolower(get_class($this->DOMIT_RSS_indexer[$tagName])) == 'domit_element') {
	        	$collection = new xml_domit_rss_collection();
	        	$collection->addElement($this->DOMIT_RSS_indexer[$tagName]);
	        	$collection->addElement($node);
	        	$this->DOMIT_RSS_indexer[$tagName] =& $collection;
	        }
	        else {
				//Don't think I need this case???
	            //$this->DOMIT_RSS_indexer[$tagName]->addElement($node);
	        }
	    }
	    else {
	        $this->DOMIT_RSS_indexer[$tagName] =& $node;
	    }
	} //addIndexedElement

	/**
	* Indicates whether the requested element is actually a collection of elements of the same type
	* @param string The name of the requested element
	* @return boolean True if a collection of elements exists
	*/
	function isCollection($elementName) {
	    $elementName = strtolower($elementName);

		if (isset($this->DOMIT_RSS_indexer[$elementName])) {
			return (get_class($this->DOMIT_RSS_indexer[$elementName]) == 'xml_domit_rss_collection');
		}
		else {
			return false;
		}
	} //isCollection

	/**
	* Indicates whether the requested element is a DOMIT! node
	* @param string The name of the requested element
	* @return boolean True if the requested element is a DOMIT! node
	*/
	function isNode($elementName) {
	    $elementName = strtolower($elementName);

		if (isset($this->DOMIT_RSS_indexer[$elementName])) {
			return (strtolower(get_class($this->DOMIT_RSS_indexer[$elementName])) == 'domit_element');
		}
		else {
			return false;
		}
	} //isNode

	/**
	* Indicates whether the requested element is a DOMIT! node (alias for isNode)
	* @param string The name of the requested element
	* @return boolean True if the requested element is a DOMIT! node
	*/
	function isCustomRSSElement($elementName) {
	    return isNode($elementName);
	} //isCustomRSSElement

	/**
	* Gets a named list of existing elements as a child of the current element
	* @return array A named list of existing elements
	*/
	function getElementList() {
		return array_keys($this->DOMIT_RSS_indexer);
	} //getElementList

	/**
	* Indicates whether a particular element exists
	* @param string The name of the requested element
	* @return boolean True if an element with the specified name exists
	*/
	function hasElement($elementName) {
		return isset($this->DOMIT_RSS_indexer[strtolower($elementName)]);
	} //hasElement

	/**
	* Gets a reference to an element with the specified name
	* @param string The name of the requested element
	* @return mixed A reference to an element with the specified name, or the text of the element if it is a text node
	*/
	function &getElement($elementName) {
		$elementName = strtolower($elementName);

		if (isset($this->DOMIT_RSS_indexer[$elementName])) {
			return $this->DOMIT_RSS_indexer[$elementName];
		}
		else {
			xml_domit_rss_exception::raiseException(DOMIT_RSS_ELEMENT_NOT_FOUND_ERR,
					'Element ' . $elementName . ' not present.');
		}
	} //getElement

	/**
	* Gets a reference to an element at the specified index
	* @param int The index of the requested element
	* @return mixed A reference to an element at the specified index, or the text of the element if it is a text node
	*/
	function &getElementAt($index) {
		$this->indexNumerically();

	    if (isset($this->DOMIT_RSS_numericalIndexer[$index])) {
			return $this->DOMIT_RSS_numericalIndexer[$index];
		}
		else {
			xml_domit_rss_exception::raiseException(DOMIT_RSS_ELEMENT_NOT_FOUND_ERR,
					'Element ' . $index . ' not present.');
		}
	} //getElementAt

	/**
	* Populates an integer-based index for elements if one isn't already present.
	*/
	function indexNumerically() {
		if (!isset($this->DOMIT_RSS_numericalIndexer)) {
			$counter = 0;

            foreach ($this->DOMIT_RSS_indexer as $key => $value) {
				$this->DOMIT_RSS_numericalIndexer[$counter] =& $this->DOMIT_RSS_indexer[$key];
				$counter++;
	    	}
		}
	} //indexNumerically

	/**
	* Gets the text of the specified element
	* @param string The name of the requested element
	* @return string The element text, or an empty string
	*/
	function getElementText($elementName) {
		$elementName = strtolower($elementName);
	    return $this->_getElementText($elementName, $this->DOMIT_RSS_indexer);
	} //getElementText

	/**
	* Gets the text at the specified index
	* @param int The index of the requested element
	* @return string The element text, or an empty string
	*/
	function getElementTextAt($index) {
	    $this->indexNumerically();

	    return $this->_getElementText($index, $this->DOMIT_RSS_numericalIndexer);
	} //getElementTextAt

	/**
	* Gets the text at the specified index
	* @param mixed The index or name of the requested element
	* @param array The indexing array from which to extract data
	* @return string The element text, or an empty string
	*/
	function _getElementText($index, &$myArray) {
	    if (isset($myArray[$index])) {
			$element =& $myArray[$index];
			$result = '';

			if (is_array($element)) {
				//do nothing; data for domit_rss_channels, domit_rss_items,
				//and domit_rss_categories should be extracted with their own methods
			}
			else {
				switch (strtolower(get_class($element))) {
				    case 'xml_domit_rss_simpleelement':
				        $result = $element->getElementText();
				        break;

				    case 'xml_domit_rss_collection':
				        $result = $element->getElementText();
				        break;

					case 'domit_element':
					    $total = $element->childCount;

						for ($i = 0; $i < $total; $i++) {
							$currNode =& $element->childNodes[$i];

							if ($currNode->nodeType == DOMIT_CDATA_SECTION_NODE) {
								$result .= $currNode->nodeValue;
							}
							else {
								$result .= $currNode->toString();
							}
						}
						break;
				}
			}

			return $result;
		}

		return '';
	} //_getElementText
} //xml_domit_rss_elementindexer


/**
* A base class for DOMIT! RSS and DOMIT! RSS Lite documents
*
* @package domit-rss
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_base_document extends xml_domit_rss_elementindexer {
	/** @var array An array of item elements (only present in some RSS formats) */
	var $domit_rss_items = array();
	/** @var array An array of existing channel elements */
	var $domit_rss_channels = array();
	/** @var array An array of existing category elements */
	var $domit_rss_categories = array();
	/** @var boolean True if caching is enabled */
	var $cacheEnabled = true;
	/** @var Object A reference to the file caching object */
	var $cache;
	/** @var boolean True if PEAR:Cache_Lite is to be used instead of php_text_cache */
	var $useCacheLite = false;
	/** @var boolean True if php_http_client_generic is to be used instead of PHP get_file_contents */
	var $doUseHTTPClient = false;
	/** @var string The name of the current parser - either 'DOMIT_RSS' or 'DOMIT_RSS_LITE' */
	var $parser;
	/** @var object A reference to a http connection or proxy, if one is required */
	var $httpConnection = null;
	/** @var int The timeout value for an http connection */
	var $rssTimeout = 0;

	/**
	* Constructor
	* @param string Path to the rss file
	* @param string Directory in which cache files are to be stored
	* @param int Expiration time (in seconds) for the cache file
	* @return mixed Null if an url was not provided, true if an url was provided and parsing was successful, false otherwise
	*/
	function xml_domit_rss_base_document ($url = '', $cacheDir = './', $cacheTime = 3600) {
	    $success = null;
	    $this->createDocument();

	    if ($url != '') { //if rss data is from filesystem
			if (substr($url, 0, 4) != "http") {
				$rssText = $this->getTextFromFile($url);
				$this->parseRSS($rssText);
			}
			else {
				$this->createDefaultCache($cacheDir, $cacheTime);
				$success = $this->loadRSS($url, $cacheDir, $cacheTime);
			}
	    }

	    return $success;
	} //xml_domit_rss_base_document

	/**
	* Specifies the default timeout value for connecting to a host
	* @param int The number of seconds to timeout when attempting to connect to a server
	*/
	function setRSSTimeout($rssTimeout) {
		$this->rssTimeout = $rssTimeout;

		if (!$this->useCacheLite && !($this->cache == null)) {
			$this->cache->setTimeout($rssTimeout);
		}
	} //setRSSTimeout

	/**
	* Specifies the parameters of the http conection used to obtain the xml data
	* @param string The ip address or domain name of the connection
	* @param string The path of the connection
	* @param int The port that the connection is listening on
	* @param int The timeout value for the connection
	* @param string The user name, if authentication is required
	* @param string The password, if authentication is required
	*/
	function setConnection($host, $path = '/', $port = 80, $timeout = 0, $user = null, $password = null) {
	    require_once(DOMIT_RSS_INCLUDE_PATH . 'php_http_client_generic.php');

		$this->httpConnection = new php_http_client_generic($host, $path, $port, $timeout, $user, $password);
	} //setConnection

	/**
	* Specifies basic authentication for an http connection
	* @param string The user name
	* @param string The password
	*/
	function setAuthorization($user, $password) {
		$this->httpConnection->setAuthorization($user, $password);
	} //setAuthorization

	/**
	* Specifies that a proxy is to be used to obtain the xml data
	* @param string The ip address or domain name of the proxy
	* @param string The path to the proxy
	* @param int The port that the proxy is listening on
	* @param int The timeout value for the connection
	* @param string The user name, if authentication is required
	* @param string The password, if authentication is required
	*/
	function setProxyConnection($host, $path = '/', $port = 80, $timeout = 0, $user = null, $password = null) {
		require_once(DOMIT_RSS_INCLUDE_PATH . 'php_http_proxy.php');

		$this->httpConnection = new php_http_proxy($host, $path, $port, $timeout, $user, $password);
	} //setProxyConnection

	/**
	* Specifies a user name and password for the proxy
	* @param string The user name
	* @param string The password
	*/
	function setProxyAuthorization($user, $password) {
		$this->httpConnection->setProxyAuthorization($user, $password);
	} //setProxyAuthorization

	/**
	* Specifies whether an HTTP client should be used to establish a connection
	* @param boolean True if an HTTP client is to be used to establish the connection
	*/
	function useHTTPClient($truthVal) {
		$this->doUseHTTPClient = $truthVal;
	} //useHTTPClient

	/**
	* Returns the name of the parser
	*@return string Either 'DOMIT_RSS' or 'DOMIT_RSS_LITE'
	*/
	function parsedBy() {
	    return $this->parser;
	} //parsedBy

	/**
	* Creates an empty DOMIT! document to contain the RSS nodes
	*/
	function createDocument() {
	    require_once(DOMIT_RSS_INCLUDE_PATH . 'xml_domit_include.php');
		$this->node = new DOMIT_Document();
		$this->node->resolveErrors(true);
	} //createDocument

	/**
	* Substitutes PEAR::Cache_Lite for the default php_text_cache
	* @param boolean True if Cache Lite is to be used
	* @param string Absolute or relative path to the Cache Lite library
	* @param string Directory for cache files
	* @param int Expiration time for a cache file
	*/
	function useCacheLite($doUseCacheLite, $pathToLibrary = './Lite.php', $cacheDir = './', $cacheTime = 3600) {
		$this->useCacheLite = $doUseCacheLite;

		if ($doUseCacheLite) {
		    if (!file_exists($pathToLibrary)) {
				$this->useCacheLite(false);
		    }
		    else {
				require_once($pathToLibrary);

				$cacheOptions = array('cacheDir' => $cacheDir, 'lifeTime' => $cacheTime);
				$this->cache = new Cache_Lite($cacheOptions);
		    }
		}
		else {
		    $this->createDefaultCache($cacheDir, $cacheTime);
		}
	} //useCacheLite

	/**
	* Instantiates a default cache (php_text_cache)
	* @param string Directory for cache files
	* @param int Expiration time for a cache file
	*/
	function createDefaultCache($cacheDir = './', $cacheTime = 3600) {
	    require_once(DOMIT_RSS_INCLUDE_PATH . 'php_text_cache.php');
		$this->cache = new php_text_cache($cacheDir, $cacheTime, $this->rssTimeout);
	} //initDefaultCache

	/**
	* Disables caching mechanism
	*/
	function disableCache() {
		$this->cacheEnabled = false;
	} //initDefaultCache

	/**
	* Loads and parses the RSS at the specified url
	* @param string The url of the RSS feed
	* @return boolean True if parsing is successful
	*/
	function loadRSS($url) {
		if (substr($url, 0, 4) != "http") {
			$rssText = $this->getTextFromFile($url);
			return $this->parseRSS($rssText);
		}
		else {
		    if ($this->cacheEnabled && !isset($this->cache)) {
				$this->createDefaultCache();
				$this->cache->httpConnection =& $this->httpConnection;
		    }

			$success = $this->loadRSSData($url);

			if ($success) {
				$this->_init();
			}

			return $success;
		}
	} //loadRSS

	/**
	* Parses the RSS text provided
	* @param string The RSS text
	* @return boolean True if parsing is successful
	*/
	function parseRSS($rssText) {
	    if ($this->cacheEnabled && !isset($this->cache)) $this->createDefaultCache();
	    $success = $this->parseRSSData($rssText);

		if ($success) {
			$this->_init();
		}

		return $success;
	} //parseRSS

	/**
	* Retrieves the RSS data from the url/cache file and parses
	* @param string The url for the RSS data
	* @return boolean True if parsing is successful
	*/
	function loadRSSData($url) {
		$rssText = $this->getDataFromCache($url);
		return $this->parseRSSData($rssText);
	} //loadRSSData

	/**
	* Retrieves the RSS data from the url/cache file
	* @param string The url for the RSS data
	* @return string The RSS data
	*/
	function getDataFromCache($url) {
		if ($this->cacheEnabled) {
	    	if ($this->useCacheLite) {
	        	if ($rssText = $this->cache->get($url)) {
	            	return $rssText;
	       		}
	        	else {
	            	$rssText = $this->getTextFromFile($url);
					if ($rssText != '') $this->cache->save($rssText, $url);
	            	return $rssText;
	        	}
	    	}
	    	else {
				$this->cache->useHTTPClient($this->doUseHTTPClient);
	        	return $this->cache->getData($url);
	    	}
		}
		else {
			return $this->getTextFromFile($url);
		}
	} //getDataFromCache

	/**
	* Parses the RSS data provided
	* @param string The the RSS data
	* @return boolean True if parsing is successful
	*/
	function parseRSSData($rssText) {
	    if ($rssText != '') {
			return $this->fromString($rssText);
		}
		else {
			return false;
		}
	} //parseRSSData

	/**
	* Reads in RSS text from a file and parses it
	* @param string The file path
	* @return boolean True if parsing is successful
	*/
	function &fromFile($filename) {
		$success = $this->node->loadXML($filename, false);
		return $success;
	} //fromFile

	/**
	* Reads in RSS text from a string and parses it
	* @param string The RSS text
	* @return boolean True if parsing is successful
	*/
	function &fromString($rssText) {
		$success = $this->node->parseXML($rssText, false);
		return $success;
	} //fromString

	/**
	* Establishes a connection, given an url
	* @param string The url of the data
	*/
	function establishConnection($url) {
		require_once(DOMIT_RSS_INCLUDE_PATH . 'php_http_client_generic.php');

		$host = php_http_connection::formatHost($url);
		$host = substr($host, 0, strpos($host, '/'));

		$this->setConnection($host, '/', 80, $this->rssTimeout);
	} //establishConnection

	/**
	* Get text from an url or file
	* @param string The url or file path
	* @return string The text contained in the url or file, or an empty string
	*/
	function getTextFromFile($filename) {
		$fileContents = '';

		if ($this->doUseHTTPClient) {
			$this->establishConnection($filename);
			$response =& $this->httpConnection->get($filename);

			if ($response != null) {
				$fileContents = $response->getResponse();
			}
		}
		else {
			if (function_exists('file_get_contents')) {
				$fileContents = @file_get_contents($filename);
			}
			else {
				require_once(DOMIT_RSS_INCLUDE_PATH . 'php_file_utilities.php');
				$fileContents =& php_file_utilities::getDataFromFile($filename, 'r');
			}

			if ($fileContents == '') {
				$this->establishConnection($filename);
				$response =& $this->httpConnection->get($filename);

				if ($response != null) {
					$fileContents = $response->getResponse();
				}
			}
		}

		return $fileContents;
	} //getTextFromFile

	/**
	* Gets a reference to the underlying DOM document
	* @return Object A reference to the underlying DOM document
	*/
	function &getDocument() {
		return $this->node;
	} //getDocument

	/**
	* Gets a reference to the root DOM element
	* @return Object A reference to the root DOM element
	*/
	function &getNode() {
		return $this->node->documentElement;
	} //getNode

	/**
	* Forces channel elements that are external to a channel to be referenced as subelements of that channel
	*/
	function handleChannelElementsEmbedded() {
		if (count($this->domit_rss_items) > 0) {
			foreach ($this->domit_rss_channels as $key => $value) {
				$this->domit_rss_channels[$key]->domit_rss_items =& $this->domit_rss_items;

				if (count($this->DOMIT_RSS_indexer) > 0) {
					foreach ($this->DOMIT_RSS_indexer as $ikey => $ivalue) {
						if ($ikey != DOMIT_RSS_ARRAY_CHANNELS) {
							$this->domit_rss_channels[$key]->DOMIT_RSS_indexer[$ikey] =& $this->DOMIT_RSS_indexer[$ikey];
							unset($this->DOMIT_RSS_indexer[$ikey]);
						}
					}
				}
			}
		}
	} //handleChannelElementsEmbedded

	/**
	* Returns the version of RSS used to format the data
	* @return string The version of RSS used to format the data
	*/
	function getRSSVersion() {
		$version = $this->node->documentElement->getAttribute(DOMIT_RSS_ATTR_VERSION);

		if ($version == '') {
			$xmlns = $this->node->documentElement->getAttribute('xmlns');
			$total = strlen($xmlns);

			if (substr($xmlns, $total) == '/') {
			    $total--;
			}

			for ($i = ($total - 1); $i > -1; $i--) {
			    $currentChar = substr($xmlns, $i);

			    if ($currentChar == '/') {
			        break;
			    }
			    else {
			        $version = $currentChar . $version;
			    }
			}

		}

		return $version;
	} //getRSSVersion

	/**
	* Returns the number of channels in the document
	* @return int The number of channels in the document
	*/
	function getChannelCount() {
		return count($this->domit_rss_channels);
	} //getChannelCount()

	/**
	* Returns a reference to the channel located at the specified index
	* @return Object A reference to the channel located at the specified index
	*/
	function &getChannel($index) {
		return $this->domit_rss_channels[$index];
	} //getChannel
} //xml_domit_rss_base_document

/**
* Represents a simple RSS element, without attributes and only a single child text node
*
* @package domit-rss
* @subpackage domit-rss-main
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_simpleelement extends xml_domit_rss_elementindexer {
	/**
	* Constructor
	* @param Object A DOM node containing element data
	*/
	function xml_domit_rss_simpleelement(&$element) {
		$this->node =& $element;
	} //xml_domit_rss_simpleelement

	/**
	* Gets the text of the element
	* @return string The element text
	*/
	function getElementText() {
	    $element =& $this->node;
	    $result = '';
    	$total = $element->childCount;

		for ($i = 0; $i < $total; $i++) {
			$currNode =& $element->childNodes[$i];

			if ($currNode->nodeType == DOMIT_CDATA_SECTION_NODE) {
				$result .= $currNode->nodeValue;
			}
			else {
				$result .= $currNode->toString();
			}
	    }

        return $result;
	} //getElementText
} //xml_domit_rss_simpleelement

/**
* @global object Reference to custom error handler for DOMIT RSS Exception class
*/
$GLOBALS['DOMIT_RSS_Exception_errorHandler'] = null;
/**
* @global int Error mode; specifies whether to die on error or simply return
*/
//$GLOBALS['DOMIT_RSS_Exception_mode'] = DOMIT_RSS_ONERROR_RETURN;
// fixes bug identified here: sarahk.pcpropertymanager.com/blog/using-domit-rss/225/
$GLOBALS['DOMIT_RSS_Exception_mode'] = 1;
/**
* @global string Log file for errors
*/
$GLOBALS['DOMIT_RSS_Exception_log'] = null;


/**
* A DOMIT! RSS exception handling class
*
* @package domit-rss
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_exception {
	/**
	* Raises the specified exception
	* @param int The error number
	* @param string A string explanation of the error
	*/
	function raiseException($errorNum, $errorString) {
		if ($GLOBALS['DOMIT_RSS_Exception_errorHandler'] != null) {
			call_user_func($GLOBALS['DOMIT_RSS_Exception_errorHandler'], $errorNum, $errorString);
		}
		else {
			$errorMessageText = $errorNum  . ' ' . $errorString;
			$errorMessage = 'Error: ' . $errorMessageText;

			if ((!isset($GLOBALS['DOMIT_RSS_ERROR_FORMATTING_HTML'])) ||
				($GLOBALS['DOMIT_RSS_ERROR_FORMATTING_HTML'] == true)) {
					$errorMessage = "<p><pre>" . $errorMessage . "</pre></p>";
			}

			//log error to file
			if ((isset($GLOBALS['DOMIT_RSS_Exception_log'])) &&
				($GLOBALS['DOMIT_RSS_Exception_log'] != null)) {
					require_once(DOMIT_RSS_INCLUDE_PATH . 'php_file_utilities.php');

					$logItem = "\n" . date('Y-m-d H:i:s') . 'DOMIT! RSS Error ' . $errorMessageText;
					php_file_utilities::putDataToFile($GLOBALS['DOMIT_RSS_Exception_log'], $logItem, 'a');
			}

			switch ($GLOBALS['DOMIT_RSS_Exception_mode']) {
				case DOMIT_RSS_ONERROR_CONTINUE:
					return;
					break;

				case DOMIT_RSS_ONERROR_DIE:
					die($errorMessage);
					break;
			}
		}
	} //raiseException

	/**
	* custom handler for DOM RSS errors
	* @param object A reference to the custom error handler
	*/
	function setErrorHandler($method) {
		$GLOBALS['DOMIT_RSS_Exception_errorHandler'] =& $method;

		require_once(DOMIT_RSS_INCLUDE_PATH . 'php_http_exceptions.php');
		$GLOBALS['HTTP_Exception_errorHandler'] =& $method;

		require_once(DOMIT_RSS_INCLUDE_PATH . 'xml_domit_shared.php');
		$GLOBALS['HTTP_Exception_errorHandler'] =& $method;
	} //setErrorHandler

	/**
	* Set error mode
	* @param int The DOMIT RSS error mode
	*/
	function setErrorMode($mode) {
		$GLOBALS['DOMIT_RSS_Exception_mode'] = $mode;

		require_once(DOMIT_RSS_INCLUDE_PATH . 'php_http_exceptions.php');
		require_once(DOMIT_RSS_INCLUDE_PATH . 'xml_domit_shared.php');

		if ($mode == DOMIT_RSS_ONERROR_CONTINUE) {
			$GLOBALS['HTTP_Exception_mode'] = HTTP_ONERROR_CONTINUE;
			$GLOBALS['DOMIT_DOMException_mode'] = DOMIT_ONERROR_CONTINUE;
		}
		else {
			$GLOBALS['HTTP_Exception_mode'] = HTTP_ONERROR_DIE;
			$GLOBALS['DOMIT_DOMException_mode'] = DOMIT_ONERROR_DIE;
		}
	} //setErrorMode

	/**
	* Set error mode
	* @param boolean True if errors should be logged
	* @param string Absolute or relative path to log file
	*/
	function setErrorLog($doLogErrors, $logfile) {
		require_once(DOMIT_RSS_INCLUDE_PATH . 'php_http_exceptions.php');

		if ($doLogErrors) {
			$GLOBALS['DOMIT_RSS_Exception_log'] = $logfile;
			$GLOBALS['HTTP_Exception_log'] = $logfile;
			$GLOBALS['DOMIT_Exception_log'] = $logfile;
		}
		else {
			$GLOBALS['DOMIT_RSS_Exception_log'] = null;
			$GLOBALS['HTTP_Exception_log'] = null;
			$GLOBALS['DOMIT_Exception_log'] = null;
		}
	} //setErrorLog
} //xml_domit_rss_exception

?>