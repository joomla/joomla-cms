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
* Template Table Class
*
* Provides access to the jos_templates table
* @package Joomla
* @since 1.0
*/
class mosTemplate extends mosDBTable {
	/** @var int */
	var $id				= null;
	/** @var string */
	var $cur_template	= null;
	/** @var int */
	var $col_main		= null;

	/**
	* @param database A database connector object
	*/
	function mosTemplate( &$database ) {
		$this->mosDBTable( '#__templates', 'id', $database );
	}
}
?>
