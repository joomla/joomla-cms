<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Categories
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();


/**
 * @package		Joomla
 * @subpackage	Categories
 */
class TableCategory extends JTable
{
	/** @var int Primary key */
	var $id 				= null;
	/** @var int */
	var $parent_id			= 0;
	/** @var string */
	var $name 				= null;
	/** @var string */
	var $alias				= null;
	/** @var string */
	var $title 				= null;
	/** @var string */
	var $image 				= null;
	/** @var string */
	var $image_position		= null;
	/** @var string */
	var $section	 		= null;
	/** @var string */
	var $description		= null;
	/** @var int */
	var $count 				= 0;
	/** @var int */
	var $published 			= 0;
	/** @var int */
	var $checked_out 		= 0;
	/** @var datetime */
	var $checked_out_time 	= 0;
	/** @var int */
	var $ordering 			= null;
	/** @var string */
	var $params 				= null;
	/** @var int */
	var $access 				= null;

	/**
	* @param database A database connector object
	*/
	function __construct(&$db)
	{
		parent::__construct( '#__categories', 'id', $db );
	}

	/**
	 * Overloaded check function
	 *
	 * @access public
	 * @return boolean
	 * @see JTable::check
	 * @since 1.5
	 */
	function check()
	{
		$alias = JFilterOutput::stringURLSafe($this->title);

		if(empty($this->alias) || $this->alias === $alias ) {
			$this->alias = $alias;
		}

		return true;
	}
}