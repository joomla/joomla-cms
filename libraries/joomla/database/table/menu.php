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
 * Menu table
 *
 * @package 	Joomla.Framework
 * @subpackage 	Model
 * @since		1.0
 */
class JTableMenu extends JTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $menutype			= null;
	/** @var string */
	var $name				= null;
	/** @var string */
	var $link				= null;
	/** @var int */
	var $type				= null;
	/** @var int */
	var $published			= null;
	/** @var int */
	var $componentid		= null;
	/** @var int */
	var $parent				= null;
	/** @var int */
	var $sublevel			= null;
	/** @var int */
	var $ordering			= null;
	/** @var boolean */
	var $checked_out		= null;
	/** @var datetime */
	var $checked_out_time	= null;
	/** @var boolean */
	var $pollid				= null;
	/** @var string */
	var $browserNav			= null;
	/** @var int */
	var $access				= null;
	/** @var int */
	var $utaccess			= null;
	/** @var string */
	var $params				= null;
	/** @var string */
	var $model_name			= null;
	/** @var string */
	var $view_name			= null;
	/** @var string */
	var $renderer_name		= null;
	/** @var int */
	var $lft				= null;
	/** @var int */
	var $rgt				= null;

	/**
	 * Constructor
	 *
	 * @access protected
	 * @param database A database connector object
	 */
	function __construct( &$db ) {
		parent::__construct( '#__menu', 'id', $db );
	}

	/**
	* Overloaded bind function
	*
	* @acces public
	* @param array $hash named array
	* @return null|string	null is operation was satisfactory, otherwise returns an error
	* @see JTable:bind
	* @since 1.5
	*/

	function bind($array, $ignore = '')
	{
		if (is_array( $array['params'] )) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}
}

?>
