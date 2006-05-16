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
class content_blog_category_html {

	function edit( &$menu, &$lists, &$params, $option )
	{
		mosCommonHTML::loadOverlib();
		/* in the HTML below, references to "section" were changed to "category" */
		?>
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			var form = document.adminForm;
			<?php
			if ( !$menu->id ) {
				?>
				if ( form.name.value == '' ) {
					alert( "<?php echo JText::_( 'This Menu item must have a title', true ); ?>" );
					return;
				} else {
					submitform( pressbutton );
				}
				<?php
			} else {
				?>
				if ( form.name.value == '' ) {
					alert( "<?php echo JText::_( 'This Menu item must have a title', true ); ?>" );
				} else {
					submitform( pressbutton );
				}
				<?php
			}
			?>
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">

		<table width="100%">
		<tr valign="top">
			<td width="60%">
				<table class="adminform">
				<?php menuHTML::MenuOutputTop( $lists, $menu, 'Blog - Content Category' ); ?>
				<tr>
			  		<td valign="top" align="right">
			  		<?php echo JText::_( 'Category' ); ?>:
			  		</td>
			 		<td>
			  		<?php echo $lists['categoryid']; ?>
			 		<?php
			 		echo mosToolTip( JText::_( 'You can select multiple Categories' ) )
			 		?>
			  		</td>
				</tr>
				<?php menuHTML::MenuOutputBottom( $lists, $menu ); ?>
				</table>
			</td>
			<td width="40%">
				<?php menuHTML::MenuOutputParams( $params, $menu ); ?>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="id" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="menutype" value="<?php echo $menu->menutype; ?>" />
		<input type="hidden" name="type" value="<?php echo $menu->type; ?>" />
		<input type="hidden" name="link" value="index.php?option=com_content&task=blogcategory&id=0" />
		<input type="hidden" name="componentid" value="0" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}
}
?>