<?php
/**
* @version $Id: admin.menus.html.php 3593 2006-05-22 15:48:29Z Jinx $
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

jimport('joomla.application.view');

/**
 * @package Joomla
 * @subpackage Menus
 * @static
 * @since 1.5
 */
class JMenuNewWizardView extends JView
{
	function display()
	{
		$document = &$this->getDocument();

		$document->addStyleSheet('components/com_menumanager/includes/popup.css');
		$document->setTitle('New Menu Wizard');
		
		$menuType	= JRequest::getVar( 'menutype' );

		$model		= &$this->getModel();
		$menuTypes 	= $model->getMenuTypelist();
		$components	= $model->getComponentList();
?>
	<style type="text/css">
	._type {
		font-weight: bold;
	}
	</style>
	<form action="index2.php" method="post" name="adminForm" target="_top">
		<fieldset>
			<div style="float: right">
				<button type="button" onclick="this.form.submit();window.top.document.popup.hide();">
					<?php echo JText::_('Next');?></button>
		    </div>
		    Click Next to create the menu item.
		</fieldset>

		<fieldset>
			<legend>
				<?php echo JText::_('New Menu Item');?>
			</legend>
			
			<table class="adminform">
				<tr>
					<td width="20%">
					</td>
					<td valign="top">
						<label for="menutype">
							<?php echo JText::_('Create in Menu');?>
						</label>
						<br/>
						<?php echo mosHTML::selectList( $menuTypes, 'menutype', 'class="inputbox" size="1"', 'menutype', 'title', $menuType );?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_component" value="component" checked="true" />
						<label for="type_component" class="_type">
							<?php echo JText::_('Component');?>
						</label>
					</td>
					<td valign="top">
						<?php echo JText::_('Link a component to this menu item');?>
						<br/>
						<?php echo mosHTML::selectList( $components, 'componentid', 'class="inputbox" size="8"', 'id', 'name', $components[0]->id );?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_url" value="url" />
						<label for="type_url" class="_type">
							<?php echo JText::_('URL');?>
						</label>
					</td>
					<td valign="top">
						<label for="type_url">
							<?php echo JText::_('URL Address');?>
						</label>
						<br/>
						<input type="text" name="link" size="40" value="http://" /> 
						<br/>
						<?php echo JText::_('Link another URL to this menu item');?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_separator" value="separator" />
						<label for="type_separator" class="_type">
							<?php echo JText::_('Text Label');?>
						</label>
					</td>
					<td valign="top">
						<label for="type_url">
							<?php echo JText::_('Text');?>
						</label>
						<br/>
						<input type="text" name="name" size="40" value="" /> 
						<br/>
						<?php echo JText::_('This menu item will be just plain text');?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_component_item_link" value="component_item_link" />
						<label for="type_component_item_link" class="_type">
							<?php echo JText::_('Menu Item');?>
						</label>
						<br/>
					</td>
					<td valign="top">
						<?php echo JText::_('Link to an existing menu item');?>
						<br/>
						LIST
					</td>
				</tr>
			</table>
		</fieldset>

		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" name="task" value="edit2" />

	</form>
<?php
	}
}

/**
 * Displays the menu edit form
 * @package Joomla
 * @subpackage Menus
 * @static
 * @since 1.5
 */
class JMenuEditView extends JView
{
	/**
	 * Display the view
	 */
	function display()
	{
		$document = &$this->getDocument();

		$document->addStyleSheet('components/com_menumanager/includes/popup.css');
		$document->setTitle('Edit Menu');
		
		$menuType	= JRequest::getVar( 'menutype' );

		$model		= &$this->getModel();
		$table		= &$model->getTable();
		$component	= &$model->getComponent();
		$menuTypes 	= $model->getMenuTypelist();
		$components	= $model->getComponentList();

		$helper		= new JMenuHelper( $component->option );
		$control	= $helper->getControlParams( $table->mvcrt );
		$params		= $helper->getViewParams( $table->params, $control );

		$put[] = mosHTML::makeOption( '0', JText::_( 'No' ));
		$put[] = mosHTML::makeOption( '1', JText::_( 'Yes' ));
		$put[] = mosHTML::makeOption( '-1', JText::_( 'Trash' ));

		mosCommonHTML::loadOverlib();
?>
	<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}

		var comp_links = new Array;
		<?php
		foreach ($components as $row) {
			?>
			comp_links[ <?php echo $row->id;?> ] = 'index.php?<?php echo addslashes( $row->link );?>';
			<?php
		}
		?>
		if ( form.id.value == 0 ) {
			var comp_id = getSelectedValue( 'adminForm', 'componentid' );
			form.link.value = comp_links[comp_id];
		} else {
			form.link.value = comp_links[form.componentid.value];
		}

