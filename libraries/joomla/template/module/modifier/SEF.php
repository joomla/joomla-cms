<?php
/**
* patTemplate modfifier for Search Engine Friendly URL's
* @version $Id: SEF.php 47 2005-09-15 02:55:27Z rhuk $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JTemplate Translate modifier
 *
 * @package 	Joomla.Framework
 * @subpackage 	Template
 * @since 1.1
 */
class patTemplate_Modifier_SEF extends patTemplate_Modifier
{
   /**
	* modify the value
	*
	* @access	public
	* @param	string		value
	* @return	string		modified value
	*/
	function modify( $value, $params = array() )
	{
		if (function_exists( 'sefRelToAbs' )) {
			return sefRelToAbs( $value );
		} else {
			return $value;
		}
	}
}
?>