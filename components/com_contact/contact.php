<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Contact
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

// load the html drawing class
require_once( $mainframe->getPath( 'front_html' ) );
require_once( $mainframe->getPath( 'class' ) );

$mainframe->setPageTitle( _CONTACT_TITLE );

//Load Vars
$op			= mosGetParam( $_REQUEST, 'op' );
$con_id 	= intval( mosGetParam( $_REQUEST ,'con_id', 0 ) );
$contact_id = intval( mosGetParam( $_REQUEST ,'contact_id', 0 ) );
$catid 		= intval( mosGetParam( $_REQUEST ,'catid', 0 ) );

switch( $task ) {
	case 'view':
		contactpage( $contact_id );
		break;

	case 'vcard':
		vCard( $contact_id );
		break;

	default:
		listContacts( $option, $catid );
		break;
}

switch( $op ) {
	case 'sendmail':
		sendmail( $con_id, $option );
		break;

}


function listContacts( $option, $catid ) {
	global $mainframe, $database, $my;
	global $mosConfig_live_site;
	global $Itemid;
	global $_LANG;

	/* Query to retrieve all categories that belong under the contacts section and that are published. */
	$query = "SELECT *, COUNT( a.id ) AS numlinks"
	. "\n FROM #__categories AS cc"
	. "\n LEFT JOIN #__contact_details AS a ON a.catid = cc.id"
	. "\n WHERE a.published = 1"
	. "\n AND cc.section = 'com_contact_details'"
	. "\n AND cc.published = 1"
	. "\n AND a.access <= $my->gid"
	. "\n AND cc.access <= $my->gid"
	. "\n GROUP BY cc.id"
	. "\n ORDER BY cc.ordering"
	;
	$database->setQuery( $query );
	$categories = $database->loadObjectList();

	$count = count( $categories );
	if ( ( $count < 2 ) && ( @$categories[0]->numlinks == 1 ) ) {
		// if only one record exists loads that record, instead of displying category list
		contactpage( $option, 0 );
	} else {
		$rows = array();
		$currentcat = NULL;

		// Parameters
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );

		$params->def( 'page_title', 1 );
		$params->def( 'header', $menu->name );
		$params->def( 'pageclass_sfx', '' );
		$params->def( 'headings', 1 );
		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );
		$params->def( 'description_text', $_LANG->_( 'The Contact list for this Website.' ) );
		$params->def( 'image', -1 );
		$params->def( 'image_align', 'right' );
		$params->def( 'other_cat_section', 1 );
		// Category List Display control
		$params->def( 'other_cat', 1 );
		$params->def( 'cat_description', 1 );
		$params->def( 'cat_items', 1 );
		// Table Display control
		$params->def( 'headings', 1 );
		$params->def( 'position', '1' );
		$params->def( 'email', '0' );
		$params->def( 'phone', '1' );
		$params->def( 'fax', '1' );
		$params->def( 'telephone', '1' );

		if( $catid == 0 ) {
			$catid = $params->get( 'catid', 0 );
		}

		if ( $catid ) {
			$params->set( 'type', 'category' );
		} else {
			$params->set( 'type', 'section' );
		}

		if ( $catid ) {
			// url links info for category
			$query = "SELECT *"
			. "\n FROM #__contact_details"
			. "\n WHERE catid = $catid"
			 . "\n AND published =1"
			 . "\n AND access <= $my->gid"
			. "\n ORDER BY ordering"
			;
			$database->setQuery( $query );
			$rows = $database->loadObjectList();

			// current category info
			$query = "SELECT name, description, image, image_position"
			. "\n FROM #__categories"
			. "\n WHERE id = $catid"
			. "\n AND published = 1"
			. "\n AND access <= $my->gid"
			;
			$database->setQuery( $query );
			$database->loadObject( $currentcat );
		}

		// page description
		$currentcat->descrip = '';
		if( isset($currentcat->description) && ($currentcat->description != '') ) {
			$currentcat->descrip = $currentcat->description;
		} else if ( !$catid ) {
			// show description
			if ( $params->get( 'description' ) ) {
				$currentcat->descrip = $params->get( 'description_text' );
			}
		}

		// page image
		$currentcat->img = '';
		$path = $mosConfig_live_site .'/images/stories/';
		if ( isset($currentcat->image) && ($currentcat->image != '') ) {
			$currentcat->img = $path . $currentcat->image;
			$currentcat->align = $currentcat->image_position;
		} else if ( !$catid ) {
			if ( $params->get( 'image' ) != -1 ) {
				$currentcat->img = $path . $params->get( 'image' );
				$currentcat->align = $params->get( 'image_align' );
			}
		}

		// page header
		$currentcat->header = '';
		if ( isset($currentcat->name) && ($currentcat->name != '') ) {
			$currentcat->header = $params->get( 'header' ) .' - '. $currentcat->name;
		} else {
			$currentcat->header = $params->get( 'header' );
		}

		// used to show table rows in alternating colours
		$tabclass = array( 'sectiontableentry1', 'sectiontableentry2' );

		HTML_contact::displaylist( $categories, $rows, $catid, $currentcat, $params, $tabclass );
	}
}


