<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
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
* Writes the edit form for new and existing content item
*
* A new record is defined when <var>$row</var> is passed with the <var>id</var>
* property set to 0.
* @package Joomla
* @subpackage Menus
*/
class components_menu_html {


	function edit( &$menu, &$components, &$lists, &$params, $option ) {
		global $mosConfig_live_site;
		;

		if ( $menu->id ) {
			$title = '[ '. $lists['componentname'] .' ]';
		} else {
			$title = '';
		}
		?>
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
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
				comp_links[ <?php echo $row->value;?> ] = 'index.php?<?php echo addslashes( $row->link );?>';
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
				alert( "<?php echo JText::_( 'Item must have a name' ); ?>" );
			} else if (form.componentid.value == ""){
				alert( "<?php echo JText::_( 'Please select a Component' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>

		<form action="index2.php" method="post" name="adminForm">

		<table class="adminheading">
		<tr>
			<th>
			<?php echo $menu->id ? JText::_( 'Edit' ) : JText::_( 'Add' );?> <?php echo JText::_( 'Menu Item :: Component' ); ?> <small><small><?php echo $title; ?></small></small>
			</th>
		</tr>
		</table>

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
					<td width="10%" align="right"><?php echo JText::_( 'Name' ); ?>:</td>
					<td width="80%">
					<input class="inputbox" type="text" name="name" size="50" maxlength="100" value="<?php echo htmlspecialchars( $menu->name, ENT_QUOTES ); ?>" />
					</td>
				</tr>
				<tr>
					<td valign="top" align="right"><?php echo JText::_( 'Component' ); ?>:</td>
					<td>
					<?php echo $lists['componentid']; ?>
					</td>
				</tr>
				<tr>
					<td width="10%" align="right"><?php echo JText::_( 'Url' ); ?>:</td>
					<td width="80%">
                    <?php echo ampReplace($lists['link']); ?>
					</td>
				</tr>
				<tr>
					<td align="right"><?php echo JText::_( 'Parent Item' ); ?>:</td>
					<td>
					<?php echo $lists['parent'];?>
					</td>
				</tr>

				<tr>
					<td valign="top" align="right"><?php echo JText::_( 'Ordering' ); ?>:</td>
					<td>
					<?php echo $lists['ordering']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right"><?php echo JText::_( 'Access Level' ); ?>:</td>
					<td>
					<?php echo $lists['access']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right"><?php echo JText::_( 'Published' ); ?>:</td>
					<td>
					<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				</table>
			</td>
			<td width="40%">
				<table class="adminform">
				<tr>
					<th>
					<?php echo JText::_( 'Parameters' ); ?>
					</th>
				</tr>
				<tr>
					<td>
					<?php
					if ($menu->id) {
						echo $params->render();
					} else {
						?>
						<strong><?php echo JText::_( 'TIPPARAMLISTMENUITEM' ); ?></strong>
						<?php
					}
					?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="id" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="link" value="" />
		<input type="hidden" name="menutype" value="<?php echo $menu->menutype; ?>" />
		<input type="hidden" name="type" value="<?php echo $menu->type; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<script language="Javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/overlib_mini.js"></script>
		<?php
	}
}
?>