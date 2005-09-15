<?php
/**
* @version $Id: admin.menus.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage Languages
 */
class menuScreens {
	/**
	 * Static method to create the template object
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $files=null) {

		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );

		return $tmpl;
	}

	/**
	* List languages
	* @param array
	*/
	function view( &$lists, $search, $menutype ) {
		global $_LANG;

		$tmpl =& menuScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'view.html' );

		$tmpl->addObject( 'menus-list', $lists['menus'], 'row_' );

		$tmpl->addVar( 'body2', 'search', $search );

		if ( $lists['trash'] ) {
			$trash = '&nbsp<small>[';
			$trash .= $lists['trash'];
			$trash .= ']</small>';
		} else {
			$trash = '';
		}
		$tmpl->addVar( 'trashMenu', 'trash', $trash );

		$tmpl->addVar( 'body2', 'menu', $menutype );
		if ( $menutype == 'mainmenu') {
			$tmpl->addVar( 'body2', 'warning1', $_LANG->_( 'descMainmenuDelete' ) );
			$tmpl->addVar( 'body2', 'warning2', $_LANG->_( 'descMainmenuHome' ) );
		}

		// temp lists --- these can be done in pat a lot better
		$tmpl->addVar( 'body2', 'lists_state', $lists['state'] );
		$tmpl->addVar( 'body2', 'lists_level', $lists['level'] );
		$tmpl->addVar( 'body2', 'lists_access', $lists['access'] );

		$tmpl->displayParsedTemplate( 'body2' );
	}

	/**
	* List languages
	* @param array
	*/
	function trashView($lists) {
		global $mosConfig_lang;

		$tmpl =& menuScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'viewTrash.html' );

		$tmpl->addObject( 'menus-list', $lists['menus'], 'row_' );

		//$tmpl->addVar( 'body2', 'client', $lists['client'] );

		$tmpl->displayParsedTemplate( 'body2' );
	}
}

/**
* @package Joomla
* @subpackage Menus
*/
class HTML_menusections {