function contactpage( $contact_id ) {
	global $mainframe, $database, $my, $Itemid;
	global $_LANG;

	$query = "SELECT a.id AS value, CONCAT_WS( ' - ', a.name, a.con_position ) AS text"
	. "\n FROM #__contact_details AS a"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = a.catid"
	. "\n WHERE a.published = 1"
	. "\n AND cc.published = 1"
	. "\n AND a.access <= $my->gid"
	. "\n AND cc.access <= $my->gid"
	. "\n ORDER BY a.default_con DESC, a.ordering ASC"
	;
	$database->setQuery( $query );
	$list = $database->loadObjectList();

	$count = count( $list );
	if ($count) {
		if ($contact_id < 1) {
			$contact_id = $list[0]->value;
		}

		$query = "SELECT *"
		. "\n FROM #__contact_details"
		. "\n WHERE published = 1"
		. "\n AND id = $contact_id"
		. "\n AND access <= $my->gid"
		;
		$database->SetQuery($query);
		$contacts = $database->LoadObjectList();

		if (!$contacts){
			echo $_LANG->_('ALERTNOTAUTH');
			return;
		}
		$contact = $contacts[0];
		// creates dropdown select list
		$contact->select = mosHTML::selectList( $list, 'contact_id', 'class="inputbox" onchange="ViewCrossReference(this);"', 'value', 'text', $contact_id );

		// Adds parameter handling
		$params = new mosParameters( $contact->params );

		$params->set( 'page_title', 0 );
		$params->def( 'pageclass_sfx', '' );
		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );
		$params->def( 'print', !$mainframe->getCfg( 'hidePrint' ) );
		$params->def( 'name', '1' );
		$params->def( 'email', '0' );
		$params->def( 'street_address', '1' );
		$params->def( 'suburb', '1' );
		$params->def( 'state', '1' );
		$params->def( 'country', '1' );
		$params->def( 'postcode', '1' );
		$params->def( 'telephone', '1' );
		$params->def( 'fax', '1' );
		$params->def( 'misc', '1' );
		$params->def( 'image', '1' );
		$params->def( 'email_description', '1' );
		$params->def( 'email_description_text', $_LANG->_( 'Send an Email to this Contact:' ) );
		$params->def( 'email_form', '1' );
		$params->def( 'email_copy', '1' );
		// global pront|pdf|email
		$params->def( 'icons', $mainframe->getCfg( 'icons' ) );
		// contact only icons
		$params->def( 'contact_icons', 0 );
		$params->def( 'icon_address', '' );
		$params->def( 'icon_email', '' );
		$params->def( 'icon_telephone', '' );
		$params->def( 'icon_fax', '' );
		$params->def( 'icon_misc', '' );
		$params->def( 'drop_down', '0' );
		$params->def( 'vcard', '1' );


		if ( $contact->email_to && $params->get( 'email' )) {
			// email cloacking
			$contact->email = mosHTML::emailCloaking( $contact->email_to );
		}

		// loads current template for the pop-up window
		$pop = mosGetParam( $_REQUEST, 'pop', 0 );
		if ( $pop ) {
			$params->set( 'popup', 1 );
			$params->set( 'back_button', 0 );
		}

		if ( $params->get( 'email_description' ) ) {
			$params->set( 'email_description', $params->get( 'email_description_text' ) );
		} else {
			$params->set( 'email_description', '' );
		}

		// needed to control the display of the Address marker
		$temp = $params->get( 'street_address' )
		. $params->get( 'suburb' )
		. $params->get( 'state' )
		. $params->get( 'country' )
		. $params->get( 'postcode' )
		;
		$params->set( 'address_check', $temp );

		// determines whether to use Text, Images or nothing to highlight the different info groups
		switch ( $params->get( 'contact_icons' ) ) {
			case 1:
			// text
				$params->set( 'marker_address', $_LANG->_( 'Address' ) .": " );
				$params->set( 'marker_email', $_LANG->_( 'Email' ) .": " );
				$params->set( 'marker_telephone', $_LANG->_( 'Telephone' ) .": " );
				$params->set( 'marker_fax', $_LANG->_( 'Fax' ) .": " );
				$params->set( 'marker_misc', $_LANG->_( 'Information' ) .": " );
				$params->set( 'column_width', '100' );
				break;
			case 2:
			// none
				$params->set( 'marker_address', '' );
				$params->set( 'marker_email', '' );
				$params->set( 'marker_telephone', '' );
				$params->set( 'marker_fax', '' );
				$params->set( 'marker_misc', '' );
				$params->set( 'column_width', '0' );
				break;
			default:
			// icons
				$image1 = mosAdminMenus::ImageCheck( 'con_address.png', '/images/M_images/', $params->get( 'icon_address' ), NULL, $_LANG->_( 'Address' ) .": ", $_LANG->_( 'Address' ) .": " );
				$image2 = mosAdminMenus::ImageCheck( 'emailButton.png', '/images/M_images/', $params->get( 'icon_email' ), NULL, $_LANG->_( 'Email' ) .": ", $_LANG->_( 'Email' ) .": " );
				$image3 = mosAdminMenus::ImageCheck( 'con_tel.png', '/images/M_images/', $params->get( 'icon_telephone' ), NULL, $_LANG->_( 'Telephone' ) .": ", $_LANG->_( 'Telephone' ) .": " );
				$image4 = mosAdminMenus::ImageCheck( 'con_fax.png', '/images/M_images/', $params->get( 'icon_fax' ), NULL, $_LANG->_( 'Fax' ) .": ", $_LANG->_( 'Fax' ) .": " );
				$image5 = mosAdminMenus::ImageCheck( 'con_info.png', '/images/M_images/', $params->get( 'icon_misc' ), NULL, $_LANG->_( 'Information' ) .": ", $_LANG->_( 'Information' ) .": " );
				$params->set( 'marker_address', $image1 );
				$params->set( 'marker_email', $image2 );
				$params->set( 'marker_telephone', $image3 );
				$params->set( 'marker_fax', $image4 );
				$params->set( 'marker_misc', $image5 );
				$params->set( 'column_width', '40' );
				break;
		}

		// params from menu item
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$menu_params = new mosParameters( $menu->params );

		$menu_params->def( 'page_title', 1 );
		$menu_params->def( 'header', $menu->name );
		$menu_params->def( 'pageclass_sfx', '' );

		HTML_contact::viewcontact( $contact, $params, $count, $list, $menu_params );
	} else {
		$params = new mosParameters( '' );
		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );
		HTML_contact::nocontact( $params );
	}
}


