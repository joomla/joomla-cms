<?php

/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Category table
 *
 * @package 	Joomla.Framework
 * @subpackage 	Model
 * @since		1.0
 */
class JTableCategory extends JTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $parent_id			= null;
	/** @var string The menu title for the Category (a short name)*/
	var $title				= null;
	/** @var string The full name for the Category*/
	var $name				= null;
	/** @var string */
	var $image				= null;
	/** @var string */
	var $section			= null;
	/** @var int */
	var $image_position		= null;
	/** @var string */
	var $description		= null;
	/** @var boolean */
	var $published			= null;
	/** @var boolean */
	var $checked_out		= null;
	/** @var time */
	var $checked_out_time	= null;
	/** @var int */
	var $ordering			= null;
	/** @var int */
	var $access				= null;
	/** @var string */
	var $params				= null;

	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct( '#__categories', 'id', $db );
	}

	// overloaded check function
	function check()
	{
		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = sprintf( JText::_( 'must contain a title' ), JText::_( 'Category') );
			return false;
		}
		if (trim( $this->name ) == '') {
			$this->_error = sprintf( JText::_( 'must have a name' ), JText::_( 'Category') );
			return false;
		}
		// check for existing name
		$query = "SELECT id"
		. "\n FROM #__categories "
		. "\n WHERE name = '$this->name'"
		. "\n AND section = '$this->section'"
		;
		$this->_db->setQuery( $query );

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = sprintf( JText::_( 'WARNNAMETRYAGAIN' ), JText::_( 'Category') );
			return false;
		}
		return true;
	}
}
?>
