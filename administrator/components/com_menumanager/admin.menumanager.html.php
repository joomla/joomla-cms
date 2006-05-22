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
* HTML class for all menumanager component output
* @package Joomla
* @subpackage Menus
*/
class HTML_menumanager
{
	/**
	* Writes a list of the menumanager items
	*/
	function show ( $option, $menus, $page ) 
	{
		global $mainframe;

		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');

		$user		=& $mainframe->getUser();
		$document	=& $mainframe->getDocument();

		$document->addScript('../includes/js/joomla/popup.js');
		$document->addStyleSheet('../includes/js/joomla/popup.css');

		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(task)
		{
			var f = document.adminForm;
			if (task == 'deleteconfirm')
			{
				id = radioGetCheckedValue( f.cid );
				document.popup.show('index3.php?option=com_menumanager&task=deleteconfirm&cid='+id, 700, 500, null);
			}
			else
			{
				submitform(task);
			}
		}

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

		<form action="index2.php" method="post" name="adminForm">

		<div id="pane-document">
			<table class="adminlist">
			<thead>
				<tr>
					<th width="20">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th width="20">
					</th>
					<th class="title" nowrap="nowrap">
						<?php echo JText::_( 'Menu Name' ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo JText::_( 'Menu Items' ); ?>
					</th>
					<th width="10%">
						<?php echo JText::_( 'NUM Published' ); ?>
					</th>
					<th width="15%">
						<?php echo JText::_( 'NUM Unpublished' ); ?>
					</th>
					<th width="15%">
						<?php echo JText::_( 'NUM Trash' ); ?>
					</th>
					<th width="15%">
						<?php echo JText::_( 'NUM Modules' ); ?>
					</th>
					<th width="3%">
						<?php echo JText::_( 'ID' ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<td colspan="13">
					<?php echo $page->getListFooter(); ?>
				</td>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			$i = 0;
			$start = 0;
			if ($page->limitstart)
				$start = $page->limitstart;
			$count = count($menus)-$start;
			if ($page->limit)
				if ($count > $page->limit)
					$count = $page->limit;
			for ($m = $start; $m < $start+$count; $m++) {
				$menu = $menus[$m];
				$link 	= 'index2.php?option=com_menumanager&amp;task=edit&amp;hidemainmenu=1&amp;menu='. $menu->menutype;
				$linkA 	= 'index2.php?option=com_menus&amp;menutype='. $menu->menutype;
				?>
				<tr class="<?php echo "row". $k; ?>">
					<td align="center" width="30">
						<?php echo $i + 1 + $page->limitstart;?>
					</td>
					<td width="30" align="center">
						<input type="radio" id="cb<?php echo $i;?>" name="cid" value="<?php echo $menu->id; ?>" onclick="isChecked(this.checked);" />
					</td>
					<td>
						<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Menu Name' ); ?>">
							<?php echo $menu->menutype; ?></a>
					</td>
					<td align="center">
						<a href="<?php echo $linkA; ?>" title="<?php echo JText::_( 'Edit Menu Items' ); ?>">
							<img src="<?php echo $mainframe->getSiteURL(); ?>/includes/js/ThemeOffice/mainmenu.png" border="0" /></a>
					</td>
					<td align="center">
						<?php
						echo $menu->published;
						?>
					</td>
					<td align="center">
						<?php
						echo $menu->unpublished;
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
					<td align="center">
						<?php
						echo $menu->id;
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
		</div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}


	/**
	* writes a form to take the name of the menu you would like created
	* @param option	display options for the form
	*/
	function edit ( &$row, $option )
	{
		mosCommonHTML::loadOverlib();

		$isNew = ($row->id == 0);
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;

			if (pressbutton == 'savemenu') {
				if ( form.menutype.value == '' ) {
					alert( '<?php echo JText::_( 'Please enter a menu name', true ); ?>' );
					form.menutype.focus();
					return;
				}
				var r = new RegExp("[\']", "i");
				if ( r.exec(form.menutype.value) ) {
					alert( '<?php echo JText::_( 'The menu name cannot contain a \'', true ); ?>' );
					form.menutype.focus();
					return;
				}
				<?php
				if ($isNew) {
					?>
					if ( form.title.value == '' ) {
						alert( '<?php echo JText::_( 'Please enter a module name for your menu', true ); ?>' );
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
			<tr height="45;">
				<td width="100" >
					<label for="menutype">
						<strong><?php echo JText::_( 'Menu Name' ); ?>:</strong>
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="menutype" id="menutype" size="30" maxlength="25" value="<?php echo isset( $row->menutype ) ? $row->menutype : ''; ?>" />
					<?php
					$tip = JText::_( 'TIPNAMEUSEDTOIDENTIFYMENU' );
					echo mosToolTip( $tip );
					?>
				</td>
			</tr>
		<?php
		if ($isNew) {
			?>
			<tr>
				<td width="100"  valign="top">
					<label for="title">
						<strong><?php echo JText::_( 'Module Title' ); ?>:</strong>
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="title" id="title" size="30" value="" />
					<?php
					$tip = JText::_( 'TIPTITLEMAINMENUMODULEREQUIRED' );
					echo mosToolTip( $tip );
					?>
					<br /><br /><br />
					<strong>
					<?php echo JText::_( 'TIPTITLECREATED' ); ?>
					<br /><br />
					<?php echo JText::_( 'DESCPARAMMODULEMANAGER' ); ?>
					</strong>
				</td>
			</tr>
			<?php
		}
		?>
		</table>

		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="savemenu" />
		</form>
	<?php
	}


	/**
	* A delete confirmation page
	* Writes list of the items that have been selected for deletion
	*/
	function showDelete( $option, $type, $items, $modules ) {
		global $mainframe;

		$document	=& $mainframe->getDocument();
		$document->addStyleSheet('templates/_system/css/popup.css');
		$document->setTitle('Confirm Delete Menu Type: ' . $type->menutype );

		?>
	<form action="index2.php" method="post" name="adminForm" target="_top">
		<fieldset>
			<div style="float: right">
				<button type="button" onclick="if (confirm('<?php echo str_replace( "\n", '\n', JText::_( 'WARNWANTDELTHISMENU' ) ); ?>')){ submitbutton('deletemenu');};window.top.document.popup.hide();">
					Delete</button>
				<button type="button" onclick="window.top.document.popup.hide();">
					<?php echo JText::_( 'Cancel' );?></button>
		    </div>
		</fieldset>

		<div>
			<?php echo JText::_( '* This will' ); ?> <strong><font color="#FF0000"><?php echo JText::_( 'Delete' ); ?></font></strong> <?php echo JText::_( 'this Menu,' ); ?> <br /><?php echo JText::_( 'DESCALLMENUITEMS' ); ?>
			<div style="width:30%;float:left">
				<?php
				if ( $modules ) {
					?>
					<strong><?php echo JText::_( 'Module(s) being Deleted' ); ?>:</strong>
					<ol>
					<?php
					foreach ( $modules as $module ) {
						if ($module->id == 0)
						{
							continue;
						}											
						?>
						<li>
						<?php echo $module->title; ?>
						</li>
						<input type="hidden" name="cid[]" value="<?php echo $module->id; ?>" />
						<?php
					}
					?>
					</ol>
					<?php
				}
				?>
			</div>
			<div style="width:30%;float:left">
				<strong><?php echo JText::_( 'Menu Items being Deleted' ); ?>:</strong>
				<ol>
				<?php
				foreach ( $items as $item ) {
					?>
					<li>
						<?php echo $item->name; ?>
					</li>
					<input type="hidden" name="mids[]" value="<?php echo $item->id; ?>" />
					<?php
				}
				?>
				</ol>
			</div>

			<div class="clr"></div>
		</div>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="type" value="<?php echo $type; ?>" />
		<input type="hidden" name="boxchecked" value="1" />
	</form>
		<?php
	}

	/**
	* A delete confirmation page
	* Writes list of the items that have been selected for deletion
	*/
	function showDelete_orig( $option, $type, $items, $modules ) {
		global $mainframe;

		$document	=& $mainframe->getDocument();
		$document->addStyleSheet('templates/_system/css/popup.css');
		$document->setTitle('Confirm Delete Menu Type: ' . $type->menutype );

		?>
		<fieldset>
			<div style="float: right">
				<button type="button" onclick="alert('todo');window.top.document.popup.hide();">
					Delete</button>
				<button type="button" onclick="window.top.document.popup.hide();">
					<?php echo JText::_( 'Cancel' );?></button>
		    </div>
		</fieldset>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<th>
				<?php echo JText::_( 'Delete Menu' ); ?>: <?php echo $type->menutype;?>
			</th>
		</tr>
		</table>

		<br />
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="20%">
				<?php
				if ( $modules ) {
					?>
					<strong><?php echo JText::_( 'Module(s) being Deleted' ); ?>:</strong>
					<ol>
					<?php
					foreach ( $modules as $module ) {
						if ($module->id == 0)
						{
							continue;
						}											
						?>
						<li>
						<?php echo $module->title; ?>
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
			<td  valign="top" width="25%">
				<strong><?php echo JText::_( 'Menu Items being Deleted' ); ?>:</strong>
				<ol>
				<?php
				foreach ( $items as $item ) {
					?>
					<li>
						<?php echo $item->name; ?>
					</li>
					<input type="hidden" name="mids[]" value="<?php echo $item->id; ?>" />
					<?php
				}
				?>
			</ol>
			</td>
			<td>
				<?php echo JText::_( '* This will' ); ?> <strong><font color="#FF0000"><?php echo JText::_( 'Delete' ); ?></font></strong> <?php echo JText::_( 'this Menu,' ); ?> <br /><?php echo JText::_( 'DESCALLMENUITEMS' ); ?>
				<br /><br /><br />
				<div style="border: 1px dotted gray; width: 70px; padding: 10px; margin-left: 100px;">
					<a class="toolbar" href="javascript:if (confirm('<?php echo JText::_( 'WARNWANTDELTHISMENU' ); ?>')){ submitbutton('deletemenu');}" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('remove','','images/delete_f2.png',1);">
						<img name="remove" src="images/delete.png" alt="<?php echo JText::_( 'Delete' ); ?>" border="0" align="middle" />
						&nbsp;<?php echo JText::_( 'Delete' ); ?></a>
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
	?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if (pressbutton == 'copymenu') {
				if ( document.adminForm.menu_name.value == '' ) {
					alert( "<?php echo JText::_( 'Please enter a name for the copy of the Menu', true ); ?>" );
					return;
				} else if ( document.adminForm.module_name.value == '' ) {
					alert( "<?php echo JText::_( 'Please enter a name for the new Module', true ); ?>" );
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
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="30%">
			<strong><?php echo JText::_( 'New Menu Name' ); ?>:</strong>
			<br />
			<input class="inputbox" type="text" name="menu_name" size="30" value="" />
			<br /><br /><br />
			<strong><?php echo JText::_( 'New Module Name' ); ?>:</strong>
			<br />
			<input class="inputbox" type="text" name="module_name" size="30" value="" />
			<br /><br />
			</td>
			<td  valign="top" width="25%">
			<strong>
			<?php echo JText::_( 'Menu being copied' ); ?>:
			</strong>
			<br />
			<font color="#000066">
			<strong>
			<?php echo $type; ?>
			</strong>
			</font>
			<br /><br />
			<strong>
			<?php echo JText::_( 'Menu Items being copied' ); ?>:
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