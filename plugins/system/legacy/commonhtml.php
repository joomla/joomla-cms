<?php
/**
* @version		$Id$
* @package		Joomla.Legacy
* @subpackage	1.5
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Legacy class, use {@link JHTML::_()} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosCommonHTML
{
	/**
 	 * Legacy function, use {@link JHTML::_('legend');} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function ContentLegend( )
	{
		JHTML::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_content'.DS.'html' );
		JHTML::_('grid.legend');
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function menuLinksContent( &$menus )
	{
		foreach( $menus as $menu ) {
			?>
			<tr>
				<td colspan="2">
					<hr />
				</td>
			</tr>
			<tr>
				<td width="90" valign="top">
					<?php echo JText::_( 'Menu' ); ?>
				</td>
				<td>
					<a href="javascript:go2('go2menu','<?php echo $menu->menutype; ?>');" title="<?php echo JText::_( 'Go to Menu' ); ?>">
						<?php echo $menu->menutype; ?></a>
				</td>
			</tr>
			<tr>
				<td width="90" valign="top">
				<?php echo JText::_( 'Link Name' ); ?>
				</td>
				<td>
					<strong>
					<a href="javascript:go2('go2menuitem','<?php echo $menu->menutype; ?>','<?php echo $menu->id; ?>');" title="<?php echo JText::_( 'Go to Menu Item' ); ?>">
						<?php echo $menu->name; ?></a>
					</strong>
				</td>
			</tr>
			<tr>
				<td width="90" valign="top">
					<?php echo JText::_( 'State' ); ?>
				</td>
				<td>
					<?php
					switch ( $menu->published ) {
						case -2:
							echo '<font color="red">'. JText::_( 'Trashed' ) .'</font>';
							break;
						case 0:
							echo JText::_( 'UnPublished' );
							break;
						case 1:
						default:
							echo '<font color="green">'. JText::_( 'Published' ) .'</font>';
							break;
					}
					?>
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
			<td colspan="2">
				<input type="hidden" name="menu" value="" />
				<input type="hidden" name="menuid" value="" />
			</td>
		</tr>
		<?php
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function menuLinksSecCat( &$menus )
	{
		$i = 1;
		foreach( $menus as $menu ) {
			?>
			<fieldset>
				<legend align="right"> <?php echo $i; ?>. </legend>

				<table class="admintable">
				<tr>
					<td valign="top" class="key">
						<?php echo JText::_( 'Menu' ); ?>
					</td>
					<td>
						<a href="javascript:go2('go2menu','<?php echo $menu->menutype; ?>');" title="<?php echo JText::_( 'Go to Menu' ); ?>">
							<?php echo $menu->menutype; ?></a>
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<?php echo JText::_( 'Type' ); ?>
					</td>
					<td>
						<?php echo $menu->type; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<?php echo JText::_( 'Item Name' ); ?>
					</td>
					<td>
						<strong>
						<a href="javascript:go2('go2menuitem','<?php echo $menu->menutype; ?>','<?php echo $menu->id; ?>');" title="<?php echo JText::_( 'Go to Menu Item' ); ?>">
							<?php echo $menu->name; ?></a>
						</strong>
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<?php echo JText::_( 'State' ); ?>
					</td>
					<td>
						<?php
						switch ( $menu->published ) {
							case -2:
								echo '<font color="red">'. JText::_( 'Trashed' ) .'</font>';
								break;
							case 0:
								echo JText::_( 'UnPublished' );
								break;
							case 1:
							default:
								echo '<font color="green">'. JText::_( 'Published' ) .'</font>';
								break;
						}
						?>
					</td>
				</tr>
				</table>
			</fieldset>
			<?php
			$i++;
		}
		?>
		<input type="hidden" name="menu" value="" />
		<input type="hidden" name="menuid" value="" />
		<?php
	}

	/**
 	 * Legacy function, use {@link JHTMLGrid::checkedOut()} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function checkedOut( &$row, $overlib=1 )
	{
		jimport('joomla.html.html.grid');
		return JHTMLGrid::_checkedOut($row, $overlib);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('behavior.tooltip')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function loadOverlib()
	{
		JHTML::_('behavior.tooltip');
	}

	/**
 	 * Legacy function, use {@link JHTML::_('behavior.calendar')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function loadCalendar()
	{
		JHTML::_('behavior.calendar');
	}

	/**
 	 * Legacy function, use {@link JHTML::_('grid.access')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function AccessProcessing( &$row, $i, $archived=NULL )
	{
		return JHTML::_('grid.access',  $row, $i, $archived);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('grid.checkedout')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function CheckedOutProcessing( &$row, $i )
	{
		return JHTML::_('grid.checkedout',  $row, $i);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('grid.published')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function PublishedProcessing( &$row, $i, $imgY='tick.png', $imgX='publish_x.png' )
	{
		return JHTML::_('grid.published',$row, $i, $imgY, $imgX);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('grid.state')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function selectState( $filter_state=NULL, $published='Published', $unpublished='Unpublished', $archived=NULL )
	{
		return JHTML::_('grid.state', $filter_state, $published, $unpublished, $archived);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('grid.order')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function saveorderButton( $rows, $image='filesave.png' )
	{
		echo JHTML::_('grid.order', $rows, $image);
	}

	/**
 	 * Legacy function, use {@link echo JHTML::_('grid.sort')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function tableOrdering( $text, $ordering, &$lists, $task=NULL )
	{
		// TODO: We may have to invert order_Dir here because this control now does the flip for you
		echo JHTML::_('grid.sort',  $text, $ordering, @$lists['order_Dir'], @$lists['order'], $task);
	}
}