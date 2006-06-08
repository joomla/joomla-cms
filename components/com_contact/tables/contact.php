<?php
/**
 * @version $Id: contact.php 3690 2006-05-27 04:59:14Z eddieajau $
 * @package Joomla
 * @subpackage Contact
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * @package Joomla
 * @subpackage Contact
 */
class JTableContact extends JTable {
	/** @var int Primary key */
	var $id 				= null;
	/** @var string */
	var $name 				= null;
	/** @var string */
	var $con_position 		= null;
	/** @var string */
	var $address 			= null;
	/** @var string */
	var $suburb 			= null;
	/** @var string */
	var $state 				= null;
	/** @var string */
	var $country 			= null;
	/** @var string */
	var $postcode 			= null;
	/** @var string */
	var $telephone 			= null;
	/** @var string */
	var $fax 				= null;
	/** @var string */
	var $misc 				= null;
	/** @var string */
	var $image 				= null;
	/** @var string */
	var $imagepos 			= null;
	/** @var string */
	var $email_to 			= null;
	/** @var int */
	var $default_con 		= null;
	/** @var int */
	var $published 			= null;
	/** @var int */
	var $checked_out 		= null;
	/** @var datetime */
	var $checked_out_time 	= null;
	/** @var int */
	var $ordering 			= null;
	/** @var string */
	var $params 			= null;
	/** @var int A link to a registered user */
	var $user_id 			= null;
	/** @var int A link to a category */
	var $catid 				= null;
	/** @var int */
	var $access 			= null;
	/** @var string Mobile phone number(s) */
	var $mobile 			= null;
	/** @var string */
	var $webpage 			= null;

	/**
	* @param database A database connector object
	*/
	function __construct(&$db) {
		parent::__construct( '#__contact_details', 'id', $db );
	}

	function check() {
		$this->default_con = intval( $this->default_con );

		// Create specific filters
		$iFilter = new InputFilter();

		if ($iFilter->badAttributeValue(array ('href', $this->webpage))) {
			$this->_error = JText::_('Please provide a valid URL');
			return false;
		}

		// check for http on webpage
		if (!(eregi('http://', $this->webpage) || (eregi('https://', $this->webpage)) || (eregi('ftp://', $this->webpage)))) {
			$this->webpage = 'http://'.$this->webpage;
		}

		return true;
	}
}
?>