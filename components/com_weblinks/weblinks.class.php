<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
* JWeblink Model class
*
* @package Joomla
* @subpackage Weblinks
* @since 1.0
*/
class JWeblinkModel extends JModel {
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;

	/**
	 * @var int
	 */
	var $catid = null;

	/**
	 * @var int
	 */
	var $sid = null;

	/**
	 * @var string
	 */
	var $title = null;

	/**
	 * @var string
	 */
	var $url = null;

	/**
	 * @var string
	 */
	var $description = null;

	/**
	 * @var datetime
	 */
	var $date = null;

	/**
	 * @var int
	 */
	var $hits = null;

	/**
	 * @var int
	 */
	var $published = null;

	/**
	 * @var boolean
	 */
	var $checked_out = null;

	/**
	 * @var time
	 */
	var $checked_out_time = null;

	/**
	 * @var int
	 */
	var $ordering = null;

	/**
	 * @var int
	 */
	var $archived = null;

	/**
	 * @var int
	 */
	var $approved = null;

	/**
	 * @var string
	 */
	var $params = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function JWeblinkModel(& $db) {
		parent::__construct('#__weblinks', 'id', $db);
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.0
	 */
	function check() {

		// Filter malicious code
		$ignoreList = array ('params');
		$this->filter($ignoreList);

		// Create specific filters
		$iFilter = new InputFilter();

		if ($iFilter->badAttributeValue(array ('href', $this->url))) {
			$this->_error = JText::_('Please provide a valid URL');
			return false;
		}

		/** check for valid name */
		if (trim($this->title) == '') {
			$this->_error = JText::_('Your Weblink must contain a title.');
			return false;
		}

		if (!(eregi('http://', $this->url) || (eregi('https://', $this->url)) || (eregi('ftp://', $this->url)))) {
			$this->url = 'http://'.$this->url;
		}

		/** check for existing name */
		$query = "SELECT id"."\n FROM #__weblinks "."\n WHERE title = '$this->title'"."\n AND catid = $this->catid";
		$this->_db->setQuery($query);

		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			$this->_error = sprintf(JText::_('WARNNAMETRYAGAIN'), JText::_('Web Link'));
			return false;
		}
		return true;
	}
}
?>
