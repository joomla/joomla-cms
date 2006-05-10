<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Contact
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
* @subpackage Contact
*/
class HTML_contact 
{
	function showContacts( &$rows, &$pageNav, $option, &$lists ) 
	{
		global $my;
		
		//Ordering allowed ?
		$ordering = ($lists['order'] == 'cd.ordering');

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_contact" method="post" name="adminForm">

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
				echo $lists['catid'];
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>

		<div id="tablecell">
			<table class="adminlist">
			<tr>
				<th width="10">
					<?php echo JText::_( 'Num' ); ?>
				</th>
				<th width="10" class="title">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" />
				</th>
				<th class="title">
					<?php mosCommonHTML::tableOrdering( 'Name', 'cd.name', $lists ); ?>
				</th>
				<th width="5%" class="title" nowrap="nowrap">
					<?php mosCommonHTML::tableOrdering( 'Published', 'cd.published', $lists ); ?>
				</th>
				<th colspan="2" nowrap="nowrap" width="5%">
					<?php echo JText::_( 'Reorder' ); ?>
				</th>
				<th width="2%" nowrap="nowrap">
					<a href="javascript:tableOrdering('cd.ordering','ASC');" title="<?php echo JText::_( 'Order by' ); ?> <?php echo JText::_( 'Order' ); ?>">
						<?php echo JText::_( 'Order' );?>
					</a>	
	 			</th>
				<th width="1%">
					<?php mosCommonHTML::saveorderButton( $rows ); ?>
				</th>
				<th width="7%" nowrap="nowrap">
					<?php mosCommonHTML::tableOrdering( 'Access', 'cd.access', $lists ); ?>
				</th>
				<th width="5%" nowrap="nowrap">
					<?php mosCommonHTML::tableOrdering( 'ID', 'cd.id', $lists ); ?>
				</th>
				<th width="15%" class="title">
					<?php mosCommonHTML::tableOrdering( 'Category', 'category', $lists ); ?>
				</th>
				<th class="title" nowrap="nowrap" width="15%">
					<?php mosCommonHTML::tableOrdering( 'Linked to User', 'user', $lists ); ?>
				</th>
			</tr>
			<?php
			$k = 0;
			for ($i=0, $n=count($rows); $i < $n; $i++) {
				$row = $rows[$i];

				$link 		= ampReplace( 'index2.php?option=com_contact&task=editA&hidemainmenu=1&id='. $row->id );

				$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
				$access 	= mosCommonHTML::AccessProcessing( $row, $i );
				$published 	= mosCommonHTML::PublishedProcessing( $row, $i );

				$row->cat_link 	= ampReplace( 'index2.php?option=com_categories&section=com_contact_details&task=editA&hidemainmenu=1&id='. $row->catid );
				$row->user_link	= ampReplace( 'index2.php?option=com_users&task=editA&hidemainmenu=1&id='. $row->user_id );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
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
						<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Contact' ); ?>">
							<?php echo $row->name; ?></a>
						<?php
					}
					?>
					</td>
					<td align="center">
						<?php echo $published;?>
					</td>
					<td>
						<?php echo $pageNav->orderUpIcon( $i, ( $row->catid == @$rows[$i-1]->catid ), 'orderup', 'Move Up', $ordering ); ?>
					</td>
					<td>
						<?php echo $pageNav->orderDownIcon( $i, $n, ( $row->catid == @$rows[$i+1]->catid ), 'orderdown', 'Move Down', $ordering ); ?>
					</td>
					<td align="center" colspan="2">
						<?php $disabled = $ordering ?  '' : '"disabled=disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
					</td>
					<td align="center">
						<?php echo $access;?>
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
					<td>
						<a href="<?php echo $row->cat_link; ?>" title="<?php echo JText::_( 'Edit Category' ); ?>">
							<?php echo $row->category; ?></a>
					</td>
					<td>
						<a href="<?php echo $row->user_link; ?>" title="<?php echo JText::_( 'Edit User' ); ?>">
							<?php echo $row->user; ?></a>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</table>
			<?php echo $pageNav->getListFooter(); ?>
		</div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}


