<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Menus
* @static
* @since 1.5
*/
class menuHTML
{
	/**
	 * Common top section to a menu edit form
	 */
	function MenuOutputTop( &$lists, &$menu, $text=NULL, $tip=NULL ) {
		?>
		<tr>
			<th colspan="2">
			<?php echo JText::_( 'Details' ); ?>
			</th>
		</tr>
		<tr>
			<td width="20%" align="right">
			<?php echo JText::_( 'ID' ); ?>:
			</td>
			<td width="80%">
				<strong><?php echo $menu->id; ?></strong>
			</td>
		</tr>
		<tr>
			<td align="right">
			<?php echo JText::_( 'Menu Type' ); ?>:
			</td>
			<td>
			<?php echo JText::_( $text ); ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JText::_( 'Published' ); ?>:
			</td>
			<td>
			<?php echo $lists['published']; ?>
			</td>
		</tr>
		<tr>
			<td align="right">
			<?php echo JText::_( 'Name' ); ?>:
			</td>
			<td>
			<input class="inputbox" type="text" name="name" size="50" maxlength="100" value="<?php echo $menu->name; ?>" />
			<?php
			if ( !$menu->id && $tip ) {
				echo mosToolTip( JText::_( 'TIPIFLEAVEBLANKCAT' ) );
			}
			?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Common bottom section to a menu edit form
	 */
	function MenuOutputBottom( &$lists, &$menu ) {
		?>
		<tr>
			<td align="right">
			<?php echo JText::_( 'Url' ); ?>:
			</td>
			<td>
			<?php echo ampReplace($lists['link']); ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JText::_( 'Ordering' ); ?>:
			</td>
			<td>
			<?php echo $lists['ordering']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JText::_( 'Access Level' ); ?>:
			</td>
			<td>
			<?php echo $lists['access']; ?>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top">
			<?php echo JText::_( 'Parent Item' ); ?>:
			</td>
			<td>
			<?php echo $lists['parent']; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Outputs the parameters block for a menu item edit form
	 * @param object A JParameters object
	 * @param object A JMenu object
	 * @param string ??
	 */
	function MenuOutputParams( &$params, $menu, $tip=NULL ) {
		?>
		<fieldset>
			<legend>
				<?php echo JText::_( 'Menu Parameters' ); ?>
			</legend>
				
			<?php
			if ($tip) {
				if ($menu->id) {
					echo $params->render();
				} else {
					?>
					<strong>
					<?php echo JText::_( 'TIPPARAMLISTMENUITEM' ); ?>
					</strong>
					<?php
				}
			} else {
				echo $params->render();
			}
			?>
		</fieldset>
		<?php
	}
}


/**
* @package Joomla
* @subpackage Menus
*/
class HTML_menusections {

	function showMenusections( &$rows, &$page, $menutype, $option, &$lists ) 
	{
		global $mainframe;

		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');
		$user =& $mainframe->getUser();
		
		//Ordering allowed ?
		$ordering = ($lists['order'] == 'm.ordering');

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_menus&amp;menutype=<?php echo $menutype; ?>" method="post" name="adminForm">

		<table>
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php
				echo JText::_( 'Max Levels' );
				echo $lists['levellist'];
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>
		
		<table class="adminlist">
			<thead>
				<tr>
					<th width="20">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" />
					</th>
					<th class="title" width="30%">
						<?php mosCommonHTML::tableOrdering( 'Menu Item', 'm.name', $lists ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'Published', 'm.published', $lists ); ?>
					</th>
					<th width="80" nowrap="nowrap">
						<a href="javascript:tableOrdering('m.ordering','ASC');" title="<?php echo JText::_( 'Order by' ); ?> <?php echo JText::_( 'Order' ); ?>">
							<?php echo JText::_( 'Order' ); ?>
						</a>			
					</th>
					<th width="1%">
						<?php mosCommonHTML::saveorderButton( $rows ); ?>
					</th>
					<th width="10%">
						<?php mosCommonHTML::tableOrdering( 'Access', 'groupname', $lists ); ?>
					</th>
					<th nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'Itemid', 'm.id', $lists ); ?>
					</th>
					<th width="15%" class="title">
						<?php mosCommonHTML::tableOrdering( 'Type', 'm.type', $lists ); ?>
					</th>
					<th nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'CID', 'm.componentid', $lists ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<td colspan="12">
					<?php echo $page->getListFooter(); ?>
				</td>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			$i = 0;
			$n = count( $rows );
			foreach ($rows as $row) {
				$access 	= mosCommonHTML::AccessProcessing( $row, $i );
				$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
				$published 	= mosCommonHTML::PublishedProcessing( $row, $i );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $i + 1 + $page->limitstart;?>
					</td>
					<td>
						<?php echo $checked; ?>
					</td>
					<td nowrap="nowrap">
						<?php
						if ( $row->checked_out && ( $row->checked_out != $user->get('id') ) ) {
							echo $row->treename;
						} else {
							$link = 'index2.php?option=com_menus&menutype='. $row->menutype .'&task=edit&id='. $row->id . '&hidemainmenu=1';
							?>
							<a href="<?php echo ampReplace( $link ); ?>">
								<?php echo $row->treename; ?></a>
							<?php
						}
						?>
					</td>
					<td width="10%" align="center">
						<?php echo $published;?>
					</td>
					<td class="order" colspan="2" nowrap="nowrap">
						<span><?php echo $page->orderUpIcon( $i, true, 'orderup', 'Move Up', $ordering); ?></span>
						<span><?php echo $page->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', $ordering ); ?></span>
						<?php $disabled = $ordering ?  '' : '"disabled=disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
					</td>
					<td align="center">
						<?php echo $access;?>
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
					<td>
						<span class="editlinktip">
							<?php
							echo mosToolTip( $row->descrip, '', 280, 'tooltip.png', $row->type, $row->edit, !empty($row->edit) );
							?>
						</span>
					</td>
					<td align="center">
						<?php echo $row->componentid; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
				$i++;
			}
			?>
			</tbody>
			</table>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="menutype" value="<?php echo $menutype; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}


	/**
	* Displays a selection list for menu item types
	*/
	function addMenuItem( &$cid, $menutype, $option, $types_content, $types_component, $types_link, $types_other, $types_submit ) {

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm">

		<table width="100%">
		<tr>
			<td valign="bottom" nowrap="nowrap" style="color: red;">
            <?php echo JText::_( 'DESCMENUGROUP' ); ?>
			</td>
		</tr>
		</table>

		<table width="100%">
		<tr>
			<td width="50%" valign="top">
				<fieldset>
					<legend><?php echo JText::_( 'Content' ); ?></legend>
					<table class="adminform">
					<?php
					$k 		= 0;
					$count 	= count( $types_content );
						for ( $i=0; $i < $count; $i++ ) {
						$row = &$types_content[$i];

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type .'&hidemainmenu=1';

						HTML_menusections::htmlOptions( $row, $link, $k, $i );

						$k = 1 - $k;
					}
					?>
					</table>
				</fieldset>
				<fieldset>
					<legend><?php echo JText::_( 'Miscellaneous' ); ?></legend>
					<table class="adminform">
					<?php
					$k 		= 0;
					$count 	= count( $types_other );
						for ( $i=0; $i < $count; $i++ ) {
						$row = &$types_other[$i];

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type .'&hidemainmenu=1';

						HTML_menusections::htmlOptions( $row, $link, $k, $i );

						$k = 1 - $k;
					}
					?>
					</table>
				</fieldset>
				<fieldset>
					<legend><?php echo JText::_( 'Submit' ); ?></legend>
					<table class="adminform">
					<?php
					$k 		= 0;
					$count 	= count( $types_submit );
						for ( $i=0; $i < $count; $i++ ) {
						$row = &$types_submit[$i];

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type .'&hidemainmenu=1';

						HTML_menusections::htmlOptions( $row, $link, $k, $i );

						$k = 1 - $k;
					}
					?>
					</table>
				</fieldset>
			</td>
			<td width="50%" valign="top">
				<fieldset>
					<legend><?php echo JText::_( 'Components' ); ?></legend>
					<table class="adminform">
					<?php
					$k 		= 0;
					$count 	= count( $types_component );
						for ( $i=0; $i < $count; $i++ ) {
						$row = &$types_component[$i];

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type .'&hidemainmenu=1';

						HTML_menusections::htmlOptions( $row, $link, $k, $i );

						$k = 1 - $k;
					}
					?>
					</table>
				</fieldset>
				<fieldset>
					<legend><?php echo JText::_( 'Links' ); ?></legend>
					<table class="adminform">
					<?php
					$k 		= 0;
					$count 	= count( $types_link );
						for ( $i=0; $i < $count; $i++ ) {
						$row = &$types_link[$i];

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type .'&hidemainmenu=1';

						HTML_menusections::htmlOptions( $row, $link, $k, $i );

						$k = 1 - $k;
					}
					?>
					</table>
				</fieldset>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="menutype" value="<?php echo $menutype; ?>" />
		<input type="hidden" name="task" value="edit" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}

	function htmlOptions( &$row, $link, $k, $i ) {
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td width="20">
			</td>
			<td style="height: 25px;">
				<span class="editlinktip" style="cursor: pointer;">
						<?php
						echo mosToolTip( $row->descrip, $row->name, 250, '', $row->name, $link, 1 );
						?>
				</span>
			</td>
			<td width="20">
				<input type="radio" id="cb<?php echo $i;?>" name="type" value="<?php echo $row->type; ?>" onclick="isChecked(this.checked);" />
			</td>
			<td width="20">
			</td>
		</tr>
		<?php
	}

	/**
	* Form to select Menu to move menu item(s) to
	*/
	function moveMenu( $option, $cid, $MenuList, $items, $menutype  ) {
		?>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="30%">
			<strong><?php echo JText::_( 'Move to Menu' ); ?>:</strong>
			<br />
			<?php echo $MenuList ?>
			<br /><br />
			</td>
			<td  valign="top">
			<strong>
			<?php echo JText::_( 'Menu Items being moved' ); ?>:
			</strong>
			<br />
			<ol>
			<?php
			foreach ( $items as $item ) {
				?>
				<li>
				<?php echo $item->name; ?>
				</li>
				<?php
			}
			?>
			</ol>
			</td>
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="boxchecked" value="1" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="menutype" value="<?php echo $menutype; ?>" />
		<?php
		foreach ( $cid as $id ) {
			echo "\n <input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
		</form>
		<?php
	}


	/**
	* Form to select Menu to copy menu item(s) to
	*/
	function copyMenu( $option, $cid, $MenuList, $items, $menutype  ) {
		?>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="30%">
			<strong>
			<?php echo JText::_( 'Copy to Menu' ); ?>:
			</strong>
			<br />
			<?php echo $MenuList ?>
			<br /><br />
			</td>
			<td  valign="top">
			<strong>
			<?php echo JText::_( 'Menu Items being copied' ); ?>:
			</strong>
			<br />
			<ol>
			<?php
			foreach ( $items as $item ) {
				?>
				<li>
				<?php echo $item->name; ?>
				</li>
				<?php
			}
			?>
			</ol>
			</td>
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="menutype" value="<?php echo $menutype; ?>" />
		<?php
		foreach ( $cid as $id ) {
			echo "\n <input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
		</form>
		<?php
	}
}
?>
