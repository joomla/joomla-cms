<?php
/**
* @version $Id: weblink.php 4481 2006-08-12 04:07:07Z webImagery $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Include library dependencies
jimport('joomla.filter.input');

/**
 * Weblink table
 *
 * @package 	Joomla.Framework
 * @subpackage 	Model
 * @since		1.0
 */
class JTableWeblink extends JTable
{
	/** @var int Primary key */
	var $id		= null;
	/** @var int */
	var $catid		= null;
	/** @var int */
	var $sid		= null;
	/** @var string */
	var $title		= null;
	/** @var string */
	var $url		= null;
	/** @var string */
	var $description	= null;
	/** @var datetime */
	var $date		= null;
	/** @var int */
	var $hits		= null;
	/** @var int */
	var $published	= null;
	/** @var int */
	var $checked_out	= null;
	/** @var datetime */
	var $checked_out_time	= null;
	/** @var int */
	var $ordering	= null;
	/** @var int */
	var $archived	= null;
	/** @var int */
	var $approved	= null;
	/** @var string */
	var $params		= null;


	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct( '#__weblinks', 'id', $db );
	}

	/**
	 * Validation and filtering
	 */
	function check()
	{
		return true;
	}

	/**
	 * Method to checkin/unlock the weblink
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkin($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery('UPDATE #__weblinks SET checked_out = 0 WHERE id = '.$id);
		return $db->query();
	}
}
?>