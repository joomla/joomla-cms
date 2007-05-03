<?php
/**
* @version		$Id: select.php 7116 2007-04-09 22:40:27Z eddieajau $
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
 * Utility class for creating HTML Grids
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JHTMLGrid
{
	
	function Legend( )
	{
		?>
		<table cellspacing="0" cellpadding="4" border="0" align="center">
		<tr align="center">
			<td>
			<img src="images/publish_y.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Pending' ); ?>" />
			</td>
			<td>
			<?php echo JText::_( 'Published, but is' ); ?> <u><?php echo JText::_( 'Pending' ); ?></u> |
			</td>
			<td>
			<img src="images/publish_g.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Visible' ); ?>" />
			</td>
			<td>
			<?php echo JText::_( 'Published and is' ); ?> <u><?php echo JText::_( 'Current' ); ?></u> |
			</td>
			<td>
			<img src="images/publish_r.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Finished' ); ?>" />
			</td>
			<td>
			<?php echo JText::_( 'Published, but has' ); ?> <u><?php echo JText::_( 'Expired' ); ?></u> |
			</td>
			<td>
			<img src="images/publish_x.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Finished' ); ?>" />
			</td>
			<td>
			<?php echo JText::_( 'Not Published' ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="8" align="center">
			<?php echo JText::_( 'Click on icon to toggle state.' ); ?>
			</td>
		</tr>
		</table>
		<?php
	}
	
	/**
	 * @param	string	The link title
	 * @param	string	The order field for the column
	 * @param	string	The current direction
	 * @param	string	The selected ordering
	 * @param	string	An optional task override
	 */
	function Sort( $title, $order, $direction, $selected, $task=NULL )
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
	
	/**
	* @param int The row index
	* @param int The record id
	* @param boolean
	* @param string The name of the form element
	* 
	* @return string
	*/
	function Id( $rowNum, $recId, $checkedOut=false, $name='cid' )
	{
		if ( $checkedOut ) {
			return '';
		} else {
			return '<input type="checkbox" id="cb'.$rowNum.'" name="'.$name.'[]" value="'.$recId.'" onclick="isChecked(this.checked);" />';
		}
	}
	
	function Access( &$row, $i, $archived = NULL )
	{
		if ( !$row->access )  {
			$color_access = 'style="color: green;"';
			$task_access = 'accessregistered';
		} else if ( $row->access == 1 ) {
			$color_access = 'style="color: red;"';
			$task_access = 'accessspecial';
		} else {
			$color_access = 'style="color: black;"';
			$task_access = 'accesspublic';
		}

		if ($archived == -1) 
		{
			$href = JText::_( $row->groupname );
		} 
		else 
		{
			$href = '
			<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task_access .'\')" '. $color_access .'>
			'. JText::_( $row->groupname ) .'
			</a>'
			;
		}

		return $href;
	}
	
	function CheckedOut( &$row, $i, $identifier = 'id' )
	{
		$user   =& JFactory::getUser();
		$userid = $user->get('id');

		$result = false;
		if(is_a($row, 'JTable')) {
			$result = $row->isCheckedOut($userid);
		} else {
			$result = JTable::isCheckedOut($userid, $row->checked_out);
		}

		$checked = '';
		if ( $result ) {
			$checked = JHTMLGrid::_checkedOut( $row );
		} else {
			$checked = JHTML::_('grid.id', $i, $row->$identifier );
		}

		return $checked;
	}
	
	function Published( &$row, $i, $imgY = 'tick.png', $imgX = 'publish_x.png', $prefix='' )
	{
		$img 	= $row->published ? $imgY : $imgX;
		$task 	= $row->published ? 'unpublish' : 'publish';
		$alt 	= $row->published ? JText::_( 'Published' ) : JText::_( 'Unpublished' );
		$action = $row->published ? JText::_( 'Unpublish Item' ) : JText::_( 'Publish item' );

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $prefix.$task .'\')" title="'. $action .'">
		<img src="images/'. $img .'" border="0" alt="'. $alt .'" />
		</a>'
		;

		return $href;
	}
	
	function State( $filter_state='*', $published='Published', $unpublished='Unpublished', $archived=NULL )
	{
		$state[] = JHTML::_('select.option',  '', '- '. JText::_( 'Select State' ) .' -' );
		$state[] = JHTML::_('select.option',  '*', JText::_( 'Any' ) );
		$state[] = JHTML::_('select.option',  'P', JText::_( $published ) );
		$state[] = JHTML::_('select.option',  'U', JText::_( $unpublished ) );

		if ($archived) {
			$state[] = JHTML::_('select.option',  'A', JText::_( $archived ) );
		}

		return JHTML::_('select.genericlist',   $state, 'filter_state', 'class="inputbox" size="1" onchange="submitform( );"', 'value', 'text', $filter_state );
	}
	
	function Order( $rows, $image='filesave.png', $task="saveorder" )
	{
		$image = JAdminMenus::ImageCheckAdmin( $image, '/images/', NULL, NULL, JText::_( 'Save Order' ), '', 1 );
		?>
		<a href="javascript:saveorder(<?php echo count( $rows )-1; ?>, '<?php echo $task; ?>')" title="<?php echo JText::_( 'Save Order' ); ?>">
			<?php echo $image; ?></a>
		<?php
	}

	
	function _checkedOut( &$row, $overlib = 1 )
	{
		$hover = '';
		if ( $overlib ) 
		{
			$text = addslashes(htmlspecialchars($row->editor));

			$date 	= JHTML::Date( $row->checked_out_time, '%A, %d %B %Y' );
			$time	= JHTML::Date( $row->checked_out_time, '%H:%M' );

			$hover = '<span class="editlinktip hasTip" title="'. JText::_( 'Checked Out' ) .'::'. $text .'<br />'. $date .'<br />'. $time .'">';
		}
		$checked = $hover .'<img src="images/checked_out.png"/></span>';

		return $checked;
	}
}

