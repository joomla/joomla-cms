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

/**
 * @package Joomla
 * @subpackage Menus
 * @since 1.5
 */
class JMenuViewItem extends JView
{

	function edit()
	{
		$document = &$this->getDocument();

		$document->addScript('../includes/js/joomla/popup.js');
		$document->addStyleSheet('../includes/js/joomla/popup.css');
		$document->addStyleSheet('components/com_menumanager/includes/popup.css');
		$document->setTitle('Edit Menu');

		$menuType	= JRequest::getVar( 'menutype' );

		$item		= &$this->get('Item');
		$component	= &$this->get('Component');
		$menuTypes 	= $this->get('MenuTypelist');
		$components	= $this->get('ComponentList');

		$helper		= new JMenuHelper( $component->option );
		$control	= $this->get( 'ControlParams' );
		$params		= $this->get( 'StateParams' );
		$details	= $this->get( 'Details' );
		$controlFields	= $this->get( 'ControlFields' );

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
	<h1>BROKEN - WIP - I KNOW</h1>
	<form action="index2.php" method="post" name="adminForm">

		<table class="admintable" width="100%">
			<tr valign="top">
				<td width="60%">
					<fieldset>
						<legend>
							<?php echo JText::_( 'Menu Item Details' ); ?>
						</legend>
						<table width="100%">
							<?php if ($item->id) { ?>
							<tr>
								<td class="key" width="20%" align="right">
									<?php echo JText::_( 'ID' ); ?>:
								</td>
								<td width="80%">
									<strong><?php echo $item->id; ?></strong>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<td class="key" align="right">
									<?php echo JText::_( 'Name' ); ?>:
								</td>
								<td>
									<input class="inputbox" type="text" name="name" size="50" maxlength="100" value="<?php echo $item->name; ?>" />
								</td>
							</tr>
							<tr>
								<td class="key" align="right">
									<?php echo JText::_( 'Display in' ); ?>:
								</td>
								<td>
									<?php echo mosHTML::selectList( $menuTypes, 'menutype', 'class="inputbox" size="1"', 'menutype', 'title', $item->menutype );?>
								</td>
							</tr>
							<tr>
								<td class="key" align="right" valign="top">
									<?php echo JText::_( 'Parent Item' ); ?>:
								</td>
								<td>
									<?php echo JMenuHelper::Parent( $item ); ?>
								</td>
							</tr>
							<tr>
								<td class="key" valign="top" align="right">
									<?php echo JText::_( 'Published' ); ?>:
								</td>
								<td>
									<?php echo mosHTML::radioList( $put, 'published', '', $item->published ); ?>
								</td>
							</tr>
							<tr>
								<td class="key" valign="top" align="right">
									<?php echo JText::_( 'Ordering' ); ?>:
								</td>
								<td>
									<?php echo mosAdminMenus::Ordering( $item, $item->id ); ?>
								</td>
							</tr>
							<tr>
								<td class="key" valign="top" align="right">
									<?php echo JText::_( 'Access Level' ); ?>:
								</td>
								<td>
									<?php echo mosAdminMenus::Access( $item ); ?>
								</td>
							</tr>
						</table>
					</fieldset>
					<fieldset>
						<legend>
							<?php echo JText::_( 'Control Parameters' ); ?>
						</legend>
						<table width="100%">
							</tr>
								<td align="right"  colspan="2">
									<a onclick="document.popup.show('index.php?option=com_menus&amp;task=newwiz&amp;tmpl=component.html&amp;id=<?php echo $item->id; ?>', 700, 500, null);" class="toolbar">
										[ EDIT ]
									</a>
								</td>
							</tr>
							<?php foreach($details as $detail) { ?>
							</tr>
								<td class="key"  width="20%">
									<?php echo $detail['label']; ?>:
								</td>
								<td width="80%">
									<?php echo $detail['name']; ?>
								</td>
							</tr>
							<?php }
							$cArray = $control->toArray();
							foreach($cArray as $k => $v) { ?>
							</tr>
								<td></td>
								<td>
									<?php echo $v; ?>
								</td>
							</tr>
							<?php } ?>
						</table>
					</fieldset>
				</td>
				<td width="40%">
					<fieldset>
						<legend>
							<?php echo JText::_( 'Menu Item Parameters' ); ?>
						</legend>
					<?php
						echo $params->render('state');
						if (is_array($controlFields)) {
							echo implode("\n", $controlFields);
						}
					?>
					</fieldset>
				</td>
			</tr>
		</table>

		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" name="id" value="<?php echo $item->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $item->id; ?>" />
		<input type="hidden" name="link" value="" />
		<input type="hidden" name="menutype" value="<?php echo $item->menutype; ?>" />
		<input type="hidden" name="type" value="<?php echo $item->type; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
	</form>
	<?php
	}
}
?>