<?php
/**
* @version $Id: newsfeed_category_table.menu.html.php 137 2005-09-12 10:21:17Z eddieajau $
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
* Writes the edit form for new and existing content item
*
* A new record is defined when <var>$row</var> is passed with the <var>id</var>
* property set to 0.
* @package Joomla
* @subpackage Menus
*/
class newsfeed_category_table_menu_html {

	function editCategory( &$menu, &$lists, &$params, $option ) {
		global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if ( pressbutton == 'cancel' ) {
				submitform( pressbutton );
				return;
			}
			var form = document.adminForm;
			<?php
			if ( !$menu->id ) {
				?>
				if ( getSelectedValue( 'adminForm', 'componentid' ) < 1 ) {
					alert( '<?php echo $_LANG->_( 'You must select a category' ); ?>' );
					return;
				}
				cat = getSelectedText( 'adminForm', 'componentid' );

				form.link.value = "index.php?option=com_newsfeeds&catid=" + form.componentid.value;
				if ( form.name.value == '' ) {
					form.name.value = cat;
				}
				submitform( pressbutton );
				<?php
			} else {
				?>
				if ( form.name.value == '' ) {
					alert( '<?php echo $_LANG->_( 'This Menu item must have a title' ); ?>' );
				} else {
					submitform( pressbutton );
				}
				<?php
			}
			?>
		}
		</script>
		<?php
		$tip = '';
		if ( !$menu->id ) {
			$tip = $_LANG->_( 'TIPIFLEAVEBLANKCATNAMEWILLBEUSED' );
		}

		mosMenuFactory::formStart( 'Link - Newsfeed Category' );

		mosMenuFactory::tableStart();
		mosMenuFactory::formElementName( $menu->name, '', $tip );

		mosMenuFactory::formElement( $lists['componentid'],	'CAT' );
		mosMenuFactory::formElement( $lists['link'], 		'URL' );
		mosMenuFactory::formElement( $lists['target'], 		'TAR' );
		mosMenuFactory::formElement( $lists['parent'], 		'PAR' );
		mosMenuFactory::formElement( $lists['ordering'], 	'ORD' );
		mosMenuFactory::formElement( $lists['access'], 		'ACC' );
		mosMenuFactory::formElement( $lists['published'], 	'PUB' );
		mosMenuFactory::tableEnd();

		mosMenuFactory::formParams( $params );
		?>
		<input type="hidden" name="link" value="<?php echo $menu->link; ?>" />
		<?php
		mosMenuFactory::formElementHdden( $menu, $option );
	}
}
?>