<?php
/**
* @version $Id: content_blog_category.menu.html.php 137 2005-09-12 10:21:17Z eddieajau $
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
class content_blog_category_html {

	function edit( &$menu, &$lists, &$params, $option ) {
 		global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
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
					alert( '<?php echo $_LANG->_( 'This Menu item must have a title' ); ?>' );
					return;
				} else {
					submitform( pressbutton );
				}
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
		mosMenuFactory::formStart( 'Blog - Content Category' );

		mosMenuFactory::tableStart();
		mosMenuFactory::formElementName( $menu->name );

		mosMenuFactory::formElement( $lists['categoryid'], 	'CAT', $_LANG->_( 'You can select multiple Categories' ) );

		mosMenuFactory::formElement( $lists['link'], 		'URL' );
		mosMenuFactory::formElement( $lists['target'], 		'TAR' );
		mosMenuFactory::formElement( $lists['parent'], 		'PAR' );
		mosMenuFactory::formElement( $lists['ordering'], 	'ORD' );
		mosMenuFactory::formElement( $lists['access'], 		'ACC' );
		mosMenuFactory::formElement( $lists['published'], 	'PUB' );
		mosMenuFactory::tableEnd();

		mosMenuFactory::formParams( $params, 3 );
		?>
		<input type="hidden" name="link" value="index.php?option=com_content&task=blogcategory&id=0" />
		<input type="hidden" name="componentid" value="0" />
		<?php
		mosMenuFactory::formElementHdden( $menu, $option );
	}
}
?>