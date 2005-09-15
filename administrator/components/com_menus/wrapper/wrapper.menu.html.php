<?php
/**
* @version $Id: wrapper.menu.html.php 137 2005-09-12 10:21:17Z eddieajau $
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
* Display wrapper
* @package Joomla
* @subpackage Menus
*/
class wrapper_menu_html {


	function edit( &$menu, &$lists, &$params, $option ) {
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
			if ( form.name.value == "" ) {
				alert( '<?php echo $_LANG->_( 'This Menu item must have a title' ); ?>' );
			} else {
				<?php
				if ( !$menu->id ) {
					?>
					if ( form.url.value == "" ){
						alert( "<?php echo $_LANG->_( 'You must provide a url.' ); ?>" );
					} else {
						submitform( pressbutton );
					}
					<?php
				} else {
					?>
					submitform( pressbutton );
					<?php
				}
				?>
			}
		}
		</script>
		<?php
		mosMenuFactory::formStart( 'Wrapper' );

		mosMenuFactory::tableStart();
		mosMenuFactory::formElementName( $menu->name );

		mosMenuFactory::formElementInput( $_LANG->_( 'Wrapper Link' ), 'url', $menu->url );

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