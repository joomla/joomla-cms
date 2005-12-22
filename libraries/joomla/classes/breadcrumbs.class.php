<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JBreadCrumbs class
 *
 * @author Louis Landry <louis@webimagery.net>
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */
class JBreadCrumbs extends JObject {

	/**
	 * Array to hold the breadcrumbs item objects
	 * @access private
	 */
	var $_breadcrumbs = null;

	/**
	 * String to hold the breadcrumbs item separator
	 * @access private
	 */
	var $_separator = null;

	/**
	 * Integer number of items in the breadcrumbs
	 * @access private
	 */
	var $_count = 0;

	/**
	 * Class constructor
	 */
	function __construct() {
	}

	/**
	 * Class destructor
	 */
	function __destruct() {
	}

	/**
	 * Set the breadcrumbs separator for the breadcrumbs object.
	 *
	 * @access public
	 * @param string $custom Custom xhtml complient string to separate the items of the breadcrumbs
	 * @return boolean True on success
	 * @since 1.1
	 */
	function setSeparator($custom = null) 
	{
		global $mainframe;

		/**
		 * If a custom separator has not been provided we try to load a template
		 * specific one first, and if that is not present we load the default separator
		 */
		if ($custom == null) {

			// Set path for what would be a template specific separator
			$tSepPath = 'templates/'.$mainframe->getTemplate().'/images/arrow.png';

			// Check to see if the template specific separator exists and if so, set it
			if (JFile :: exists(JPATH_SITE."/$tSepPath")) {
				$this->_separator = '<img src="'.JURL_SITE.'/'.$tSepPath.'" border="0" alt="arrow" />';
			} else {

				// Template specific separator does not exist, use the default separator
				$dSepPath = '/images/M_images/arrow.png';

				// Check to make sure the default separator exists
				if (JFile :: exists(JPATH_SITE.$dSepPath)) {
					$this->_separator = '<img src="'.JURL_SITE.'/images/M_images/arrow.png" alt="arrow" />';
				} else {
					// The default separator does not exist either ... just use a bracket
					$this->_separator = '&gt;';
				}
			}
		} else {
			$this->_separator = $custom;
		}
		return true;
	}

	/**
	 * Get the breadcrumbs string in XHTML format for output to the page
	 *
	 * @access public
	 * @param boolean $showHome True if the home item should be shown [Default: true]
	 * @param boolean $showComponent True if the component item should be shown [Default: true]
	 * @return string XHTML Compliant breadcrumbs string
	 * @since 1.1
	 */
	function toXHTML($showHome = true, $showComponent = true) 
	{
		// Set the default separator if not set
		if (!isset($this->_separator)) {
			$this->setSeparator();
		}

		/*
		 * Initialize variables
		 */
		$breadcrumbs = '<span class="pathway">';
		$i = null;
		$numItems = count($this->_breadcrumbs);

		for ($i = 0; $i < $numItems; $i ++) {

			// Get Link to current item
			$link = $this->_makeLink($i);

			if ($i > 1) {
				// Add the link if it exists
				if (trim($link) != '') {
					$breadcrumbs .= $link;
					// If not the last item in the breadcrumbs add the separator
					if ($i < $numItems - 1) {
						$breadcrumbs .= ' ' .$this->_separator. ' ';
					}
				}
			} elseif ($i == 1 && $showComponent == true) {
				// Add the component link if it exists and show component flag is set
				if (trim($link) != '') {
					$breadcrumbs .= $link;
					// If not the last item in the breadcrumbs add the separator
					if ($i < $numItems - 1) {
						$breadcrumbs .= ' ' .$this->_separator. ' ';
					}
				}
			} elseif ($i == 0 && $showHome == true) {
				// Add home link if it exists and show home flag is set
				if (trim($link) != '') {
					$breadcrumbs .= $link;
					// If not the last item in the breadcrumbs add the separator
					if ($i < $numItems - 1) {
						$breadcrumbs .= ' ' .$this->_separator. ' ';
					}
				}
			}
		}

		// Close the breadcrumbs span
		$breadcrumbs .= '</span>';

		return $breadcrumbs;

	}

	/**
	 * Return the JBreadCrumbs item separator string
	 *
	 * @access public
	 * @return string JBreadCrumbs item separator
	 * @since 1.1
	 */
	function getSeparator() {
		return $this->_separator;
	}

	/**
	 * Return the JBreadCrumbs items array
	 *
	 * @access public
	 * @param boolean $showHome True to show the home element of the JBreadCrumbs array
	 * @param boolean $showComponent True to show the component element of the JBreadCrumbs array
	 * @return array Array of breadcrumbs items
	 * @since 1.1
	 */
	function getBreadCrumbs($showHome = true, $showComponent = true) 
	{
		$bc = $this->_breadcrumbs;

		if ($showComponent == false) {
			unset($bc[1]);
		}
		if ($showHome == false) {
			unset($bc[0]);
		}
		
		return $bc;
	}

	/**
	 * Create and return an array of the breadcrumbs names.  Useful for things like SEF URLs
	 *
	 * @access public
	 * @param boolean $showHome True to show the home element of the JBreadCrumbs array
	 * @param boolean $showComponent True to show the component element of the JBreadCrumbs array
	 * @return array Array of names of breadcrumbs items
	 * @since 1.1
	 */
	function getNameBreadCrumbs($showHome = true, $showComponent = true) 
	{
		/*
		 * Initialize variables
		 */
		$names = array (null);

		// Build the names array using just the names of each breadcrumbs item
		foreach ($this->_breadcrumbs as $item) {
			$names[] = $item->name;
		}

		if ($showComponent == false) {
			unset($names[1]);
		}
		if ($showHome == false) {
			unset($names[0]);
		}

		return $names;
	}

	/**
	 * Create and add an item to the breadcrumbs.
	 *
	 * @access public
	 * @param string $name
	 * @param string $link
	 * @return boolean True on success
	 * @since 1.1
	 */
	function addItem($name, $link) 
	{
		// Initalize variables
		$ret = false;

		if ($this->_breadcrumbs[] = $this->_makeItem($name, $link)) {
			$ret = true;
			$this->_count++;
		}

		return $ret;
	}

	/**
	 * Set item name.
	 *
	 * @access public
	 * @param integer $id
	 * @param string $name
	 * @return boolean True on success
	 * @since 1.1
	 */
	function setItemName($id, $name) 
	{
		// Initalize variables
		$ret = false;

		if (isset($this->_breadcrumbs[$id])) {
			$this->_breadcrumbs[$id]->name = $name;
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Create and return an XTML compliant link from a breadcrumbs item.
	 *
	 * @access private
	 * @param string $id Breadcrumbs array offset of item to make a link for
	 * @return string Breadcrumbs item link
	 * @since 1.1
	 */
	function _makeLink($id) 
	{
		// Get a reference to the current working breadcrumbs item
		$item = & $this->_breadcrumbs[$id];

		// If a link is present create an html link, if not just use the name
		if (empty($item->link) || $this->_count == $id + 1 ) {
			$link = $item->name;
		} else {
			$link = '<a href="'.sefRelToAbs($item->link).'" class="pathway">'.$item->name.'</a>';
		}

		return ampReplace($link);
	}

	/**
	 * Create and return a new breadcrumbs object.
	 *
	 * @access private
	 * @param string $name Name of the item
	 * @param string $link Link to the item
	 * @return object Breadcrumbs item object
	 * @since 1.1
	 */
	function _makeItem($name, $link) 
	{
		$item = new stdClass();
		$item->name = ampReplace(html_entity_decode($name));
		$item->link = $link;

		return $item;
	}
}
?>