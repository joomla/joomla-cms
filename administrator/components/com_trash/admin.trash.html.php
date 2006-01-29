<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Trash
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
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
* HTML class for all trash component output
* @package Joomla
* @subpackage Trash
*/

class HTML_trash {	
	/**
	* Writes a list of the Trash items
	*/
	function showListContent( $option, &$contents, &$pageNav, &$lists ) {
		?>
		<form action="index2.php?option=com_trash&amp;task=viewContent" method="post" name="adminForm">
		
		<table class="adminform">
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<input type="button" value="<?php echo JText::_( 'Go' ); ?>" class="button" onclick="this.form.submit();" />
				<input type="button" value="<?php echo JText::_( 'Reset' ); ?>" class="button" onclick="getElementById('search').value='';this.form.submit();" />
			</td>
			<td nowrap="nowrap">
			</td>
		</tr>
		</table>

		<div id="tablecell">				
			<table class="adminlist" width="90%">
			<tr>
				<th width="20">
					<?php echo JText::_( 'NUM' ); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $contents );?>);" />
				</th>
				<th class="title">
					<?php mosCommonHTML :: tableOrdering( 'Title', 'c.title', $lists ); ?>
				</th>
				<th width="70">
					<?php mosCommonHTML :: tableOrdering( 'ID', 'c.id', $lists ); ?>
				</th>
				<th class="title" width="25%">
					<?php mosCommonHTML :: tableOrdering( 'Section', 'sectname', $lists ); ?>
				</th>
				<th class="title" width="25%">
					<?php mosCommonHTML :: tableOrdering( 'Category', 'catname', $lists ); ?>
				</th>
			</tr>
			<?php
			$k = 0;
			$i = 0;
			$n = count( $contents );
			foreach ( $contents as $row ) {
				?>
				<tr class="<?php echo "row". $k; ?>">
					<td align="center">
						<?php echo $i + 1 + $pageNav->limitstart;?>
					</td>
					<td align="center">
						<?php echo mosHTML::idBox( $i, $row->id ); ?>
					</td>
					<td nowrap="nowrap">
						<?php echo $row->title; ?>
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
					<td>
						<?php echo $row->sectname; ?>
					</td>
					<td>
						<?php echo $row->catname; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
				$i++;
			}
			?>
			</table>
			
			<?php echo $pageNav->getListFooter(); ?>
		</div>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="viewContent" />
		<input type="hidden" name="return" value="viewContent" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}


	/**
	* Writes a list of the Trash items
	*/
	function showListMenu( $option, &$menus, &$pageNav, &$lists ) {
		?>
		<script language="javascript" type="text/javascript">
		/**
		* Toggles the check state of a group of boxes
		*
		* Checkboxes must have an id attribute in the form cb0, cb1...
		* @param The number of box to 'check'
		*/
		function checkAll_xtd ( n ) {
			var f = document.adminForm;
			var c = f.toggle1.checked;
			var n2 = 0;
			for ( i=0; i < n; i++ ) {
				cb = eval( 'f.cb1' + i );
				if (cb) {
					cb.checked = c;
					n2++;
				}
			}
			if (c) {
				document.adminForm.boxchecked.value = n2;
			} else {
				document.adminForm.boxchecked.value = 0;
			}
		}
		</script>
		<form action="index2.php?option=com_trash&amp;task=viewMenu" method="post" name="adminForm">

		<table class="adminform">
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<input type="button" value="<?php echo JText::_( 'Go' ); ?>" class="button" onclick="this.form.submit();" />
				<input type="button" value="<?php echo JText::_( 'Reset' ); ?>" class="button" onclick="getElementById('search').value='';this.form.submit();" />
			</td>
			<td nowrap="nowrap">
			</td>
		</tr>
		</table>

		<div id="tablecell">				
			<table class="adminlist" width="90%">
			<tr>
				<th width="20">
					<?php echo JText::_( 'NUM' ); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle1" value="" onclick="checkAll_xtd(<?php echo count( $menus );?>);" />
				</th>
				<th class="title">
					<?php mosCommonHTML :: tableOrdering( 'Name', 'm.name', $lists ); ?>
				</th>
				<th width="70">
					<?php mosCommonHTML :: tableOrdering( 'ID', 'm.id', $lists ); ?>
				</th>
				<th class="title" width="25%">
					<?php mosCommonHTML :: tableOrdering( 'Menu', 'm.menutype', $lists ); ?>
				</th>
				<th class="title" width="25%">
					<?php mosCommonHTML :: tableOrdering( 'Type', 'm.type', $lists ); ?>
				</th>
			</tr>
			<?php
			$k = 0;
			$i = 0;
			$n = count( $menus );
			foreach ( $menus as $row ) {
				?>
				<tr class="<?php echo "row". $k; ?>">
					<td align="center">
						<?php echo $i + 1 + $pageNav->limitstart;?>
					</td>
					<td align="center">
						<input type="checkbox" id="cb1<?php echo $i;?>" name="mid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" />
					</td>
					<td nowrap="nowrap">
						<?php echo $row->name; ?>
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
					<td>
						<?php echo $row->menutype; ?>
					</td>
					<td>
						<?php echo $row->type; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
				$i++;
			}
			?>
			</table>
			
			<?php echo $pageNav->getListFooter(); ?>
		</div>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="viewMenu" />
		<input type="hidden" name="return" value="viewMenu" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}


	/**
	* A delete confirmation page
	* Writes list of the items that have been selected for deletion
	*/
	function showDelete( $option, $cid, $items, $type, $return ) {
	?>
		<form action="index2.php?option=com_trash&amp;task=<?php echo $return; ?>" method="post" name="adminForm">
		
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="20%">
			<strong><?php echo JText::_( 'Number of Items' ); ?>:</strong>
			<br />
			<font color="#000066"><strong><?php echo count( $cid ); ?></strong></font>
			<br /><br />
			</td>
			<td  valign="top" width="25%">
			<strong><?php echo JText::_( 'Items being Deleted' ); ?>:</strong>
			<br />
			<?php
			echo "<ol>";
			foreach ( $items as $item ) {
				echo "<li>". $item->name ."</li>";
			}
			echo "</ol>";
			?>
			</td>
			 <td valign="top"><?php echo JText::_( '* This will' ); ?>
			 <strong><font color="#FF0000"><?php echo JText::_( 'Permanently Delete' ); ?></font></strong> <br /><?php echo JText::_( 'these Items from the Database *' ); ?>
			<br /><br /><br />
			<div style="border: 1px dotted gray; width: 70px; padding: 10px; margin-left: 50px;">
			<a class="toolbar" href="javascript:if (confirm('<?php echo JText::_( 'WARNWANTDELLISTEDITEMS' ); ?>')){ submitbutton('delete');}" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('remove','','images/delete_f2.png',1);">
			<img name="remove" src="images/delete.png" alt="<?php echo JText::_( 'Delete' ); ?>" border="0" align="middle" />
			&nbsp;<?php echo JText::_( 'Delete' ); ?>
			</a>
			</div>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="1" />
		<input type="hidden" name="type" value="<?php echo $type; ?>" />
		<input type="hidden" name="return" value="<?php echo $return;?>" />
		<?php
		foreach ($cid as $id) {
			echo "\n<input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
		</form>
		<?php
	}


	/**
	* A restore confirmation page
	* Writes list of the items that have been selected for restore
	*/
	function showRestore( $option, $cid, $items, $type, $return ) {
		?>
		<form action="index2.php?option=com_trash&amp;task=<?php echo $return; ?>" method="post" name="adminForm">
		
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="20%">
			<strong><?php echo JText::_( 'Number of Items' ); ?>:</strong>
			<br />
			<font color="#000066"><strong><?php echo count( $cid ); ?></strong></font>
			<br /><br />
			</td>
			<td  valign="top" width="25%">
			<strong><?php echo JText::_( 'Items being Restored' ); ?>:</strong>
			<br />
			<?php
			echo "<ol>";
			foreach ( $items as $item ) {
				echo "<li>". $item->name ."</li>";
			}
			echo "</ol>";
			?>
			</td>
			 <td valign="top"><?php echo JText::_( '* This will' ); ?>
			 <strong><font color="#FF0000"><?php echo JText::_( 'Restore' ); ?></font></strong> <?php echo JText::_( 'these Items,' ); ?><br /><?php echo JText::_( 'TIPWILLBERETURNED' ); ?>
			<br /><br /><br />
			<div style="border: 1px dotted gray; width: 80px; padding: 10px; margin-left: 50px;">
			<a class="toolbar" href="javascript:if (confirm('<?php echo JText::_( 'WARNRESTORE' ); ?>')){ submitbutton('restore');}" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('restore','','images/restore_f2.png',1);">
			<img name="restore" src="images/restore.png" alt="<?php echo JText::_( 'Restore' ); ?>" border="0" align="middle" />
			&nbsp;<?php echo JText::_( 'Restore' ); ?>
			</a>
			</div>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="1" />
		<input type="hidden" name="type" value="<?php echo $type; ?>" />
		<input type="hidden" name="return" value="<?php echo $return;?>" />
		<?php
		foreach ($cid as $id) {
			echo "\n<input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
		</form>
		<?php
	}

}
?>