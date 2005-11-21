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
* Module database table class
* @package Joomla
* @since 1.0
*/
class mosModule extends JModel {
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $title				= null;
	/** @var string */
	var $showtitle			= null;
	/** @var int */
	var $content			= null;
	/** @var int */
	var $ordering			= null;
	/** @var string */
	var $position			= null;
	/** @var boolean */
	var $checked_out		= null;
	/** @var time */
	var $checked_out_time	= null;
	/** @var boolean */
	var $published			= null;
	/** @var string */
	var $module				= null;
	/** @var int */
	var $numnews			= null;
	/** @var int */
	var $access				= null;
	/** @var string */
	var $params				= null;
	/** @var string */
	var $iscore				= null;
	/** @var string */
	var $client_id			= null;

	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct( '#__modules', 'id', $db );
	}
	// overloaded check function
	function check() {
		;

		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = sprintf( JText::_( 'must contain a title' ), JText::_( 'Module') );
			return false;
		}

		// limitation has been removed
		// check for existing title
		//$this->_db->setQuery( "SELECT id FROM #__modules"
		//. "\nWHERE title='$this->title'"
		//);
		// check for module of same name
		//$xid = intval( $this->_db->loadResult() );
		//if ($xid && $xid != intval( $this->id )) {
		//	$this->_error = "There is a module already with that name, please try again.";
		//	return false;
		//}
		return true;
	}
}
?>
