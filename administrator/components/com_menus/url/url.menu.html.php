<?php
/**
* @version $Id: url.menu.html.php 137 2005-09-12 10:21:17Z eddieajau $
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
class url_menu_html {

	function edit( $menu, $lists, $params, $option ) {
		global $_LANG;

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
				alert( "<?php echo $_LANG->_( 'Link must have a name' ); ?>" );
			} else if (trim(form.link.value) == ""){
				alert( "<?php echo $_LANG->_( 'You must provide a url.' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<?php
		mosMenuFactory::formStart( 'Link - URL' );

		mosMenuFactory::tableStart();
		mosMenuFactory::formElementName( $menu->name );

		mosMenuFactory::formElementInput( $_LANG->_( 'Link' ), 'link', $menu->link );

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