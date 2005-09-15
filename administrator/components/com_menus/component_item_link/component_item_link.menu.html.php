<?php
/**
* @version $Id: component_item_link.menu.html.php 137 2005-09-12 10:21:17Z eddieajau $
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
* Display Component item link
* @package Joomla
* @subpackage Menus
*/
class component_item_link_menu_html {

	function edit( &$menu, &$lists, &$params, $option ) {
	  	global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if ( pressbutton == 'cancel' ) {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if ( trim(form.name.value) == "" ){
				alert( "<?php echo $_LANG->_( 'Link must have a name' ); ?>" );
			} else if ( trim( form.link.value ) == "" ){
				alert( "<?php echo $_LANG->_( 'You must select a Component to link to' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<?php
		mosMenuFactory::formStart( 'Link - Component Item' );

		mosMenuFactory::tableStart();
		mosMenuFactory::formElementName( $menu->name );

		mosMenuFactory::formElement( $lists['components'], 	$_LANG->_( 'Component to Link' ) );

		mosMenuFactory::formElement( $lists['link'], 		'URL' );
		mosMenuFactory::formElement( $lists['target'], 		'TAR' );
		mosMenuFactory::formElement( $lists['parent'], 		'PAR' );
		mosMenuFactory::formElement( $lists['ordering'], 	'ORD' );
		mosMenuFactory::formElement( $lists['access'], 		'ACC' );
		mosMenuFactory::formElement( $lists['published'], 	'PUB' );
		mosMenuFactory::tableEnd();

		mosMenuFactory::formParams( $params, 1 );

		mosMenuFactory::formElementHdden( $menu, $option );
	}
}
?>