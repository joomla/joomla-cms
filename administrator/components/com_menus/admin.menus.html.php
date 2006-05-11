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

		<div id="pane-document">

		<table class="adminform">
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<input type="button" value="<?php echo JText::_( 'Go' ); ?>" class="button" onclick="this.form.submit();" />
				<input type="button" value="<?php echo JText::_( 'Reset' ); ?>" class="button" onclick="getElementById('search').value='';this.form.submit();" />
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

		<?php
		if ( $menutype == 'mainmenu' ) {
			?>
			<table class="adminheading">
			<tr>
				<td align="left" nowrap="nowrap" style="color: red; font-weight: normal;">
					<?php echo JText::_( 'WARNDELETEMENU' ); ?>
				</td>
				<td align="right" valign="top" nowrap="nowrap" style="font-weight: normal;">
					<?php echo JText::_( 'WARNMAINMENUHOME' ); ?>
				</td>
			</tr>
			</table>
			<?php
		}
		?>

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
		</div>

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

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&hidemainmenu=1&type='. $row->type;

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

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type;

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

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type;

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

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type;

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

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type;

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
