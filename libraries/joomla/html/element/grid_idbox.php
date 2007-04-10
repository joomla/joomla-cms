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
class JHTMLGrid_idBox
{
	/**
	 * @param	string	The link title
	 */
	function display( &$item, $rowNum, $identifier='id', $name='cid' )
	{
		$user   =& JFactory::getUser();
		$userId = $user->get('id');

		if (isset( $row->checked_out ))
		{
			if ($row->checked_out == $userId)
			{
				echo JHTML::element( 'grid_checkedout', $item );
				return;
			}
		}
		$result = '<input type="checkbox" id="cb'.$rowNum.'" name="'.$name.'[]" value="'.$item->$identifier.'" onclick="isChecked(this.checked);" />';
		return $result;
	}
}