function sendmail( $con_id, $option ) {
	global $database, $Itemid;
	global $mosConfig_sitename, $mosConfig_live_site, $mosConfig_mailfrom, $mosConfig_fromname;
	global $_LANG;

	$query = "SELECT *"
	. "\n FROM #__contact_details"
	. "\n WHERE id = $con_id"
	;
	$database->setQuery( $query );
	$contact 	= $database->loadObjectList();

	$default 	= $mosConfig_sitename.' '. $_LANG->_( 'Enquiry' );
	$email 		= mosGetParam( $_POST, 'email', '' );
	$text 		= mosGetParam( $_POST, 'text', '' );
	$name 		= mosGetParam( $_POST, 'name', '' );
	$subject 	= mosGetParam( $_POST, 'subject', $default );
	$email_copy = mosGetParam( $_POST, 'email_copy', 0 );

	if ( !$email || !$text || ( is_email( $email )==false ) ) {
		mosErrorAlert( $_LANG->_( 'CONTACT_FORM_NC' ) );
	}
	$prefix = sprintf( $_LANG->_( 'ENQUIRY_TEXT' ), $mosConfig_live_site );
	$text 	= $prefix ."\n". $name. ' <'. $email .'>' ."\n\n". stripslashes( $text );

	mosMail( $email, $name , $contact[0]->email_to, $mosConfig_fromname .': '. $subject, $text );

	if ( $email_copy ) {
		$copy_text = sprintf( $_LANG->_( 'Copy of:' ), $contact[0]->name, $mosConfig_sitename );
		$copy_text = $copy_text ."\n\n". $text;
		$copy_subject = $_LANG->_( 'Copy of:' ) ." ". $subject;
		mosMail( $mosConfig_mailfrom, $mosConfig_fromname, $email, $copy_subject, $copy_text );
	}
	?>
	<script>
	alert( "<?php echo $_LANG->_( 'Thank you for your e-mail' ); ?>" );
	document.location.href='<?php echo sefRelToAbs( 'index.php?option='. $option .'&Itemid='. $Itemid ); ?>';
	</script>
	<?php
}


