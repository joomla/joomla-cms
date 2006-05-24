<?php
/**
* @version $Id: admin.menumanager.html.php 3593 2006-05-22 15:48:29Z Jinx $
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

jimport( 'joomla.application.view' );

/**
 * A delete confirmation page. Writes list of the items that have been selected
 * for deletion
 * @package Joomla
 * @subpackage Menus
 */
class JMenuManagerListView extends JView
{
	/**
	 * Toolbar for view
	 */
	function setToolbar() {
		JMenuBar::title( JText::_( 'Menu Manager' ), 'menu.png' );
		JMenuBar::customX( 'copyconfirm', 'copy.png', 'copy_f2.png', 'Copy', true );
		JMenuBar::customX( 'deleteconfirm', 'delete.png', 'delete_f2.png', 'Delete', true );
		JMenuBar::editListX();
		JMenuBar::addNewX();
		JMenuBar::help( 'screen.menumanager' );
	}

	/**
	 * Display the view
	 */
	function display( $menus, $page ) 
	{
		$this->setToolbar();

		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');

		$controller	= &$this->getController();
		$mainframe	= &$controller->getApplication();
		$document	= &$this->getDocument();

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
				id = radioGetCheckedValue( f.id );
				document.popup.show('index3.php?option=com_menumanager&task=deleteconfirm&id='+id, 700, 500, null);
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
		
			<table class="adminlist">
			<thead>
				<tr>
					<th width="20">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th width="20">
					</th>
					<th class="title" nowrap="nowrap">
						<?php echo JText::_( 'Title' ); ?>
					</th>
					<th class="title" nowrap="nowrap">
						<?php echo JText::_( 'Type' ); ?>
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
				$menu = &$menus[$m];
				$link 	= 'index2.php?option=com_menumanager&amp;task=edit&amp;hidemainmenu=1&amp;id='. $menu->id;
				$linkA 	= 'index2.php?option=com_menus&amp;menutype='. $menu->menutype;
				?>
				<tr class="<?php echo "row". $k; ?>">
					<td align="center" width="30">
						<?php echo $i + 1 + $page->limitstart;?>
					</td>
					<td width="30" align="center">
						<input type="radio" id="cb<?php echo $i;?>" name="id" value="<?php echo $menu->id; ?>" onclick="isChecked(this.checked);" />
					</td>
					<td>
						<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Menu Name' ); ?>">
							<?php echo $menu->title; ?></a>
					</td>
					<td>
						<?php echo $menu->menutype; ?>
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

		<input type="hidden" name="option" value="com_menumanager" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}
}

/**
 * Writes a form to take the name of the menu you would like created
 * @package Joomla
 * @subpackage Menus
 */
class JMenuManagerEditView extends JView
{
	/**
	 * Toolbar for view
	 * @param boolean
	 */
	function setToolbar( $isNew ) {
		$text = ( $isNew ? JText::_( 'Edit' ) : JText::_( 'New' ) );
		JMenuBar::title( JText::_( 'Menu Details' ).': <small><small>[ '. $text.' ]</small></small>', 'menu.png' );
		JMenuBar::custom( 'savemenu', 'save.png', 'save_f2.png', 'Save', false );
		JMenuBar::cancel();
		JMenuBar::help( 'screen.menumanager.new' );
	}

