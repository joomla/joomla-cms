<?php
/**
* DOMIT! RSS Lite is a lightweight version of the DOMIT! Lite RSS parser
* @package domit-rss
* @subpackage domit-rss-lite
* @version 0.51
* @copyright (C) 2004 John Heinstein. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author John Heinstein <johnkarl@nbnet.nb.ca>
* @link http://www.engageinteractive.com/domitrss/ DOMIT! RSS Home Page
* DOMIT! RSS is Free Software
**/

if (!defined('DOMIT_RSS_INCLUDE_PATH')) {
	define('DOMIT_RSS_INCLUDE_PATH', (dirname(__FILE__) . "/"));
}

/** current version of DOMIT! RSS Lite */
define ('DOMIT_RSS_LITE_VERSION', '0.51');

require_once(DOMIT_RSS_INCLUDE_PATH . 'xml_domit_rss_shared.php');

/**
* The base DOMIT! RSS Lite document class
*
* @package domit-rss
* @subpackage domit-rss-lite
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_document_lite extends xml_domit_rss_base_document {
	/**
	* Constructor
	* @param string Path to the rss file
	* @param string Directory for cache files
	* @param int Expiration time for a cache file
	*/
	function xml_domit_rss_document_lite($url = '', $cacheDir = './', $cacheTime = 3600) {
	    $this->parser = 'DOMIT_RSS_LITE';
	    $this->xml_domit_rss_base_document($url, $cacheDir, $cacheTime);
	} //xml_domit_rss_document_lite

 	/**
	* Performs initialization of the RSS document
	*/
	function _init() {
		$total = $this->node->documentElement->childCount;
		$itemCounter = 0;
		$channelCounter = 0;

		for ($i = 0; $i < $total; $i++) {
			$currNode =& $this->node->documentElement->childNodes[$i];
			$tagName = strtolower($currNode->nodeName);

			switch ($tagName) {
				case DOMIT_RSS_ELEMENT_ITEM:
					$this->domit_rss_items[$itemCounter] = new xml_domit_rss_item_lite($currNode);
					$itemCounter++;
					break;
				case DOMIT_RSS_ELEMENT_CHANNEL:
					$this->domit_rss_channels[$channelCounter] = new xml_domit_rss_channel_lite($currNode);
					$channelCounter++;
					break;
                case DOMIT_RSS_ELEMENT_TITLE:
                case DOMIT_RSS_ELEMENT_LINK:
                case DOMIT_RSS_ELEMENT_DESCRIPTION:
                    $this->DOMIT_RSS_indexer[$tagName] = new xml_domit_rss_simpleelement($currNode);
                    break;
			}
		}

		if ($itemCounter != 0) {
			$this->DOMIT_RSS_indexer[DOMIT_RSS_ARRAY_ITEMS] =& $this->domit_rss_items;
		}

		if ($channelCounter != 0) {
			$this->DOMIT_RSS_indexer[DOMIT_RSS_ARRAY_CHANNELS] =& $this->domit_rss_channels;
		}

		$this->handleChannelElementsEmbedded();
	} //_init

	/**
	* Returns the current version of DOMIT! RSS
	* @return Object The current version of DOMIT! RSS
	*/
	function getVersion() {
		return DOMIT_RSS_LITE_VERSION;
	} //getVersion
} //xml_domit_rss_document_lite

/**
* Represents an RSS channel
*
* @package domit-rss
* @subpackage domit-rss-lite
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_channel_lite extends xml_domit_rss_elementindexer {
	/** @var array A list of references to channel items */
	var $domit_rss_items = array();

	/**
	* Constructor
	* @param Object A DOM node containing channel data
	*/
	function xml_domit_rss_channel_lite(&$channel) {
		$this->node =& $channel;
		$this->_init();
	} //xml_domit_rss_channel_lite

	/**
	* Performs initialization of the RSS channel element
	*/
	function _init() {
		$total = $this->node->childCount;
		$itemCounter = 0;

		for ($i = 0; $i < $total; $i++) {
			$currNode =& $this->node->childNodes[$i];
			$tagName = strtolower($currNode->nodeName);

			switch ($tagName) {
				case DOMIT_RSS_ELEMENT_ITEM:
					$this->domit_rss_items[$itemCounter] = new xml_domit_rss_item_lite($currNode);
					$itemCounter++;
					break;
				case DOMIT_RSS_ELEMENT_TITLE:
				case DOMIT_RSS_ELEMENT_LINK:
				case DOMIT_RSS_ELEMENT_DESCRIPTION:
					$this->DOMIT_RSS_indexer[$tagName] = new xml_domit_rss_simpleelement($currNode);
					break;
			}
		}

		if ($itemCounter != 0) {
			$this->DOMIT_RSS_indexer[DOMIT_RSS_ARRAY_ITEMS] =& $this->domit_rss_items;
		}
	} //_init

	/**
	* Returns the title of the channel
	* @return string The title of the channel, or an empty string
	*/
	function getTitle() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_TITLE);
	} //getTitle

	/**
	* Returns the url of the channel
	* @return string The url of the channel, or an empty string
	*/
	function getLink() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_LINK);
	} //getLink

	/**
	* Returns a description of the channel
	* @return string A description of the channel, or an empty string
	*/
	function getDescription() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_DESCRIPTION);
	} //getDescription

	/**
	* Returns the number of items in the channel
	* @return int The number of items in the channel
	*/
	function getItemCount() {
		return count($this->domit_rss_items);
	} //getItemCount

	/**
	* Returns a reference to the item at the specified index
	* @param int The index of the requested item
	* @return Object A reference to the item at the specified index
	*/
	function &getItem($index) {
		return $this->domit_rss_items[$index];
	} //getItem
} //xml_domit_rss_channel_lite

/**
* Represents an RSS item
*
* @package domit-rss
* @subpackage domit-rss-lite
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_item_lite extends xml_domit_rss_elementindexer {
	/**
	* Constructor
	* @param Object A DOM node containing item data
	*/
	function xml_domit_rss_item_lite(&$item) {
		$this->node =& $item;
		$this->_init();
	} //xml_domit_rss_item_lite

	/**
	* Performs initialization of the item element
	*/
	function _init(){
		$total = $this->node->childCount;

		for($i = 0; $i < $total; $i++) {
			$currNode =& $this->node->childNodes[$i];
			$tagName = strtolower($currNode->nodeName);

			switch ($tagName) {
				case DOMIT_RSS_ELEMENT_TITLE:
				case DOMIT_RSS_ELEMENT_LINK:
				case DOMIT_RSS_ELEMENT_DESCRIPTION:
					$this->DOMIT_RSS_indexer[$tagName] = new xml_domit_rss_simpleelement($currNode);
					break;
			}
		}
	} //init

	/**
	* Returns the title of the item
	* @return string The title of the item, or an empty string
	*/
	function getTitle() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_TITLE);
	} //getTitle

	/**
	* Returns the url of the item
	* @return string The url of the item, or an empty string
	*/
	function getLink() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_LINK);
	} //getLink

	/**
	* Returns a description of the item
	* @return string A description of the item, or an empty string
	*/
	function getDescription() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_DESCRIPTION);
	} //getDescription
} //xml_domit_rss_item_lite

?>