	function editContact( &$row, &$lists, $option, &$params ) {
		if ($row->image == '') {
			$row->image = 'blank.png';
		}

		mosCommonHTML::loadOverlib();

		$tabs = new mosTabs(0);

		mosMakeHtmlSafe( $row, ENT_QUOTES, 'misc' );
		?>
		<script language="javascript" type="text/javascript">
		<!--
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if ( form.name.value == "" ) {
				alert( "<?php echo JText::_( 'You must provide a name.', true ); ?>" );
			} else if ( form.catid.value == 0 ) {
				alert( "<?php echo JText::_( 'Please select a Category.', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		//-->
		</script>

		<form action="index2.php" method="post" name="adminForm">

		<div id="editcell">
			<table width="100%">
			<tr>
				<td width="60%" valign="top">
					<table width="100%" class="adminform">
					<tr>
						<th colspan="2">
							<?php echo JText::_( 'Contact Details' ); ?>
						</th>
					</tr>
					<tr>
						<td width="20%" align="right">
							<label>
								<?php echo JText::_( 'ID' ); ?>:
							</label>
						</td>
						<td width="40%">
							<strong><?php echo $row->id;?></strong>
						</td>
					</tr>
					<tr>
						<td width="20%" align="right">
							<label for="catid">
								<?php echo JText::_( 'Category' ); ?>:
							</label>
						</td>
						<td width="40%">
							<?php echo $lists['catid'];?>
						</td>
					</tr>
					<tr>
						<td width="20%" align="right">
							<label for="user_id">
								<?php echo JText::_( 'Linked to User' ); ?>:
							</label>
						</td>
						<td >
							<?php echo $lists['user_id'];?>
						</td>
					</tr>
					<tr>
						<td width="20%" align="right">
							<label for="name">
								<?php echo JText::_( 'Name' ); ?>:
							</label>
						</td>
						<td >
							<input class="inputbox" type="text" name="name" id="name" size="50" maxlength="255" value="<?php echo $row->name; ?>" />
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="con_position">
								<?php echo JText::_( 'Contact\'s Position' ); ?>:
							</label>
						</td>
						<td>
 							<textarea name="con_position" id="con_position" rows="2" cols="50" class="inputbox"><?php echo $row->con_position; ?></textarea>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="email_to">
								<?php echo JText::_( 'E-mail' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="email_to" id="email_to" size="50" maxlength="255" value="<?php echo $row->email_to; ?>" />
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="address">
								<?php echo JText::_( 'Street Address' ); ?>:
							</label>
						</td>
						<td>
 							<textarea name="address" id="address" rows="3" cols="50" class="inputbox"><?php echo $row->address; ?></textarea>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="suburb">
								<?php echo JText::_( 'Town/Suburb' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="suburb" id="suburb" size="50" maxlength="100" value="<?php echo $row->suburb;?>" />
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="state">
								<?php echo JText::_( 'State/County' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="state" id="state" size="50" maxlength="100" value="<?php echo $row->state;?>" />
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="country">
								<?php echo JText::_( 'Country' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="country" id="country" size="50" maxlength="100" value="<?php echo $row->country;?>" />
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="postcode">
								<?php echo JText::_( 'Postal Code/ZIP' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="postcode" id="postcode" size="50" maxlength="100" value="<?php echo $row->postcode; ?>" />
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="telephone">
								<?php echo JText::_( 'Telephone' ); ?>:
							</label>
						</td>
						<td>
 							<textarea name="telephone" id="telephone" rows="2" cols="50" class="inputbox"><?php echo $row->telephone; ?></textarea>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="mobile">
								<?php echo JText::_( 'Mobile' ); ?>:
							</label>
						</td>
						<td>
 							<textarea name="mobile" id="mobile" rows="2" cols="50" class="inputbox"><?php echo $row->mobile; ?></textarea>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="fax">
								<?php echo JText::_( 'Fax' ); ?>:
							</label>
						</td>
						<td>
 							<textarea name="fax" id="fax" rows="2" cols="50" class="inputbox"><?php echo $row->fax; ?></textarea>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="webpage">
								<?php echo JText::_( 'Webpage' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="webpage" id="webpage" size="50" maxlength="255" value="<?php echo $row->webpage; ?>" />
						</td>
					</tr>
					<tr>
						<td align="right" valign="top">
							<label for="misc">
								<?php echo JText::_( 'Miscellaneous Info' ); ?>:
							</label>
						</td>
						<td>
							<textarea name="misc" id="misc" rows="5" cols="50" class="inputbox"><?php echo $row->misc; ?></textarea>
						</td>
					</tr>
					</table>
				</td>
				<td width="40%" valign="top">
					<?php
					$title = JText::_( 'Info' );
					$tabs->startPane("content-pane");
					$tabs->startTab( $title, "info-page" );
					?>
						<table width="100%" class="adminform">
						<tr>
							<td valign="top" align="right"  width="100">
								<?php echo JText::_( 'Published' ); ?>:
							</td>
							<td>
								<?php echo $lists['published']; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<label for="ordering">
									<?php echo JText::_( 'Ordering' ); ?>:
								</label>
							</td>
							<td>
								<?php echo $lists['ordering']; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<label for="access">
									<?php echo JText::_( 'Access' ); ?>:
								</label>
							</td>
							<td>
								<?php echo $lists['access']; ?>
							</td>
						</tr>
						</table>

						<br />

						<table width="100%" class="adminform">
						<tr>
							<td  width="100">
								<label for="image">
									<?php echo JText::_( 'Image' ); ?>:
								</label>
							</td>
							<td >
								<?php echo $lists['image']; ?>
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<script language="javascript" type="text/javascript">
								if (document.forms[0].image.options.value!=''){
									jsimg='../images/stories/' + getSelectedValue( 'adminForm', 'image' );
								} else {
									jsimg='../images/M_images/blank.png';
								}
								document.write('<img src=' + jsimg + ' name="imagelib" width="100" height="100" border="2" alt="<?php echo JText::_( 'Preview' ); ?>" />');
								</script>
							</td>
						</tr>
						</table>

					<?php
					$title = JText::_( 'Parameters' );
					$tabs->endTab();
					$tabs->startTab( $title, "params-page" );
					?>

						<table class="adminform">
						<tr>
							<td>
								<?php echo JText::_( 'DESCPARAMWHENCLICKCONTAC' ); ?>
								<br /><br />
							</td>
						</tr>
						<tr>
							<td>
								<?php echo $params->render();?>
							</td>
						</tr>
						</table>

					<?php
					$tabs->endTab();
					$tabs->endPane();
					?>
				</td>
			</tr>
			</table>
		</div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>