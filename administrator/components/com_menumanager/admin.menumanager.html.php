<?php
/**
* @version $Id: admin.menumanager.html.php 137 2005-09-12 10:21:17Z eddieajau $
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
 * @subpackage Menu Manager
 */
class menumanagerScreens {
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
	function view( &$lists ) {
		global $mosConfig_lang;

		$tmpl =& menumanagerScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'view.html' );

		$tmpl->addObject( 'menus-list', $lists['menus'], 'row_' );

		if ( $lists['trash'] ) {
			$trash = '&nbsp<small>[';
			$trash .= $lists['trash'];
			$trash .= ']</small>';
		} else {
			$trash = '';
		}
		$tmpl->addVar( 'trashMenu', 'trash', $trash );

		$tmpl->displayParsedTemplate( 'body2' );
	}
}

/**
* HTML class for all menumanager component output
* @package Joomla
* @subpackage Menus
*/
class HTML_menumanager {
	/**
	* Writes a list of the menumanager items
	*/
	function show ( $option, &$menus, &$pageNav, &$lists ) {
		global $mosConfig_live_site;
  		global $_LANG;

  		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function menu_listItemTask( id, task, option ) {
			var f = document.adminForm;
			cb = eval( 'f.' + id );
			if (cb) {
				cb.checked = true;
				submitbutton(task);
			}
			return false;
		}
		</script>

		<form action="index2.php" method="post" name="adminForm" id="menumanagerform" class="adminform">

		<?php
		menumanagerScreens::view( $lists );
		?>
		<div id="datacell">
			<fieldset>
				<legend>
					<?php echo $_LANG->_( 'Menus' ); ?>
				</legend>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="10">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="10">
					</th>
					<th class="title" nowrap="nowrap">
						<?php echo $_LANG->_( 'Menu Name' ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo $_LANG->_( 'Menu Items' ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo $_LANG->_( 'Num Published' ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo $_LANG->_( 'Num Trash' ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo $_LANG->_( 'Num Modules' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="13" class="center">
							<?php echo $pageNav->getPagesLinks(); ?>
						</th>
					</tr>
					<tr>
						<td colspan="13" class="center">
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
				$start = 0;
				if ($pageNav->limitstart) {
					$start = $pageNav->limitstart;
				}
				$count = count($menus)-$start;
				if ($pageNav->limit) {
					if ($count > $pageNav->limit) {
						$count = $pageNav->limit;
					}
				}
				for ($m = $start; $m < $start+$count; $m++) {
					$menu = $menus[$m];
					$link 	= 'index2.php?option=com_menumanager&amp;task=edit&amp;menu='. $menu->type;
					$linkA 	= 'index2.php?option=com_menus&amp;menutype='. $menu->type;
					?>
					<tr class="<?php echo "row". $k; ?>">
						<td align="center" width="10">
							<?php echo $i + 1 + $pageNav->limitstart;?>
						</td>
						<td align="center" width="10">
							<input type="radio" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $menu->type; ?>" <?php echo ( $menu->type == 'mainmenu' ? 'disabled="disabled"' : '' )?> />
						</td>
						<td>
							<?php
							if ( $menu->type <> 'mainmenu' ) {
								?>
								<a href="<?php echo $link; ?>" title="<?php echo $_LANG->_( 'Edit Menu Name' ); ?>" class="editlink">
									<?php echo $menu->type; ?>
								</a>
								<?php
							} else {
								$txt = $_LANG->_( 'errorEditMainMenu' );
								echo mosToolTip( $txt, '', 180, 'tooltip.png', $menu->type );
							}
							?>
						</td>
						<td align="center">
							<a href="<?php echo $linkA; ?>" title="<?php echo $_LANG->_( 'Edit Menu Items' ); ?>">
								<img src="<?php echo $mosConfig_live_site; ?>/includes/js/ThemeOffice/mainmenu.png" border="0"/>
							</a>
						</td>
						<td align="center">
							<?php
							echo $menu->published;
							?>
						</td>
						<td align="center">
							<?php
							echo $menu->trash;
							?>
						</td>
						<td align="center">
							<?php
							echo $menu->modules;
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

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}


	/**
	* writes a form to take the name of the menu you would like created
	* @param option	display options for the form
	*/
	function edit ( &$row, $option ) {
		global $mosConfig_live_site;
  		global $_LANG;

		$new = $row->menutype ? 0 : 1;

		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;

			if (pressbutton == 'savemenu') {
				if ( form.menutype.value == '' ) {
					alert( '<?php echo $_LANG->_( 'Please enter a menu name' ); ?>' );
					form.menutype.focus();
					return;
				}
				<?php
				if ( $new ) {
					?>
					if ( form.title.value == '' ) {
						alert( '<?php echo $_LANG->_( 'Please enter a module name for your menu' ); ?>' );
						form.title.focus();
						return;
					}
					<?php
				}
				?>
				submitform( 'savemenu' );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">

		<table class="adminform">
		<tr>
			<td width="100" align="left">
			<strong><?php echo $_LANG->_( 'Menu Name' ); ?>:</strong>
			</td>
			<td>
			<input class="inputbox" type="text" name="menutype" size="30" value="<?php echo isset( $row->menutype ) ? $row->menutype : ''; ?>" />
			<?php
			$tip = $_LANG->_( 'TIPNAMEUSEDBYMAMBOTOIDENTIFYMENU' );
			echo mosToolTip( $tip );
			?>
			</td>
		</tr>
		<?php
		if ( $new ) {
			?>
			<tr>
				<td width="100" align="left" valign="top">
				<strong><?php echo $_LANG->_( 'Module Title' ); ?>:</strong>
				</td>
				<td>
				<input class="inputbox" type="text" name="title" size="30" value="<?php echo $row->title ? $row->title : '';?>" />
				<?php
				$tip = $_LANG->_( 'TIPTITLEMAINMENUMODULEREQUIRED' );
				echo mosToolTip( $tip );
				?>
				<br/><br/><br/>
				<strong>
				<?php echo $_LANG->_( 'DESCNEWMAINMENUMODULEWILLAUTOMATICALLYBECREATED' ); ?>
				<br/><br/>
				<?php echo $_LANG->_( "DESCPARAMMODULEMANAGER" ); ?>
				</strong>
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
			<td colspan="2">
			</td>
		</tr>
		</table>
		<br /><br />

		<?php
		if ( $new ) {
			?>
			<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
			<input type="hidden" name="iscore" value="<?php echo $row->iscore; ?>" />
			<input type="hidden" name="published" value="<?php echo $row->published; ?>" />
			<input type="hidden" name="position" value="<?php echo $row->position; ?>" />
			<input type="hidden" name="module" value="mod_mainmenu" />
			<input type="hidden" name="params" value="<?php echo $row->params; ?>" />
			<?php
		}
		?>

		<input type="hidden" name="new" value="<?php echo $new; ?>" />
		<input type="hidden" name="old_menutype" value="<?php echo $row->menutype; ?>" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="savemenu" />
		<input type="hidden" name="boxchecked" value="0" />
		</form>
		<?php
	}


	/**
	* A delete confirmation page
	* Writes list of the items that have been selected for deletion
	*/
	function showDelete( $option, $type, $items, $modules ) {
		global $_LANG;
		?>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td align="left" valign="top" width="20%">
			<?php
			if ( $modules ) {
				?>
				<strong><?php echo $_LANG->_( 'Module(s) being Deleted' ); ?>:</strong>
				<ol>
				<?php
				foreach ( $modules as $module ) {
					?>
					<li>
					<font color="#000066">
					<strong>
					<?php echo $module->title; ?>
					</strong>
					</font>
					</li>
					<input type="hidden" name="cid[]" value="<?php echo $module->id; ?>" />
					<?php
				}
				?>
				</ol>
				<?php
			}
			?>
			</td>
			<td align="left" valign="top" width="25%">
			<strong><?php echo $_LANG->_( 'Menu Items being Deleted' ); ?>:</strong>
			<br />
			<ol>
			<?php
			if ( $items ) {
				foreach ( $items as $item ) {
					?>
					<li>
					<font color="#000066">
					<?php echo $item->name; ?>
					</font>
					</li>
					<input type="hidden" name="mids[]" value="<?php echo $item->id; ?>" />
					<?php
				}
			}
			?>
			</ol>
			</td>
			<td><?php echo $_LANG->_( '* This will' ); ?>
			 <strong><font color="#FF0000"><?php echo $_LANG->_( 'Delete' ); ?></font></strong> <?php echo $_LANG->_( 'this Menu,' ); ?> <br /><?php echo $_LANG->_( 'DESCALLMENUITEMS' ); ?>
			<br /><br /><br />
			<div style="border: 1px dotted gray; width: 70px; padding: 10px; margin-left: 100px;">
			<a class="toolbar" href="javascript:if (confirm('<?php echo $_LANG->_( 'WARNWANTDELTHISMENU' ); ?>')){ submitbutton('deletemenu');}" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('remove','','images/delete_f2.png',1);">
			<img name="remove" src="images/delete.png" alt="Delete" border="0" align="middle" />
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
		<input type="hidden" name="type" value="<?php echo $type; ?>" />
		<input type="hidden" name="boxchecked" value="1" />
		</form>
		<?php
	}


	/**
	* A copy confirmation page
	* Writes list of the items that have been selected for copy
	*/
	function showCopy( $option, $type, $items ) {
	  	global $_LANG;
	?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if (pressbutton == 'copymenu') {
				if ( document.adminForm.menu_name.value == '' ) {
					alert( '<?php echo $_LANG->_( 'Please enter a name for the copy of the Menu' ); ?>' );
					return;
				} else if ( document.adminForm.module_name.value == '' ) {
					alert( '<?php echo $_LANG->_( 'Please enter a name for the new Module' ); ?>' );
					return;
				} else {
					submitform( 'copymenu' );
				}
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td align="left" valign="top" width="30%">
			<strong><?php echo $_LANG->_( 'New Menu Name' ); ?>:</strong>
			<br />
			<input class="inputbox" type="text" name="menu_name" size="30" value="" />
			<br /><br /><br />
			<strong><?php echo $_LANG->_( 'New Module Name' ); ?>:</strong>
			<br />
			<input class="inputbox" type="text" name="module_name" size="30" value="" />
			<br /><br />
			</td>
			<td align="left" valign="top" width="25%">
			<strong>
			<?php echo $_LANG->_( 'Menu being copied' ); ?>:
			</strong>
			<br />
			<font color="#000066">
			<strong>
			<?php echo $type; ?>
			</strong>
			</font>
			<br /><br />
			<strong>
			<?php echo $_LANG->_( 'Menu Items being copied' ); ?>:
			</strong>
			<br />
			<ol>
			<?php
			foreach ( $items as $item ) {
				?>
				<li>
				<font color="#000066">
				<?php echo $item->name; ?>
				</font>
				</li>
				<input type="hidden" name="mids[]" value="<?php echo $item->id; ?>" />
				<?php
			}
			?>
			</ol>
			</td>
			<td valign="top">
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="type" value="<?php echo $type; ?>" />
		</form>
		<?php
	}
}
?>