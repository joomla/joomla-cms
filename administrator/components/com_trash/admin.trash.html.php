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
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* HTML class for all trash component output
* @package Joomla
* @subpackage Trash
*/
class HTML_trash {
	/**
	* Writes a list of the Trash items
	*/
	function showList( $option, $contents, $menus, $pageNav_content, $pageNav_menu ) {
		global $my;
		global $_LANG;

		$tabs = new mosTabs(1);
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
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<th class="trash"><?php echo $_LANG->_( 'Trash Manager' ); ?></th>
		</tr>
		</table>

		<?php
		$title = $_LANG->_( 'Content Items' );
		$tabs->startPane("content-pane");
		$tabs->startTab( $title, "content_items" );
		?>
		<table class="adminheading" width="90%">
		<tr>
			<th><small><?php echo $_LANG->_( 'Content Items' ); ?></small></th>
		</tr>
		</table>

		<table class="adminlist" width="90%">
		<tr>
			<th width="20"><?php echo $_LANG->_( 'NUM' ); ?></th>
			<th width="20">
			<input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $contents );?>);" />
			</th>
			<th width="20px">&nbsp;</th>
			<th class="title">
			<?php echo $_LANG->_( 'Title' ); ?>
			</th>
			<th>
			<?php echo $_LANG->_( 'Section' ); ?>
			</th>
			<th>
			<?php echo $_LANG->_( 'Category' ); ?>
			</th>
			<th width="70px">
			<?php echo $_LANG->_( 'ID' ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		$i = 0;
		$n = count( $contents );
		foreach ( $contents as $row ) {
			?>
			<tr class="<?php echo "row". $k; ?>">
				<td align="center" width="30px">
				<?php echo $i + 1 + $pageNav_content->limitstart;?>
				</td>
				<td width="20px" align="center"><?php echo mosHTML::idBox( $i, $row->id ); ?></td>
				<td width="20px"></td>
				<td nowrap>
				<?php
				echo $row->title;
				?>
				</td>
				<td align="center" width="20%">
				<?php
				echo $row->sectname;
				?>
				</td>
				<td align="center" width="20%">
				<?php
				echo $row->catname;
				?>
				</td>
				<td align="center">
				<?php
				echo $row->id;
				?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
			$i++;
		}
		?>
		</table>
		<?php echo $pageNav_content->getListFooter(); ?>
		<?php
		$title = $_LANG->_( 'Menu Items' );
		$tabs->endTab();
		$tabs->startTab( $title, "menu_items" );
		?>
		<table class="adminheading" width="90%">
		<tr>
			<th><small><?php echo $_LANG->_( 'Menu Items' ); ?></small></th>
		</tr>
		</table>

		<table class="adminlist" width="90%">
		<tr>
			<th width="20"><?php echo $_LANG->_( 'NUM' ); ?></th>
			<th width="20">
			<input type="checkbox" name="toggle1" value="" onClick="checkAll_xtd(<?php echo count( $menus );?>);" />
			</th>
			<th width="20px">&nbsp;</th>
			<th class="title">
			<?php echo $_LANG->_( 'Title' ); ?>
			</th>
			<th>
			<?php echo $_LANG->_( 'Menu' ); ?>
			</th>
			<th>
			<?php echo $_LANG->_( 'Type' ); ?>
			</th>
			<th width="70px">
			<?php echo $_LANG->_( 'ID' ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		$i = 0;
		$n = count( $menus );
		foreach ( $menus as $row ) {
			?>
			<tr class="<?php echo "row". $k; ?>">
				<td align="center" width="30px">
				<?php echo $i + 1 + $pageNav_menu->limitstart;?>
				</td>
				<td width="30px" align="center">
				<input type="checkbox" id="cb1<?php echo $i;?>" name="mid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" />
				</td>
				<td width="20px"></td>
				<td nowrap>
				<?php
				echo $row->name;
				?>
				</td>
				<td align="center" width="20%">
				<?php
				echo $row->menutype;
				?>
				</td>
				<td align="center" width="20%">
				<?php
				echo $row->type;
				?>
				</td>
				<td align="center">
				<?php
				echo $row->id;
				?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
			$i++;
		}
		?>
		</table>
		<?php echo $pageNav_menu->getListFooter(); ?>
		<?php
		$tabs->endTab();
		$tabs->endPane();
		?>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		</form>
		<?php
	}


	/**
	* A delete confirmation page
	* Writes list of the items that have been selected for deletion
	*/
	function showDelete( $option, $cid, $items, $type ) {
		global $_LANG;
	?>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<th><?php echo $_LANG->_( 'Delete Items' ); ?></th>
		</tr>
		</table>

		<br />
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="20%">
			<strong><?php echo $_LANG->_( 'Number of Items' ); ?>:</strong>
			<br />
			<font color="#000066"><strong><?php echo count( $cid ); ?></strong></font>
			<br /><br />
			</td>
			<td  valign="top" width="25%">
			<strong><?php echo $_LANG->_( 'Items being Deleted' ); ?>:</strong>
			<br />
			<?php
			echo "<ol>";
			foreach ( $items as $item ) {
				echo "<li>". $item->name ."</li>";
			}
			echo "</ol>";
			?>
			</td>
			 <td valign="top"><?php echo $_LANG->_( '* This will' ); ?>
			 <strong><font color="#FF0000"><?php echo $_LANG->_( 'Permanently Delete' ); ?></font></strong> <br /><?php echo $_LANG->_( 'these Items from the Database *' ); ?>
			<br /><br /><br />
			<div style="border: 1px dotted gray; width: 70px; padding: 10px; margin-left: 50px;">
			<a class="toolbar" href="javascript:if (confirm('<?php echo $_LANG->_( 'WARNWANTDELLISTEDITEMS' ); ?>')){ submitbutton('delete');}" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('remove','','images/delete_f2.png',1);">
			<img name="remove" src="images/delete.png" alt="<?php echo $_LANG->_( 'Delete' ); ?>" border="0" align="middle" />
			&nbsp;<?php echo $_LANG->_( 'Delete' ); ?>
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
	function showRestore( $option, $cid, $items, $type ) {
		global $_LANG;
	?>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<th><?php echo $_LANG->_( 'Restore Items' ); ?></th>
		</tr>
		</table>

		<br />
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="20%">
			<strong><?php echo $_LANG->_( 'Number of Items' ); ?>:</strong>
			<br />
			<font color="#000066"><strong><?php echo count( $cid ); ?></strong></font>
			<br /><br />
			</td>
			<td  valign="top" width="25%">
			<strong><?php echo $_LANG->_( 'Items being Restored' ); ?>:</strong>
			<br />
			<?php
			echo "<ol>";
			foreach ( $items as $item ) {
				echo "<li>". $item->name ."</li>";
			}
			echo "</ol>";
			?>
			</td>
			 <td valign="top"><?php echo $_LANG->_( '* This will' ); ?>
			 <strong><font color="#FF0000"><?php echo $_LANG->_( 'Restore' ); ?></font></strong> <?php echo $_LANG->_( 'these Items,' ); ?><br /><?php echo $_LANG->_( 'TIPWILLBERETURNED' ); ?>
			<br /><br /><br />
			<div style="border: 1px dotted gray; width: 80px; padding: 10px; margin-left: 50px;">
			<a class="toolbar" href="javascript:if (confirm('<?php echo $_LANG->_( 'WARNRESTORE' ); ?>')){ submitbutton('restore');}" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('restore','','images/restore_f2.png',1);">
			<img name="restore" src="images/restore.png" alt="<?php echo $_LANG->_( 'Restore' ); ?>" border="0" align="middle" />
			&nbsp;<?php echo $_LANG->_( 'Restore' ); ?>
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