		if ( trim( form.name.value ) == "" ){
			alert( "<?php echo JText::_( 'Item must have a name', true ); ?>" );
		} else if (form.componentid.value == ""){
			alert( "<?php echo JText::_( 'Please select a Component', true ); ?>" );
		} else {
			submitform( pressbutton );
		}
	}
	</script>

	<form action="index2.php" method="post" name="adminForm">

		<table width="100%">
			<tr valign="top">
				<td width="60%">
					<table class="adminform">
						<tr>
							<th colspan="2">
							<?php echo JText::_( 'Details' ); ?>
							</th>
						</tr>
						<tr>
							<td width="20%" align="right">
								<?php echo JText::_( 'ID' ); ?>:
							</td>
							<td width="80%">
								<strong><?php echo $table->id; ?></strong>
							</td>
						</tr>
						<tr>
							<td align="right">
								<?php echo JText::_( 'Name' ); ?>:
							</td>
							<td>
								<input class="inputbox" type="text" name="name" size="50" maxlength="100" value="<?php echo $table->name; ?>" />
							</td>
						</tr>
						<tr>
							<td align="right">
								<?php echo JText::_( 'Url' ); ?>:
							</td>
							<td>
								<?php echo ampReplace( mosAdminMenus::Link( $table, $table->id ) ); ?>
							</td>
						</tr>
						</tr>
							<td valign="top" align="right">
								<?php echo JText::_( 'Component' ); ?>:
							</td>
							<td>
								<?php echo mosHTML::selectList( $components, 'componentid', 'class="inputbox" size="8"', 'id', 'name', $components[0]->id );?>
							</td>
						</tr>
						<tr>
							<td align="right">
								<?php echo JText::_( 'Display in' ); ?>:
							</td>
							<td>
								<?php echo mosHTML::selectList( $menuTypes, 'menutype', 'class="inputbox" size="1"', 'menutype', 'title', $table->menutype );?>
							</td>
						</tr>
						<tr>
							<td align="right" valign="top">
								<?php echo JText::_( 'Parent Item' ); ?>:
							</td>
							<td>
								<?php echo mosAdminMenus::Parent( $table ); ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<?php echo JText::_( 'Published' ); ?>:
							</td>
							<td>
								<?php echo mosHTML::radioList( $put, 'published', '', $table->published ); ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<?php echo JText::_( 'Ordering' ); ?>:
							</td>
							<td>
								<?php echo mosAdminMenus::Ordering( $table, $table->id ); ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<?php echo JText::_( 'Access Level' ); ?>:
							</td>
							<td>
								<?php echo mosAdminMenus::Access( $table ); ?>
							</td>
						</tr>
					</table>
				</td>
				<td width="40%">
				<?php
					if ($helper->hasControlParams()) {
				?>
					<fieldset>
						<legend>
							<?php echo JText::_( 'Control Parameters' ); ?>
						</legend>
					<?php
						echo $control->render( 'mvcrt' );
					?>
					</fieldset>
			<?php
					}

					menuHTML::MenuOutputParams( $params, $table, 1 );
				?>
				</td>
			</tr>
		</table>

		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" name="id" value="<?php echo $table->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $table->id; ?>" />
		<input type="hidden" name="link" value="" />
		<input type="hidden" name="menutype" value="<?php echo $table->menutype; ?>" />
		<input type="hidden" name="type" value="<?php echo $table->type; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
	</form>
<?php
	}
}

/**
 * @package Joomla
 * @subpackage Menus
 * @static
 * @since 1.5
 */