function is_email($email){
	$rBool=false;

	if  ( preg_match( "/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/" , $email ) ){
		$rBool=true;
	}
	return $rBool;
}

function vCard( $id ) {
	global $database;
	global $mosConfig_sitename, $mosConfig_live_site;

	$contact	= new mosContact( $database );
	$contact->load( $id );
	$name 	= explode( ' ', $contact->name );
	$count 	= count( $name );

	// handles conversion of name entry into firstname, surname, middlename distinction
	$surname	= '';
	$middlename	= '';

	switch( $count ) {
		case 1:
			$firstname		= $name[0];
			break;

		case 2:
			$firstname 		= $name[0];
			$surname		= $name[1];
			break;

		default:
			$firstname 		= $name[0];
			$surname		= $name[$count-1];
			for ( $i = 1; $i < $count - 1 ; $i++ ) {
				$middlename	.= $name[$i] .' ';
			}
			break;
	}
	$middlename	= trim( $middlename );

	$v 	= new MambovCard();

	$v->setPhoneNumber( $contact->telephone, 'PREF;WORK;VOICE' );
	$v->setPhoneNumber( $contact->fax, 'WORK;FAX' );
	$v->setName( $surname, $firstname, $middlename, '' );
	$v->setAddress( '', '', $contact->address, $contact->suburb, $contact->state, $contact->postcode, $contact->country, 'WORK;POSTAL' );
	$v->setEmail( $contact->email_to );
	$v->setNote( $contact->misc );
	$v->setURL( $mosConfig_live_site, 'WORK' );
	$v->setTitle( $contact->con_position );
	$v->setOrg( $mosConfig_sitename );

	$filename	= str_replace( ' ', '_', $contact->name );
	$v->setFilename( $filename );

	$output 	= $v->getVCard( $mosConfig_sitename );
	$filename 	= $v->getFileName();

	// header info for page
	header( 'Content-Disposition: attachment; filename='. $filename );
	header( 'Content-Length: '. strlen( $output ) );
	header( 'Connection: close' );
	header( 'Content-Type: text/x-vCard; name='. $filename );	
	header( 'Cache-Control: store, cache' );
	header( 'Pragma: cache' );

	print $output;
}
?>
