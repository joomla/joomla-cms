<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	HTML
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JHTMLGrid_checkedout
{
	/**
	 * @param
	 */
	function display( &$row, $overlib=1 )
	{
		$hover = '';
		if ($overlib)
		{
			$text	= addslashes( htmlspecialchars( $row->editor ) );
			$date	= JHTML::Date( $row->checked_out_time, '%A, %d %B %Y' );
			$time	= JHTML::Date( $row->checked_out_time, '%H:%M' );
			$hover = '<span class="editlinktip hasTip" title="'. JText::_( 'Checked Out' ) .'::'. $text .'<br />'. $date .'<br />'. $time .'">';
		}
		$checked = $hover .'<img src="images/checked_out.png"/></span>';
		return $checked;
	}
}