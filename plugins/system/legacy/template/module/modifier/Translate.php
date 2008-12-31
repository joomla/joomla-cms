<?php
/**
* patTemplate modfifier for Search Engine Friendly URL's
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Template
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * JTemplate SEF modifier
 *
 * @package 	Joomla.Framework
 * @subpackage		Template
 * @since		1.5
 */
class patTemplate_Modifier_Translate extends patTemplate_Modifier
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
		return JText::_( $value );
	}
}