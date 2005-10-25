<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Mambots
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
* @package Joomla
* @subpackage Mambots
*/
class HTML_modules {

	/**
	* Writes a list of the defined modules
	* @param array An array of category objects
	*/
	function showMambots( &$rows, $client, &$pageNav, $option, &$lists, $search ) {
		global $my;
    	global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm">

		<table class="adminheading">
		<tr>
			<th class="modules" rowspan="2" nowrap="nowrap"><?php echo $_LANG->_( 'Mambot Manager' ); ?>
			 <small><small>[ <?php echo $client == 'admin' ? $_LANG->_( 'Administrator' ) : $_LANG->_( 'Site' );?> ]</small></small>
			</th>
			<td align="right" valign="top" nowrap="nowrap">
				<?php echo $lists['type'];?>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" nowrap="nowrap">
				<?php echo $_LANG->_( 'Filter' ); ?>:
				<input type="text" name="search" value="<?php echo $search;?>" class="text_area" onChange="document.adminForm.submit();" />
				<input type="button" value="<?php echo $_LANG->_( 'Go' ); ?>" class="button" onclick="this.form.submit();" />
				<input type="button" value="<?php echo $_LANG->_( 'Reset' ); ?>" class="button" onclick="getElementById('search').value='';this.form.submit();" />
			</td>
		</tr>
		</table>

		<table class="adminlist">
		<tr>
			<th width="20"><?php echo $_LANG->_( 'Num' ); ?></th>
			<th width="20">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows );?>);" />
			</th>
			<th class="title">
			<?php echo $_LANG->_( 'Mambot Name' ); ?>
			</th>
			<th nowrap="nowrap" width="10%">
	  		<?php echo $_LANG->_( 'Published' ); ?>
			</th>
			<th colspan="2" nowrap="true" width="5%">
			<?php echo $_LANG->_( 'Reorder' ); ?>
			</th>
			<th width="2%">
			<?php echo $_LANG->_( 'Order' ); ?>
			</th>
			<th width="1%">
			<a href="javascript: saveorder( <?php echo count( $rows )-1; ?> )"><img src="images/filesave.png" border="0" width="16" height="16" alt="<?php echo $_LANG->_( 'Save Order' ); ?>" /></a>
			</th>
			<th nowrap="nowrap" width="10%">
			<?php echo $_LANG->_( 'Access' ); ?>
			</th>
			<th nowrap="nowrap"  width="10%" class="title">
			<?php echo $_LANG->_( 'Type' ); ?>
			</th>
			<th nowrap="nowrap"  width="10%" class="title">
			<?php echo $_LANG->_( 'File' ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row 	= &$rows[$i];

			$link = 'index2.php?option=com_mambots&client='. $client .'&task=editA&hidemainmenu=1&id='. $row->id;

			$access 	= mosCommonHTML::AccessProcessing( $row, $i );
			$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
			$published 	= mosCommonHTML::PublishedProcessing( $row, $i );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="right"><?php echo $pageNav->rowNumber( $i ); ?></td>
				<td>
				<?php echo $checked; ?>
				</td>
				<td>
				<?php
				if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
					echo $row->name;
				} else {
					?>
					<a href="<?php echo $link; ?>">
					<?php echo $row->name; ?>
					</a>
					<?php
				}
				?>
				</td>
				<td align="center">
				<?php echo $published;?>
				</td>
				<td>
				<?php echo $pageNav->orderUpIcon( $i, ($row->folder == @$rows[$i-1]->folder && $row->ordering > -10000 && $row->ordering < 10000) ); ?>
				</td>
				<td>
				<?php echo $pageNav->orderDownIcon( $i, $n, ($row->folder == @$rows[$i+1]->folder && $row->ordering > -10000 && $row->ordering < 10000) ); ?>
				</td>
				<td align="center" colspan="2">
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
				</td>
				<td align="center">
				<?php echo $access;?>
				</td>
				<td nowrap="true">
				<?php echo $row->folder;?>
				</td>
				<td nowrap="true">
				<?php echo $row->element;?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>

		<?php echo $pageNav->getListFooter(); ?>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="client" value="<?php echo $client;?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}

	/**
	* Writes the edit form for new and existing module
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param mosCategory The category object
	* @param array <p>The modules of the left side.  The array elements are in the form
	* <var>$leftorder[<i>order</i>] = <i>label</i></var>
	* where <i>order</i> is the module order from the db table and <i>label</i> is a
	* text label associciated with the order.</p>
	* @param array See notes for leftorder
	* @param array An array of select lists
	* @param object Parameters
	*/
	function editMambot( &$row, &$lists, &$params, $option ) {
		global $mosConfig_live_site;
    	global $_LANG;

		$row->nameA = '';
		if ( $row->id ) {
			$row->nameA = '<small><small>[ '. $row->name .' ]</small></small>';
		}
		?>
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if (pressbutton == "cancel") {
				submitform(pressbutton);
				return;
			}
			// validation
			var form = document.adminForm;
			if (form.name.value == "") {
				alert( "<?php echo $_LANG->_( 'Mambot must have a name' ); ?>" );
			} else if (form.element.value == "") {
				alert( "<?php echo $_LANG->_( 'Mambot must have a filename' ); ?>" );
			} else {
				submitform(pressbutton);
			}
		}
		</script>
		<table class="adminheading">
		<tr>
			<th class="mambots">
			<?php echo $_LANG->_( 'Site Mambot' ); ?>:
			<small>
			<?php echo $row->id ? $_LANG->_( 'Edit' ) : $_LANG->_( 'New' );?>
			</small>
			<?php echo $row->nameA; ?>
			</th>
		</tr>
		</table>

		<form action="index2.php" method="post" name="adminForm">
		<table cellspacing="0" cellpadding="0" width="100%">
		<tr valign="top">
			<td width="60%" valign="top">
				<table class="adminform">
				<tr>
					<th colspan="2">
					<?php echo $_LANG->_( 'Mambot Details' ); ?>
					</th>
				<tr>
				<tr>
					<td width="100" >
					<?php echo $_LANG->_( 'Name' ); ?>:
					</td>
					<td>
					<input class="text_area" type="text" name="name" size="35" value="<?php echo $row->name; ?>" />
					</td>
				</tr>
				<tr>
					<td valign="top" >
					<?php echo $_LANG->_( 'Folder' ); ?>:
					</td>
					<td>
					<?php echo $lists['folder']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" >
					<?php echo $_LANG->_( 'Mambot file' ); ?>:
					</td>
					<td>
					<input class="text_area" type="text" name="element" size="35" value="<?php echo $row->element; ?>" />.php
					</td>
				</tr>
				<tr>
					<td valign="top" >
					<?php echo $_LANG->_( 'Mambot Order' ); ?>:
					</td>
					<td>
					<?php echo $lists['ordering']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" >
					<?php echo $_LANG->_( 'Access Level' ); ?>:
					</td>
					<td>
					<?php echo $lists['access']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top">
					<?php echo $_LANG->_( 'Published' ); ?>:
					</td>
					<td>
					<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" colspan="2">&nbsp;
					</td>
				</tr>
				<tr>
					<td valign="top">
					<?php echo $_LANG->_( 'Description' ); ?>:
					</td>
					<td>
					<?php echo $row->description; ?>
					</td>
				</tr>
				</table>
			</td>
			<td width="40%">
				<table class="adminform">
				<tr>
					<th colspan="2">
					<?php echo $_LANG->_( 'Parameters' ); ?>
					</th>
				<tr>
				<tr>
					<td>
					<?php
					if ( $row->id ) {
						echo $params->render();
					} else {
						echo '<i>'. $_LANG->_( 'No Parameters' ) .'</i>';
					}
					?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="client" value="<?php echo $row->client_id; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<script language="Javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/overlib_mini.js"></script>
		<?php
	}
}
?>
