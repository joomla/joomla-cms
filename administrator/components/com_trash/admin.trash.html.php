<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Trash
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
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
* @package		Joomla
* @subpackage	Trash
*/

class HTML_trash
{
	/**
	* Writes a list of the Trash items
	*/
	function showListContent( $option, &$contents, &$pageNav, &$lists )
	{
		?>
		<form action="index.php?option=com_trash&amp;task=viewContent" method="post" name="adminForm">

		<table>
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
			</td>
		</tr>
		</table>

		<div id="tablecell">
			<table class="adminlist" width="90%">
			<thead>
				<tr>
					<th width="20">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $contents );?>);" />
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort',   'Title', 'c.title', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th width="70">
						<?php echo JHTML::_('grid.sort',   'ID', 'c.id', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th class="title" width="25%">
						<?php echo JHTML::_('grid.sort',   'Section', 'sectname', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th class="title" width="25%">
						<?php echo JHTML::_('grid.sort',   'Category', 'catname', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="6">
						<?php echo $pageNav->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
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
							<?php echo JHTML::_('grid.id', $i, $row->id ); ?>
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
			</tbody>
			</table>
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
	function showListMenu( $option, &$menus, &$pageNav, &$lists )
	{
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
		<form action="index.php?option=com_trash&amp;task=viewMenu" method="post" name="adminForm">

		<table>
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
			</td>
		</tr>
		</table>

		<div id="tablecell">
			<table class="adminlist" width="90%">
			<thead>
				<tr>
					<th width="20">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle1" value="" onclick="checkAll_xtd(<?php echo count( $menus );?>);" />
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort',   'Name', 'm.name', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th width="70">
						<?php echo JHTML::_('grid.sort',   'ID', 'm.id', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th class="title" width="25%">
						<?php echo JHTML::_('grid.sort',   'Menu', 'm.menutype', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th class="title" width="25%">
						<?php echo JHTML::_('grid.sort',   'Type', 'm.type', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="6">
						<?php echo $pageNav->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
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
			</tbody>
			</table>
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
		<form action="index.php?option=com_trash&amp;task=<?php echo $return; ?>" method="post" name="adminForm">

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
			 <td valign="top"><?php echo JText::_( 'PERMDELETETHESEITEMS' ); ?>
			<br /><br /><br />
			<a class="icon-32-delete" style="border: 1px dotted gray; width: 70px; padding: 10px; margin-left: 50px; background-repeat: no-repeat; padding-left: 40px; "  href="javascript:void submitbutton('delete')">
			&nbsp;<?php echo JText::_( 'Delete' ); ?>
			</a>
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
		<form action="index.php?option=com_trash&amp;task=<?php echo $return; ?>" method="post" name="adminForm">

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
			 <td valign="top"><?php echo JText::_( 'RESTOREITEMS' ); ?><br /><?php echo JText::_( 'TIPWILLBERETURNED' ); ?>
			<br /><br /><br />
			<div style="border: 1px dotted gray; width: 80px; padding: 10px; margin-left: 50px;">
			<a class="toolbar" href="javascript:if (confirm('<?php echo JText::_( 'WARNRESTORE' ); ?>')){ submitbutton('restore');}">
			<img name="restore" src="images/restore_f2.png" alt="<?php echo JText::_( 'Restore' ); ?>" border="0" align="middle" />
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