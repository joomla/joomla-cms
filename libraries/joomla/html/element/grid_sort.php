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
class JHTMLGrid_sort
{
	/**
	 * @param	string	The link title
	 * @param	string	The order field for the column
	 * @param	string	The current direction
	 * @param	string	The selected ordering
	 * @param	string	An optional task override
	 */
	function display( $title, $order, $direction, $selected, $task=NULL )
	{
		$direction	= strtolower( $direction );
		$images		= array( 'sort_asc.png', 'sort_desc.png' );
		$index		= intval( $direction == 'desc' );
		$direction	= ($direction == 'desc') ? 'asc' : 'desc';
	?>
		<a href="javascript:tableOrdering('<?php echo $order; ?>','<?php echo $direction; ?>','<?php echo $task; ?>');" title="<?php echo JText::_( 'Click to sort this column' ); ?>">
	<?php
		echo JText::_( $title );
		if ($order == $selected ) {
			echo JAdminMenus::ImageCheckAdmin( $images[$index], '/images/', NULL, NULL, '', '', 1 );
		}
		echo '</a>';
	}
}