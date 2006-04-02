<?php
/**
* @version $Id: banners.class.php 3009 2006-04-02 00:46:14Z Jinx $
* @package Joomla
* @subpackage Banners
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Banners
*/
class mosBannerClient extends JTable {
	var	$cid 				= null;
	var $name 				= '';
	var $contact 			= '';
	var $email 				= '';
	var $extrainfo 			= '';
	var $checked_out 		= 0;
	var $checked_out_time 	= 0;
	var $editor				= '';

	function __construct( &$_db ) {
		parent::__construct( '#__bannerclient', 'cid', $_db );
	}

	function check() {
		// check for valid client name
		if (trim($this->name == '')) {
			$this->_error = JText::_( 'BNR_CLIENT_NAME' );
			return false;
		}

		// check for valid client contact
		if (trim($this->contact == '')) {
			$this->_error = JText::_( 'BNR_CONTACT' );
			return false;
		}

		// check for valid client email
		if ((trim($this->email == '')) || (preg_match("/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $this->email )==false)) {
			$this->_error = JText::_( 'BNR_VALID_EMAIL' );
			return false;
		}
		
		return true;
	}
}

/**
* @package Joomla
*/
class mosBanner extends JTable {
	/** @var int */
	var $bid				= null;
	/** @var int */
	var $cid				= null;
	/** @var string */
	var $type				= '';
	/** @var string */
	var $name				= '';
	/** @var int */
	var $imptotal			= 0;
	/** @var int */
	var $impmade			= 0;
	/** @var int */
	var $clicks				= 0;
	/** @var string */
	var $imageurl			= '';
	/** @var string */
	var $clickurl			= '';
	/** @var date */
	var $date				= null;
	/** @var int */
	var $showBanner			= 0;
	/** @var int */
	var $checked_out		= 0;
	/** @var date */
	var $checked_out_time	= 0;
	/** @var string */
	var $editor				= '';
	/** @var string */
	var $custombannercode	= '';

	function __construct( &$_db ) {
		parent::__construct( '#__banner', 'bid', $_db );
		$this->set( 'date', date( 'Y-m-d G:i:s' ) );
	}

	function clicks() {
		$query = "UPDATE #__banner"
		. "\n SET clicks = ( clicks + 1 )"
		. "\n WHERE bid = $this->bid"
		;
		$this->_db->setQuery( $query );
		$this->_db->query();
	}

	function check() {
		// check for valid client id
		if (is_null($this->cid) || $this->cid == 0) {
			$this->_error = JText::_( 'BNR_CLIENT' );
			return false;
		}

		if(trim($this->name) == '') {
			$this->_error = JText::_( 'BNR_NAME' );
			return false;
		}

		if(trim($this->imageurl) == '') {
			$this->_error = JText::_( 'BNR_IMAGE' );
			return false;
		}
		if(trim($this->clickurl) == '' && trim($this->custombannercode) == '') {
			$this->_error = JText::_( 'BNR_URL' );
			return false;
		}

		return true;
	}
}
?>