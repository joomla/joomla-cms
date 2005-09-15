<?php
/**
* @version $Id: content_item_link.menu.html.php 137 2005-09-12 10:21:17Z eddieajau $
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
* Disaply content item link
* @package Joomla
* @subpackage Menus
*/
class content_item_link_menu_html {

	function edit( &$menu, &$lists, &$params, $option, $content ) {
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
			if (pressbutton == 'redirect') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (trim(form.name.value) == ""){
				alert( "<?php echo $_LANG->_( 'Link must have a name' ); ?>" );
			} else if (trim(form.content_item_link.value) == ""){
				alert( "<?php echo $_LANG->_( 'You must select a Content to link to' ); ?>" );
			} else {
				form.link.value = "index.php?option=com_content&task=view&id=" + form.content_item_link.value;
				form.componentid.value = form.content_item_link.value;
				submitform( pressbutton );
			}
		}
		</script>
		<?php
		mosMenuFactory::formStart( 'Link - Content Item' );

		mosMenuFactory::tableStart();
		mosMenuFactory::formElementName( $menu->name );

		mosMenuFactory::formElement( $lists['content'], 	$_LANG->_( 'Content to Link' ) );

		mosMenuFactory::formElement( $lists['link'], 		'URL' );
		mosMenuFactory::formElement( $lists['target'], 		'TAR' );
		mosMenuFactory::formElement( $lists['parent'], 		'PAR' );
		mosMenuFactory::formElement( $lists['ordering'], 	'ORD' );
		mosMenuFactory::formElement( $lists['access'], 		'ACC' );
		mosMenuFactory::formElement( $lists['published'], 	'PUB' );
		mosMenuFactory::tableEnd();

		mosMenuFactory::formParams( $params, 1 );
		?>
		<input type="hidden" name="scid" value="<?php echo $menu->componentid; ?>" />
		<input type="hidden" name="componentid" value="" />
		<input type="hidden" name="link" value="" />
		<?php
		mosMenuFactory::formElementHdden( $menu, $option );
	}
}
?>