class menuHTML
{
	/**
	 * Common top section to a menu edit form
	 */
	function MenuOutputTop( &$lists, &$menu, $text=NULL, $tip=NULL ) {
		?>
		<tr>
			<th colspan="2">
			<?php echo JText::_( 'Details' ); ?>
			</th>
		</tr>
		<tr>
			<td width="20%" align="right">
			<?php echo JText::_( 'ID' ); ?>:
			</td>
			<td width="80%">
				<strong><?php echo $menu->id; ?></strong>
			</td>
		</tr>
		<tr>
			<td align="right">
			<?php echo JText::_( 'Menu Type' ); ?>:
			</td>
			<td>
			<?php echo JText::_( $text ); ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JText::_( 'Published' ); ?>:
			</td>
			<td>
			<?php echo $lists['published']; ?>
			</td>
		</tr>
		<tr>
			<td align="right">
			<?php echo JText::_( 'Name' ); ?>:
			</td>
			<td>
			<input class="inputbox" type="text" name="name" size="50" maxlength="100" value="<?php echo $menu->name; ?>" />
			<?php
			if ( !$menu->id && $tip ) {
				echo mosToolTip( JText::_( 'TIPIFLEAVEBLANKCAT' ) );
			}
			?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Common bottom section to a menu edit form
	 */
	function MenuOutputBottom( &$lists, &$menu ) {
		?>
		<tr>
			<td align="right">
			<?php echo JText::_( 'Url' ); ?>:
			</td>
			<td>
			<?php echo ampReplace($lists['link']); ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JText::_( 'Ordering' ); ?>:
			</td>
			<td>
			<?php echo $lists['ordering']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JText::_( 'Access Level' ); ?>:
			</td>
			<td>
			<?php echo $lists['access']; ?>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top">
			<?php echo JText::_( 'Parent Item' ); ?>:
			</td>
			<td>
			<?php echo $lists['parent']; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Outputs the parameters block for a menu item edit form
	 * @param object A JParameters object
	 * @param object A JMenu object
	 * @param string ??
	 */
	function MenuOutputParams( &$params, $menu, $tip=NULL ) {
		?>
		<fieldset>
			<legend>
				<?php echo JText::_( 'Menu Parameters' ); ?>
			</legend>
				
			<?php
			if ($tip) {
				if ($menu->id) {
					echo $params->render();
				} else {
					?>
					<strong>
					<?php echo JText::_( 'TIPPARAMLISTMENUITEM' ); ?>
					</strong>
					<?php
				}
			} else {
				echo $params->render();
			}
			?>
		</fieldset>
		<?php
	}
}


/**
* @package Joomla
* @subpackage Menus
*/
class HTML_menusections {

	function showMenusections( &$rows, &$page, $menutype, $option, &$lists ) 
	{
		global $mainframe;

		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');
		$user =& $mainframe->getUser();
		
		//Ordering allowed ?
		$ordering = ($lists['order'] == 'm.ordering');

		$document	= &$mainframe->getDocument();

		$document->addScript('../includes/js/joomla/popup.js');
		$document->addStyleSheet('../includes/js/joomla/popup.css');

		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(task)
		{
			var f = document.adminForm;
			if (task == 'newwiz')
			{
				menutype = f.menutype.value
				document.popup.show('index3.php?option=com_menus&task=newwiz&menutype='+menutype, 700, 500, null);
			}
			else
			{
				submitform(task);
			}
		}
		</script>

		<form action="index2.php?option=com_menus&amp;menutype=<?php echo $menutype; ?>" method="post" name="adminForm">

		<table>
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php
				echo JText::_( 'Max Levels' );
				echo $lists['levellist'];
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>
		
		<table class="adminlist">
			<thead>
				<tr>
					<th width="20">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" />
					</th>
					<th class="title" width="30%">
						<?php mosCommonHTML::tableOrdering( 'Menu Item', 'm.name', $lists ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'Published', 'm.published', $lists ); ?>
					</th>
					<th width="80" nowrap="nowrap">
						<a href="javascript:tableOrdering('m.ordering','ASC');" title="<?php echo JText::_( 'Order by' ); ?> <?php echo JText::_( 'Order' ); ?>">
							<?php echo JText::_( 'Order' ); ?>
						</a>			
					</th>
					<th width="1%">
						<?php mosCommonHTML::saveorderButton( $rows ); ?>
					</th>
					<th width="10%">
						<?php mosCommonHTML::tableOrdering( 'Access', 'groupname', $lists ); ?>
					</th>
					<th nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'Itemid', 'm.id', $lists ); ?>
					</th>
					<th width="15%" class="title">
						<?php mosCommonHTML::tableOrdering( 'Type', 'm.type', $lists ); ?>
					</th>
					<th nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'CID', 'm.componentid', $lists ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<td colspan="12">
					<?php echo $page->getListFooter(); ?>
				</td>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			$i = 0;
			$n = count( $rows );
			foreach ($rows as $row) {
				$access 	= mosCommonHTML::AccessProcessing( $row, $i );
				$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
				$published 	= mosCommonHTML::PublishedProcessing( $row, $i );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $i + 1 + $page->limitstart;?>
					</td>
					<td>
						<?php echo $checked; ?>
					</td>
					<td nowrap="nowrap">
						<?php
						if ( $row->checked_out && ( $row->checked_out != $user->get('id') ) ) {
							echo $row->treename;
						} else {
							$link = 'index2.php?option=com_menus&menutype='. $row->menutype .'&task=edit&id='. $row->id . '&hidemainmenu=1';
							?>
							<a href="<?php echo ampReplace( $link ); ?>">
								<?php echo $row->treename; ?></a>
							<?php
						}
						?>
					</td>
					<td width="10%" align="center">
						<?php echo $published;?>
					</td>
					<td class="order" colspan="2" nowrap="nowrap">
						<span><?php echo $page->orderUpIcon( $i, true, 'orderup', 'Move Up', $ordering); ?></span>
						<span><?php echo $page->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', $ordering ); ?></span>
						<?php $disabled = $ordering ?  '' : '"disabled=disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
					</td>
					<td align="center">
						<?php echo $access;?>
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
					<td>
						<span class="editlinktip">
							<?php
							echo mosToolTip( $row->descrip, '', 280, 'tooltip.png', $row->type, $row->edit, !empty($row->edit) );
							?>
						</span>
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

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="menutype" value="<?php echo $menutype; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}


	/**
	* Displays a selection list for menu item types
	*/
	function addMenuItem( &$cid, $menutype, $option, $types_content, $types_component, $types_link, $types_other, $types_submit ) {

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm">

		<table width="100%">
		<tr>
			<td valign="bottom" nowrap="nowrap" style="color: red;">
            <?php echo JText::_( 'DESCMENUGROUP' ); ?>
			</td>
		</tr>
		</table>

		<table width="100%">
		<tr>
			<td width="50%" valign="top">
				<fieldset>
					<legend><?php echo JText::_( 'Content' ); ?></legend>
					<table class="adminform">
					<?php
					$k 		= 0;
					$count 	= count( $types_content );
						for ( $i=0; $i < $count; $i++ ) {
						$row = &$types_content[$i];

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type .'&hidemainmenu=1';

						HTML_menusections::htmlOptions( $row, $link, $k, $i );

						$k = 1 - $k;
					}
					?>
					</table>
				</fieldset>
				<fieldset>
					<legend><?php echo JText::_( 'Miscellaneous' ); ?></legend>
					<table class="adminform">
					<?php
					$k 		= 0;
					$count 	= count( $types_other );
						for ( $i=0; $i < $count; $i++ ) {
						$row = &$types_other[$i];

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type .'&hidemainmenu=1';

						HTML_menusections::htmlOptions( $row, $link, $k, $i );

						$k = 1 - $k;
					}
					?>
					</table>
				</fieldset>
				<fieldset>
					<legend><?php echo JText::_( 'Submit' ); ?></legend>
					<table class="adminform">
					<?php
					$k 		= 0;
					$count 	= count( $types_submit );
						for ( $i=0; $i < $count; $i++ ) {
						$row = &$types_submit[$i];

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type .'&hidemainmenu=1';

						HTML_menusections::htmlOptions( $row, $link, $k, $i );

						$k = 1 - $k;
					}
					?>
					</table>
				</fieldset>
			</td>
			<td width="50%" valign="top">
				<fieldset>
					<legend><?php echo JText::_( 'Components' ); ?></legend>
					<table class="adminform">
					<?php
					$k 		= 0;
					$count 	= count( $types_component );
						for ( $i=0; $i < $count; $i++ ) {
						$row = &$types_component[$i];

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type .'&hidemainmenu=1';

						HTML_menusections::htmlOptions( $row, $link, $k, $i );

						$k = 1 - $k;
					}
					?>
					</table>
				</fieldset>
				<fieldset>
					<legend><?php echo JText::_( 'Links' ); ?></legend>
					<table class="adminform">
					<?php
					$k 		= 0;
					$count 	= count( $types_link );
						for ( $i=0; $i < $count; $i++ ) {
						$row = &$types_link[$i];

						$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=edit&type='. $row->type .'&hidemainmenu=1';

						HTML_menusections::htmlOptions( $row, $link, $k, $i );

						$k = 1 - $k;
					}
					?>
					</table>
				</fieldset>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="menutype" value="<?php echo $menutype; ?>" />
		<input type="hidden" name="task" value="edit" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}

	function htmlOptions( &$row, $link, $k, $i ) {
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td width="20">
			</td>
			<td style="height: 25px;">
				<span class="editlinktip" style="cursor: pointer;">
						<?php
						echo mosToolTip( $row->descrip, $row->name, 250, '', $row->name, $link, 1 );
						?>
				</span>
			</td>
			<td width="20">
				<input type="radio" id="cb<?php echo $i;?>" name="type" value="<?php echo $row->type; ?>" onclick="isChecked(this.checked);" />
			</td>
			<td width="20">
			</td>
		</tr>
		<?php
	}

	/**
	* Form to select Menu to move menu item(s) to
	*/
	function moveMenu( $option, $cid, $MenuList, $items, $menutype  ) {
		?>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="30%">
			<strong><?php echo JText::_( 'Move to Menu' ); ?>:</strong>
			<br />
			<?php echo $MenuList ?>
			<br /><br />
			</td>
			<td  valign="top">
			<strong>
			<?php echo JText::_( 'Menu Items being moved' ); ?>:
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

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="boxchecked" value="1" />
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
		?>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="30%">
			<strong>
			<?php echo JText::_( 'Copy to Menu' ); ?>:
			</strong>
			<br />
			<?php echo $MenuList ?>
			<br /><br />
			</td>
			<td  valign="top">
			<strong>
			<?php echo JText::_( 'Menu Items being copied' ); ?>:
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

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="boxchecked" value="0" />
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