	function showMenusections( &$rows, $pageNav, $search, $menutype, $option, &$lists ) {
		global $my;
  		global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm" id="menusform" class="adminform">

		<?php
		menuScreens::view( $lists, $search, $menutype );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="20">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" />
					</th>
					<th class="title" width="40%">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Menu Item' ), 'name' ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Published' ), 'published' ); ?>
					</th>
					<th colspan="2" width="5%">
						<?php echo $_LANG->_( 'Reorder' ); ?>
					</th>
					<th width="2%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Order' ), 'ordering' ); ?>
					</th>
					<th width="1%">
						<?php mosAdminHTML::saveOrderIcon( $rows ); ?>
					</th>
					<th width="10%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Access' ), 'access' ); ?>
					</th>
					<th nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Itemid' ), 'id' ); ?>
					</th>
					<th width="35%" align="left">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Type' ), 'type' ); ?>
					</th>
					<th nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'CID' ), 'componentid' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="12" class="center">
							<?php echo $pageNav->getPagesLinks(); ?>
						</th>
					</tr>
					<tr>
						<td colspan="12" class="center">
							<?php echo $_LANG->_( 'Display Num' ) ?>
							<?php echo  $pageNav->getLimitBox() ?>
							<?php echo $pageNav->getPagesCounter() ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
			    <?php
				$k = 0;
				$i = 0;
				$n = count( $rows );
				foreach ($rows as $row) {
					$access 	= mosAdminHTML::accessProcessing( $row, $i );
					$checked 	= mosAdminHTML::checkedOutProcessing( $row, $i );
					$published 	= mosAdminHTML::publishedProcessing( $row, $i );
					?>
					<tr class="row<?php echo $k; ?>">
						<td>
							<?php echo $i + 1 + $pageNav->limitstart; ?>
						</td>
						<td>
							<?php echo $checked; ?>
						</td>
						<td nowrap="nowrap">
							<?php
							if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
								echo $row->treename;
							} else {
								$link = 'index2.php?option=com_menus&amp;menutype='. $row->menutype .'&amp;task=edit&amp;id='. $row->id;
								?>
								<a href="<?php echo $link; ?>" title="<?php echo $_LANG->_( 'Edit Menu Item' );?>" class="editlink">
									<?php echo $row->treename; ?>
								</a>
								<?php
							}
							?>
						</td>
						<td width="10%" align="center">
							<?php echo $published; ?>
						</td>
						<td>
							<?php
							if ( $lists['tOrder'] == 'ordering' && ( $lists['tOrderDir'] == 'DESC' )  ) {
								echo $pageNav->orderUpIcon( $i );
							}
							?>
						</td>
						<td>
							<?php
							if ( $lists['tOrder'] == 'ordering' && ( $lists['tOrderDir'] == 'DESC' )  ) {
								echo $pageNav->orderDownIcon( $i, $n );
							}
						?>
						</td>
						<td align="center" colspan="2">
							<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
						</td>
						<td align="center">
							<?php echo $access; ?>
						</td>
						<td align="center">
							<?php echo $row->id; ?>
						</td>
						<td align="left">
							<?php
							echo mosToolTip( $row->descrip, '', 180, 'tooltip.png', $row->type, $row->edit, 'BELOW, LEFT' );
							?>
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
			</fieldset>
		</div>

		<input type="hidden" name="tOrder" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrder_old" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrderDir" value="" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="menutype" value="<?php echo $menutype; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	/**
	* Writes a list of the Trash items
	*/
	function trashShow( $rows, $lists, $pageNav, $option ) {
		global $my;
  		global $_LANG;

		?>
		<form action="index2.php" method="post" name="adminForm" id="trashform" class="adminform">

		<?php
		menuScreens::trashView($lists);
		?>
			<table class="adminlist" id="moslist" width="90%">
			<thead>
				<tr>
					<th width="10">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="10">
						<input type="checkbox" name="toggle1" value=""  />
					</th>
					<th class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Title' ), 'm.name' ); ?>
					</th>
					<th width="20%">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Menu' ), 'm.menutype' ); ?>
					</th>
					<th>
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Type' ), 'm.type' ); ?>
					</th>
					<th width="40">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'ID' ), 'm.id' ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan="8" class="center">
						<?php echo $pageNav->getPagesLinks(); ?>
					</th>
				</tr>
				<tr>
					<td colspan="8" class="center">
						<?php echo $_LANG->_( 'Display Num' ) ?>
						<?php echo  $pageNav->getLimitBox() ?>
						<?php echo $pageNav->getPagesCounter() ?>
					</td>
				</tr>
			</tfoot>

			<tbody>
			<?php
				$k = 0;
				$i = 0;
				$n = count( $rows );
				foreach ( $rows as $row ) {
					?>
					<tr class="<?php echo "row". $k; ?>">
						<td>
							<?php echo $i + 1 + $pageNav->limitstart;?>
						</td>
						<td>
							<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id; ?>"  />
						</td>
						<td nowrap="nowrap">
							<?php
							echo $row->name;
							?>
						</td>
						<td>
							<?php
							echo $row->menutype;
							?>
						</td>
						<td>
							<?php
							echo $row->type;
							?>
						</td>
						<td>
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
				</tbody>
				</table>
			</fieldset>
		</div>

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
	function trashDelete( $option, $cid, $items, $type ) {
	  	global $_LANG;
		?>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td width="3%">
			</td>
			<td align="left" valign="top" width="20%">
				<strong>
					<?php echo $_LANG->_( 'Number of Items' ); ?>:
				</strong>
				<br />
				<font color="#000066">
					<strong>
						<?php echo count( $cid ); ?>
					</strong>
				</font>
				<br /><br />
			</td>
			<td align="left" valign="top" width="25%">
				<strong>
					<?php echo $_LANG->_( 'Items being Deleted' ); ?>:
				</strong>
				<br />
				<ol>
				<?php
				foreach ( $items as $item ) {
					echo "<li>". $item->name ."</li>";
				}
				?>
				</ol>
			</td>
			 <td valign="top">
				<?php echo $_LANG->_( '* This will' ); ?>
				<strong>
					<font color="#FF0000">
						<?php echo $_LANG->_( 'Permanently Delete' ); ?>
					</font>
				</strong>
				<br />
				<?php echo $_LANG->_( 'these Items from the Database *' ); ?>
				<br /><br /><br />
				<div style="border: 1px dotted gray; width: 70px; padding: 10px; margin-left: 50px;">
					<a class="toolbar" href="javascript:if (confirm('<?php echo $_LANG->_( 'WARNWANTDELLISTEDITEMS' ); ?>')){ submitbutton('trashdelete');}" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('remove','','images/delete_f2.png',1);">
						<img name="remove" src="images/delete.png" alt="<?php echo $_LANG->_( 'Delete' ); ?>" border="0" align="middle" />
						<?php echo $_LANG->_( 'Delete' ); ?>
					</a>
				</div>
			</td>
		</tr>
		<tr>
			<td>
			</td>
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
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
	function trashRestore( $option, $cid, $items, $type ) {
	  	global $_LANG;
		?>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td width="3%">
			</td>
			<td align="left" valign="top" width="20%">
				<strong>
					<?php echo $_LANG->_( 'Number of Items' ); ?>:
				</strong>
				<br />
				<font color="#000066">
					<strong>
						<?php echo count( $cid ); ?>
					</strong>
				</font>
				<br /><br />
			</td>
			<td align="left" valign="top" width="25%">
				<strong>
					<?php echo $_LANG->_( 'Items being Restored' ); ?>:
				</strong>
				<br />
				<ol>
					<?php
					foreach ( $items as $item ) {
						echo "<li>". $item->name ."</li>";
					}
					?>
				</ol>
			</td>
			 <td valign="top">
				<?php echo $_LANG->_( '* This will' ); ?>
				<strong>
					<font color="#FF0000">
						<?php echo $_LANG->_( 'Restore' ); ?>
					</font>
				</strong>
				<?php echo $_LANG->_( 'these Items,' ); ?>
				<br />
				<?php echo $_LANG->_( 'TIPWILLBERETURNEDPLACESUNPUBLISHEDITEMS' ); ?>
				<br /><br /><br />
				<div style="border: 1px dotted gray; width: 80px; padding: 10px; margin-left: 50px;">
					<a class="toolbar" href="javascript:if (confirm('Are you sure you want to Restore the listed items?.')){ submitbutton('trashrestore');}" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('restore','','images/restore_f2.png',1);">
						<img name="restore" src="images/restore.png" alt="<?php echo $_LANG->_( 'Restore' ); ?>" border="0" align="middle" />
						&nbsp;<?php echo $_LANG->_( 'Restore' ); ?>
					</a>
				</div>
			</td>
		</tr>
		<tr>
			<td>
			</td>
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
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
	* Displays a selection list for menu item types
	*/
	function addMenuItem( &$cid, $menutype, $option, $types_content, $types_component, $types_link, $types_other ) {
  		global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm" class="adminform" id="menumanagerform">
		<style type="text/css">
		fieldset {
			padding: 10px;
		}
		legend {
			font-weight: bold;
		}
		</style>

		<div align="center" style="width: 100%;">
			<table >
			<tr>
				<td width="50%" valign="top">
					<fieldset>
						<legend><?php echo $_LANG->_( 'Content' ); ?></legend>

						<table class="adminform" id="editpage" style="width: 95%;" align="center">
						<thead>
						<tr>
							<th width="10">
							</th>
							<th>
								<?php echo $_LANG->_( 'Menu Type' ); ?>
							</th>
						</tr>
						</thead>
						<tfoot>
						<tr>
							<th colspan="2">
							</th>
						</tr>
						</tfoot>

						<tbody>
						<?php
						$k 		= 0;
						$count 	= count( $types_content );
							for ( $i=0; $i < $count; $i++ ) {
							$row = &$types_content[$i];

							$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type;
							?>
							<tr class="row<?php echo $k; ?>">
								<td width="20">
									<input type="radio" id="cb<?php echo $i; ?>" name="type" value="<?php echo $row->type; ?>" />
								</td>
								<td>
									<?php
									echo mosToolTip( stripslashes( $row->descrip ), stripslashes( $row->name ), 250, '', $row->name, $link, 'BELOW, RIGHT' );
									?>
								</td>
							</tr>
							<?php
							$k = 1 - $k;
						}
						?>
						</tbody>
						</table>
					</fieldset>

					<fieldset>
						<legend><?php echo $_LANG->_( 'Miscellaneous' ); ?></legend>

						<table class="adminform" id="editpage" style="width: 95%;" align="center">
						<thead>
						<tr>
							<th width="10">
							</th>
							<th>
								<?php echo $_LANG->_( 'Menu Type' ); ?>
							</th>
						</tr>
						</thead>
						<tfoot>
						<tr>
							<th colspan="2">
							</th>
						</tr>
						</tfoot>

						<tbody>
						<?php
						$k 		= 0;
						$count 	= count( $types_other );
							for ( $i=0; $i < $count; $i++ ) {
							$row = &$types_other[$i];

							$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type;
							?>
							<tr class="row<?php echo $k; ?>">
								<td width="20">
									<input type="radio" id="cb<?php echo $i; ?>" name="type" value="<?php echo $row->type; ?>"  />
								</td>
								<td>
									<?php
									echo mosToolTip( stripslashes( $row->descrip ), stripslashes( $row->name ), 250, '', $row->name, $link, 'BELOW, RIGHT' );
									?>
								</td>
							</tr>
							<?php
							$k = 1 - $k;
						}
						?>
						</tbody>
						</table>
					</fieldset>
					<?php echo $_LANG->_( 'DESCSOMEMENUAPPEARINMORETHATONEGROUPING' ); ?>
				</td>
				<td width="50%" valign="top">
					<fieldset>
						<legend><?php echo $_LANG->_( 'Components' ); ?></legend>

						<table class="adminform" id="editpage" style="width: 95%;" align="center">
						<thead>
						<tr>
							<th width="10">
							</th>
							<th>
								<?php echo $_LANG->_( 'Menu Type' ); ?>
							</th>
						</tr>
						</thead>
						<tfoot>
						<tr>
							<th colspan="2">
							</th>
						</tr>
						</tfoot>

						<tbody>
						<?php
						$k 		= 0;
						$count 	= count( $types_component );
							for ( $i=0; $i < $count; $i++ ) {
							$row = &$types_component[$i];

							$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type;
							?>
							<tr class="row<?php echo $k; ?>">
								<td width="20">
									<input type="radio" id="cb<?php echo $i; ?>" name="type" value="<?php echo $row->type; ?>"  />
								</td>
								<td>
									<?php
									echo mosToolTip( stripslashes( $row->descrip ), stripslashes( $row->name ), 250, '', $row->name, $link, 'LEFT' );
									?>
								</td>
							</tr>
							<?php
							$k = 1 - $k;
						}
						?>
						</tbody>
						</table>
					</fieldset>

					<fieldset>
						<legend><?php echo $_LANG->_( 'Links' ); ?></legend>

						<table class="adminform" id="editpage" style="width: 95%;" align="center">
						<thead>
						<tr>
							<th width="10">
							</th>
							<th>
								<?php echo $_LANG->_( 'Menu Type' ); ?>
							</th>
						</tr>
						</thead>
						<tfoot>
						<tr>
							<th colspan="2">
							</th>
						</tr>
						</tfoot>

						<tbody>
						<?php
						$k 		= 0;
						$count 	= count( $types_link );
							for ( $i=0; $i < $count; $i++ ) {
							$row = &$types_link[$i];

							$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type;
							?>
							<tr class="row<?php echo $k; ?>">
								<td width="20">
									<input type="radio" id="cb<?php echo $i; ?>" name="type" value="<?php echo $row->type; ?>"  />
								</td>
								<td>
									<?php
									echo mosToolTip( stripslashes( $row->descrip ), stripslashes( $row->name ), 250, '', $row->name, $link, 'LEFT' );
									?>
								</td>
							</tr>
							<?php
							$k = 1 - $k;
						}
						?>
						</tbody>
						</table>
					</fieldset>
				</td>
			</tr>
			</table>
		</div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="menutype" value="<?php echo $menutype; ?>" />
		<input type="hidden" name="task" value="edit" />
		</form>
		<?php
	}


	/**
	* Form to select Menu to move menu item(s) to
	*/
	function moveMenu( $option, $cid, $MenuList, $items, $menutype  ) {
  	global $_LANG;
		?>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td width="3%">
			</td>
			<td align="left" valign="top" width="30%">
				<strong>
					<?php echo $_LANG->_( 'Move to Menu' ); ?>:
				</strong>
				<br />
				<?php echo $MenuList; ?>
				<br /><br />
			</td>
			<td align="left" valign="top">
				<strong>
					<?php echo $_LANG->_( 'Menu Items being moved' ); ?>:
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

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
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
  	global $_LANG;
		?>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td width="3%">
			</td>
			<td align="left" valign="top" width="30%">
				<strong>
					<?php echo $_LANG->_( 'Copy to Menu' ); ?>:
				</strong>
				<br />
				<?php echo $MenuList; ?>
				<br /><br />
			</td>
			<td align="left" valign="top">
				<strong>
					<?php echo $_LANG->_( 'Menu Items being copied' ); ?>:
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

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
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