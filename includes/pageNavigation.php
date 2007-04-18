<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.html.pagination');

/**
* Legacy class, derive from {@link JPagination} instead
*
* @deprecated As of version 1.5
* @package		Joomla
* @subpackage	1.5
*/
class mosPageNav extends JPagination {

	function mosPageNav( $total, $limitstart, $limit ) {
		parent::__construct($total, $limitstart, $limit);
	}

	/**
	 * Writes the dropdown select list for number of rows to show per page
	 * Use: print $pagination->getLimitBox();
	 *
	 * @deprecated as of 1.5
	 */
	function writeLimitBox($link = null) {
		echo $this->getLimitBox();
	}

	/**
	 * Writes the counter string
	 * Use: print $pagination->getLimitBox();
	 *
	 * @deprecated as of 1.5
	 */
	function writePagesCounter() {
		echo $this->getPagesCounter();
	}

	/**
	 * Writes the page list string
	 * Use: print $pagination->getPagesLinks();
	 *
	 * @deprecated as of 1.5
	 */
	function writePagesLinks($link = null) {
		echo $this->getPagesLinks();
	}

	/**
	 * Writes the html for the leafs counter, eg, Page 1 of x
	 * Use: print $pagination->getPagesCounter();
	 *
	 * @deprecated as of 1.5
	 */
	function writeLeafsCounter() {
		echo $this->getPagesCounter();
	}

	/**
	 * Returns the pagination offset at an index
	 * Use: $pagination->getRowOffset($index); instead
	 *
	 * @deprecated as of 1.5
	 */
	function rowNumber($index) {
		return $index +1 + $this->limitstart;
	}
}
?>
