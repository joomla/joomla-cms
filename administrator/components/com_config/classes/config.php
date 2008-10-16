<?php
/**
* @version		$Id: $
* @package		Joomla
* @subpackage	Config
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Utility class for com_config
 *
 * @static
 * @package 	Joomla
 * @subpackage	Config
 * @since		1.6
 */

class JHTMLConfig
{
	function warnicon()
	{
		$tip = '<img src="'.JURI::root().'media/system/images/warning.png" border="0"  alt="" />';
		return $tip;
	}
}
