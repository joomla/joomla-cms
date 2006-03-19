<?php
/**
* @version $Id: admin.plugins.html.php 1541 2005-12-22 21:22:26Z Jinx $
* @package Joomla
* @subpackage Plugins
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
* @package Joomla
* @subpackage Plugins
*/
class HTML_modules {

	/**
	* Writes a list of the defined modules
	* @param array An array of category objects
	*/
	function showPlugins( &$rows, $client, &$pageNav, $option, &$lists ) {
		global $my;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_plugins" method="post" name="adminForm">

		<div id="pane-navigation">
			<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'navigation.html'); ?>
		</div>
		
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
					echo $lists['type'];
					echo $lists['state'];
					?>
				</td>
			</tr>
			</table>
			
			<table class="adminlist">
			<tr>
				<th width="20">
					<?php echo JText::_( 'Num' ); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows );?>);" />
				</th>
				<th class="title">
					<?php mosCommonHTML :: tableOrdering( 'Plugin Name', 'p.name', $lists ); ?>				
				</th>
				<th nowrap="nowrap" width="5%">
					<?php mosCommonHTML :: tableOrdering( 'Published', 'p.published', $lists ); ?>				
				</th>
				<?php
				if ( $lists['order'] == 'p.folder' ) {
					?>
					<th colspan="2" nowrap="true" width="5%">
						<?php echo JText::_( 'Reorder' ); ?>
					</th>
					<th width="2%">
						<?php echo JText::_( 'Order' ); ?>
					</th>
					<th width="1%">
						<?php mosCommonHTML :: saveorderButton( $rows ); ?>
					</th>
					<?php
				}
				?>
				<th nowrap="nowrap" width="7%">
					<?php mosCommonHTML :: tableOrdering( 'Access', 'groupname', $lists ); ?>				
				</th>
				<th nowrap="nowrap"  width="3%" class="title">
					<?php mosCommonHTML :: tableOrdering( 'ID', 'p.id', $lists ); ?>				
				</th>
				<th nowrap="nowrap"  width="13%" class="title">
					<?php mosCommonHTML :: tableOrdering( 'Type', 'p.folder', $lists ); ?>				
				</th>
				<th nowrap="nowrap"  width="13%" class="title">
					<?php mosCommonHTML :: tableOrdering( 'File', 'p.element', $lists ); ?>				
				</th>
			</tr>
			<?php
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row 	= &$rows[$i];
	
				$link = ampReplace( 'index2.php?option=com_plugins&client='. $client .'&task=editA&hidemainmenu=1&id='. $row->id );
	
				$access 	= mosCommonHTML::AccessProcessing( $row, $i );
				$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
				$published 	= mosCommonHTML::PublishedProcessing( $row, $i );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="right">
						<?php echo $pageNav->rowNumber( $i ); ?>
					</td>
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
							<?php echo $row->name; ?></a>
						<?php
					}
					?>
					</td>
					<td align="center">
						<?php echo $published;?>
					</td>
					<?php
					if ( $lists['order'] == 'p.folder' ) {
						?>
						<td>
							<?php echo $pageNav->orderUpIcon( $i, ($row->folder == @$rows[$i-1]->folder && $row->ordering > -10000 && $row->ordering < 10000) ); ?>
						</td>
						<td>
							<?php echo $pageNav->orderDownIcon( $i, $n, ($row->folder == @$rows[$i+1]->folder && $row->ordering > -10000 && $row->ordering < 10000) ); ?>
						</td>
						<td align="center" colspan="2">
							<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
						</td>
						<?php
					}
					?>
					<td align="center">
						<?php echo $access;?>
					</td>
					<td nowrap="true">
						<?php echo $row->id;?>
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
		</div>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="client" value="<?php echo $client;?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	/**
	* Writes the edit form for new and existing module
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param JCategoryModel The category object
	* @param array <p>The modules of the left side.  The array elements are in the form
	* <var>$leftorder[<i>order</i>] = <i>label</i></var>
	* where <i>order</i> is the module order from the db table and <i>label</i> is a
	* text label associciated with the order.</p>
	* @param array See notes for leftorder
	* @param array An array of select lists
	* @param object Parameters
	*/
	function editPlugin( &$row, &$lists, &$params, $option ) 
	{
		mosCommonHTML::loadOverlib();
		
		$row->nameA = '';
		if ( $row->id ) {
			$row->nameA = '<small><small>[ '. $row->name .' ]</small></small>';
		}
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if (pressbutton == "cancel") {
				submitform(pressbutton);
				return;
			}
			// validation
			var form = document.adminForm;
			if (form.name.value == "") {
				alert( "<?php echo JText::_( 'Plugin must have a name', true ); ?>" );
			} else if (form.element.value == "") {
				alert( "<?php echo JText::_( 'Plugin must have a filename', true ); ?>" );
			} else {
				submitform(pressbutton);
			}
		}
		</script>

		<form action="index2.php" method="post" name="adminForm">
		
		<div id="editcell">				
			<table cellspacing="0" cellpadding="0" width="100%">
			<tr valign="top">
				<td width="60%" valign="top">
					<table class="adminform">
					<tr>
						<th colspan="2">
							<?php echo JText::_( 'Plugin Details' ); ?>
						</th>
					</tr>
					<tr>
						<td width="100" >
							<label for="name"> 
								<?php echo JText::_( 'Name' ); ?>:
							</label>
						</td>
						<td>
							<input class="text_area" type="text" name="name" id="name" size="35" value="<?php echo $row->name; ?>" />
						</td>
					</tr>
					<tr>
						<td valign="top">
							<?php echo JText::_( 'Published' ); ?>:
						</td>
						<td>
							<?php echo $lists['published']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" >
							<label for="folder"> 
								<?php echo JText::_( 'Folder' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['folder']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" >
							<label for="element"> 
								<?php echo JText::_( 'Plugin file' ); ?>:
							</label>
						</td>
						<td>
							<input class="text_area" type="text" name="element" id="element" size="35" value="<?php echo $row->element; ?>" />.php
						</td>
					</tr>
					<tr>
						<td valign="top" >
							<label for="access"> 
								<?php echo JText::_( 'Access Level' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['access']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" >
							<?php echo JText::_( 'Plugin Order' ); ?>:
						</td>
						<td>
							<?php echo $lists['ordering']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" colspan="2">&nbsp;
						</td>
					</tr>
					<tr>
						<td valign="top">
							<?php echo JText::_( 'Description' ); ?>:
						</td>
						<td>
							<?php echo JText::_( $row->description ); ?>
						</td>
					</tr>
					</table>
				</td>
				<td width="40%">
					<table class="adminform">
					<tr>
						<th colspan="2">
							<?php echo JText::_( 'Parameters' ); ?>
						</th>
					</tr>
					<tr>
						<td>
							<?php
							if ( $row->id ) {
								echo $params->render();
							} else {
								echo '<i>'. JText::_( 'No Parameters' ) .'</i>';
							}
							?>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
		</div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="client" value="<?php echo $row->client_id; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>