	/**
	 * Display the view
	 */
	function display()
	{
		$model		= &$this->getModel();
		$row		= $model->getTable();

		mosCommonHTML::loadOverlib();

		$isNew = ($row->id == 0);
		$this->setToolbar( $isNew );
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
			<tr>
				<td width="100" >
					<label for="menutype">
						<strong><?php echo JText::_( 'Type' ); ?>:</strong>
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="menutype" id="menutype" size="30" maxlength="25" value="<?php echo $row->menutype; ?>" />
					<?php
					$tip = JText::_( 'TIPNAMEUSEDTOIDENTIFYMENU' );
					echo mosToolTip( $tip );
					?>
				</td>
			</tr>
			<tr>
				<td width="100" >
					<label for="title">
						<strong><?php echo JText::_( 'Title' ); ?>:</strong>
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="title" id="title" size="30" maxlength="255" value="<?php echo $row->title; ?>" />
					<?php
					$tip = JText::_( 'A proper title for the Menu Type' );
					echo mosToolTip( $tip );
					?>
				</td>
			</tr>
			<tr>
				<td width="100" >
					<label for="description">
						<strong><?php echo JText::_( 'Description' ); ?>:</strong>
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="description" id="description" size="30" maxlength="255" value="<?php echo $row->description; ?>" />
					<?php
					$tip = JText::_( 'A description for the Menu Type' );
					echo mosToolTip( $tip );
					?>
				</td>
			</tr>
		<?php
		if ($isNew) {
			?>
			<tr>
				<td width="100"  valign="top">
					<label for="module_title">
						<strong><?php echo JText::_( 'Module Title' ); ?>:</strong>
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="module_title" id="module_title" size="30" value="" />
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
		<input type="hidden" name="option" value="com_menumanager" />
		<input type="hidden" name="task" value="savemenu" />
		</form>
	<?php
	}
}


/**
 * A delete confirmation page. Writes list of the items that have been selected
 * for deletion
 * @package Joomla
 * @subpackage Menus
 */
class JMenuManagerConfirmDeleteView extends JView
{
	/**
	 * Display the view
	 */
	function display()
	{
		$document	= &$this->getDocument();
		$model		= &$this->getModel();
		$table		= $model->getTable();

		$document->addStyleSheet('templates/_system/css/popup.css');
		$document->setTitle('Confirm Delete Menu Type: ' . $table->menutype );

		// view data
		$modules	= $model->getModules();
		$menuItems	= $model->getMenuItems();
		?>
	<form action="index2.php" method="post" name="adminForm" target="_top">
		<fieldset>
			<div style="float: right">
				<button type="button" onclick="if (confirm('<?php echo str_replace( "\n", '\n', JText::_( 'WARNWANTDELTHISMENU' ) ); ?>')){ submitbutton('delete');};window.top.document.popup.hide();">
					Delete</button>
				<button type="button" onclick="window.top.document.popup.hide();">
					<?php echo JText::_( 'Cancel' );?></button>
		    </div>
			<?php echo JText::_( '* This will Delete this Menu,' ); ?> <?php echo JText::_( 'DESCALLMENUITEMS' ); ?>
		</fieldset>

		<div>
			<div style="width:30%;float:left">
				<?php
				if ( $modules ) {
					?>
					<strong><?php echo JText::_( 'Module(s) being Deleted' ); ?>:</strong>
					<ol>
					<?php
					foreach ( $modules as $module )
					{
						?>
						<li>
							<?php echo $module->title; ?>
						</li>
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
				foreach ($menuItems as $item)
				{
					?>
					<li>
						<?php echo $item->name; ?>
					</li>
					<?php
				}
				?>
				</ol>
			</div>

			<div class="clr"></div>
		</div>

		<input type="hidden" name="id" value="<?php echo $table->id; ?>" />

		<input type="hidden" name="option" value="com_menumanager" />
		<input type="hidden" name="task" value="" />
	</form>
		<?php
	}
}

/**
 * A  copy confirmation page Writes list of the items that have been selected
 * for copy
 * @package Joomla
 * @subpackage Menus
 */
class JMenuManagerConfirmCopyView extends JView
{
	/**
	 * Toolbar for view
	 */
	function setToolbar() {
		JMenuBar::title(  JText::_( 'Copy Menu Items' ) );
		JMenuBar::custom( 'copymenu', 'copy.png', 'copy_f2.png', 'Copy', false );
		JMenuBar::cancel();
		JMenuBar::help( 'screen.menumanager.copy' );
	}

	/**
	 * Display the view
	 */
	function display( $type, $items ) {
		$this->setToolbar();
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

		<input type="hidden" name="option" value="com_menumanager" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="type" value="<?php echo $type; ?>" />
		</form>
		<?php
	}
}
?>