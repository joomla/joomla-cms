<?php
/**
* @version $Id: frontpage.class.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Content
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Content
*/
class mosFrontPage extends mosDBTable {
	/** @var int Primary key */
	var $content_id=null;
	/** @var int */
	var $ordering=null;

	/**
	* @param database A database connector object
	*/
	function mosFrontPage( &$db ) {
		$this->mosDBTable( '#__content_frontpage', 'content_id', $db );
	}
}
?>