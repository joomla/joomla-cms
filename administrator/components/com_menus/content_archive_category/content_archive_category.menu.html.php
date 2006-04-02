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
* Writes the edit form for new and existing content item
*
* A new record is defined when <var>$row</var> is passed with the <var>id</var>
* property set to 0.
* @package Joomla
* @subpackage Menus
*/
class content_archive_category_menu_html {

	function editCategory( &$menu, &$lists, &$params, $option ) 
	{
		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			var form = document.adminForm;
			
			if ( getSelectedValue( 'adminForm', 'componentid' ) < 1 ) {
				alert( "<?php echo JText::_( 'You must select a category', true ); ?>" );
				return;
			}
			sectcat = getSelectedText( 'adminForm', 'componentid' );
			sectcats = sectcat.split('/');
			section = getSelectedOption( 'adminForm', 'componentid' );

			form.link.value = "index.php?option=com_content&task=archivecategory&id=" + form.componentid.value;
			if ( form.name.value == '' ) {
				form.name.value = sectcats[1];
			}
			submitform( pressbutton );
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">

		<table width="100%">
		<tr valign="top">
			<td width="60%">
				<table class="adminform">
				<?php mosAdminMenus::MenuOutputTop( $lists, $menu, 'Blog - Content Category Archive', 1 ); ?>
				<tr>
					<td valign="top" align="right">
					<?php echo JText::_( 'Category' ); ?>:
					</td>
					<td>
					<?php echo $lists['componentid']; ?>
					</td>
				</tr>
				<?php mosAdminMenus::MenuOutputBottom( $lists, $menu ); ?>
				</table>
			</td>
			<?php mosAdminMenus::MenuOutputParams( $params, $menu ); ?>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="id" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="menutype" value="<?php echo $menu->menutype; ?>" />
		<input type="hidden" name="type" value="<?php echo $menu->type; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}
}
?>