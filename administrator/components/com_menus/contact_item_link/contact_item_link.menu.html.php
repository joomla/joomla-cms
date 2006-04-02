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
* Disaply contact item link
* @package Joomla
* @subpackage Menus
*/
class contact_item_link_menu_html {

	function edit( &$menu, &$lists, &$params, $option, $contact ) 
	{
		mosCommonHTML::loadOverlib();
			
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (trim(form.name.value) == ""){
				alert( "<?php echo JText::_( 'Link must have a name', true ); ?>" );
			} else if (trim(form.contact_item_link.value) == ""){
				alert( "<?php echo JText::_( 'You must select a Contact to link to', true ); ?>" );
			} else {
				form.link.value = "index.php?option=com_contact&task=view&contact_id=" + form.contact_item_link.value;
				form.componentid.value = form.contact_item_link.value;
				submitform( pressbutton );
			}
		}
		</script>

		<form action="index2.php" method="post" name="adminForm">

		<table width="100%">
		<tr valign="top">
			<td width="60%">
				<table class="adminform">
				<?php mosAdminMenus::MenuOutputTop( $lists, $menu, 'Link - Contact Item' ); ?>
				<tr>
					<td align="right" valign="top">
					<?php echo JText::_( 'Contact to Link' ); ?>:
					</td>
					<td>
					<?php echo $lists['contact']; ?>
					</td>
				</tr>
				<?php mosAdminMenus::MenuOutputBottom( $lists, $menu ); ?>
				<tr>
					<td align="right" valign="top">
					<?php echo JText::_( 'On Click, Open in' ); ?>:
					</td>
					<td>
					<?php echo $lists['target']; ?>
					</td>
				</tr>
				</table>
			</td>
			<?php mosAdminMenus::MenuOutputParams( $params, $menu ); ?>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="id" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="componentid" value="" />
		<input type="hidden" name="link" value="" />
		<input type="hidden" name="menutype" value="<?php echo $menu->menutype; ?>" />
		<input type="hidden" name="type" value="<?php echo $menu->type; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}

}
?>