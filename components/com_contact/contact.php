<?php
/**
* @version $Id: contact.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Contact
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Contact
 * @subpackage Contact
 */
class contactTasks_front extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function contactTasks_front() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'display' );

		// set task level access control
		//$this->setAccessControl( 'com_templates', 'manage' );
	}

	function display() {
		global $mainframe, $database, $my, $Itemid;
		global $mosConfig_live_site;
		global $_LANG;

		// Parameters loading in advaced for check & further use
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );

		$catid	= intval( mosGetParam( $_REQUEST ,'catid', 0 ) );
		if( $catid == 0 ) {
			$catid = $params->get( 'catid', 0 );
		}


		/* Query to retrieve all categories that belong under the contacts section and that are published. */
		$query = "SELECT cc.*, COUNT( a.id ) AS numlinks"
		. "\n FROM #__categories AS cc"
		. "\n LEFT JOIN #__contact_details AS a ON a.catid = cc.id"
		. "\n WHERE a.published='1'"
		. "\n AND cc.section='com_contact_details'"
		. "\n AND cc.published='1'"
		. "\n AND a.access <= '$my->gid'"
		. "\n AND cc.access <= '$my->gid'"
		. "\n GROUP BY cc.id"
		. "\n ORDER BY cc.ordering"
		;
		$database->setQuery( $query );
		$categories = $database->loadObjectList();

		$count = count( $categories );
		for ( $i = 0; $i < $count; $i++ ) {
			$link 					= 'index.php?option=com_contact&amp;catid='. $categories[$i]->id .'&amp;Itemid='. $Itemid;
			$categories[$i]->link	= sefRelToAbs( $link );
		}

		if ( ( $count == 1 ) && ( @$categories[0]->numlinks == 1 ) ) {
		// if only one record exists loads that record, instead of displying category list
			$_REQUEST['contact_id'] = 0;
			contactTasks_front::view();
		} else {
			$rows 		= array();
			$currentcat = NULL;
			$type 		= 'section';

			if ( $catid ) {
				$type = 'category';

				// url links info for category
				$query = "SELECT *"
				. "\n FROM #__contact_details"
				. "\n WHERE catid = '$catid'"
				. "\n AND published='1'"
				. "\n AND access <= '$my->gid'"
				. "\n ORDER BY ordering"
				;
				$database->setQuery( $query );
				$rows = $database->loadObjectList();

				// used to show table rows in alternating colours
				$tabclass = array( 'sectiontableentry1', 'sectiontableentry2' );
				$count = count( $rows );
				$n = 0;
				for ( $i = 0; $i < $count; $i++ ) {
					$link 				= 'index.php?option=com_contact&amp;task=view&amp;contact_id='. $rows[$i]->id .'&amp;Itemid='. $Itemid;
					$rows[$i]->url		= sefRelToAbs( $link );
					$rows[$i]->class	= $tabclass[$n];
					if ( $rows[$i]->email_to ) {
						$rows[$i]->email_to = mosHTML::emailCloaking( $rows[$i]->email_to, 1 );
					}

					$n = 1 - $n;
				}

				// current category info
				$query = "SELECT name, description, image, image_position"
				. "\n FROM #__categories"
				. "\n WHERE id = '$catid'"
				. "\n AND published = '1'"
				. "\n AND access <= '$my->gid'"
				;
				$database->setQuery( $query );
				$database->loadObject( $currentcat );
			}

			// Parameters - object already load at the beginning

			$params->def( 'page_title', 		1 );
			$params->def( 'header', 			$menu->name );
			$params->def( 'pageclass_sfx', 		'' );
			$params->def( 'headings', 			1 );
			$params->def( 'back_button', 		$mainframe->getCfg( 'back_button' ) );
			$params->def( 'description_text', 	$_LANG->_( 'CONTACTS_DESC' ) );
			$params->def( 'image', 				-1 );
			$params->def( 'image_align', 		'right' );
			$params->def( 'other_cat_section',	1 );
			// Category List Display control
			$params->def( 'other_cat', 			1 );
			$params->def( 'cat_description', 	1 );
			$params->def( 'cat_items', 			1 );
			// Table Display control
			$params->def( 'headings', 			1 );
			$params->def( 'position', 			1 );
			$params->def( 'email', 				0 );
			$params->def( 'phone', 				0 );
			$params->def( 'fax', 				0 );
			$params->def( 'telephone', 			0 );
			$params->def( 'meta_key', 			'' );
			$params->def( 'meta_descrip', 		'' );
			$params->def( 'seo_title', 			$menu->name );
			$params->set( 'catid', 				$catid );

			$params->set( 'cat', 0 );
			if ( ( $type == 'category' ) && $params->get( 'other_cat' ) ) {
				$params->set( 'cat', 1 );
			} else if ( $type == 'section' && $params->get( 'other_cat_section' ) ) {
				$params->set( 'cat', 1 );
			}


			// page description
			$currentcat->descrip = '';
			if( isset( $currentcat->description ) && ( $currentcat->description <> '' ) ) {
				$currentcat->descrip = $currentcat->description;
			} else if ( !$catid ) {
				// show description
				if ( $params->get( 'description' ) ) {
					$currentcat->descrip = $params->get( 'description_text' );
				}
			}
			// page image
			$currentcat->img	= '';
			$path 				= $mosConfig_live_site .'/images/stories/';
			if ( isset( $currentcat->image ) && ( $currentcat->image <> '' ) ) {
				$currentcat->img 	= $path . $currentcat->image;
				$currentcat->align 	= $currentcat->image_position;
			} else if ( !$catid ) {
				if ( $params->get( 'image' ) <> -1 ) {
					$currentcat->img 	= $path . $params->get( 'image' );
					$currentcat->align 	= $params->get( 'image_align' );
				}
			}
			// page header
			$currentcat->header = '';
			if ( isset( $currentcat->name ) && ( $currentcat->name <> '' ) ) {
				$currentcat->header = $params->get( 'header' ) .' - '. $currentcat->name;
			} else {
				$currentcat->header = $params->get( 'header' );
			}

			// SEO Meta Tags
			$mainframe->setPageMeta( $params->get( 'seo_title' ), $params->get( 'meta_key' ), $params->get( 'meta_descrip' ) );

			mosFS::load( '@front_html' );

			if ( $catid ) {
				contactScreens_front::table_category( $params, $currentcat, $categories, $rows  );
			} else {
				contactScreens_front::list_section( $params, $currentcat, $categories );
			}
		}
	}

	function view() {
		global $mainframe, $database, $my, $Itemid;
		global $_LANG;

		$contact_id = intval( mosGetParam( $_REQUEST ,'contact_id', 0 ) );

		$query = "SELECT a.id AS value, CONCAT_WS( ' - ', a.name, a.con_position ) AS text"
		. "\n FROM #__contact_details AS a"
		. "\n LEFT JOIN #__categories AS cc ON cc.id = a.catid"
		. "\n WHERE a.published = '1'"
		. "\n AND cc.published = '1'"
		. "\n AND a.access <= '$my->gid'"
		. "\n AND cc.access <= '$my->gid'"
		. "\n ORDER BY a.default_con DESC, a.ordering ASC"
		;
		$database->setQuery( $query );
		$list = $database->loadObjectList();
		$count = count( $list );

		mosFS::load( '@front_html' );

		if ( $count ) {
			if ( $contact_id < 1 ) {
			    $contact_id = $list[0]->value;
			}

			$query = "SELECT *"
			. "\n FROM #__contact_details"
			. "\n WHERE published = '1'"
			. "\n AND id = '$contact_id'"
			. "\n AND access <= '$my->gid'"
			;
			$database->setQuery( $query );
			$contacts = $database->LoadObjectList();

			if (!$contacts){
				echo $_LANG->_( 'NOT_AUTH' );
				return;
			}
			$contact = $contacts[0];
			// creates dropdown select list
			$contact->select = mosHTML::selectList( $list, 'contact_id', 'class="inputbox" onchange="ViewCrossReference(this);"', 'value', 'text', $contact_id );

			// Adds parameter handling
			$params = new mosParameters( $contact->params );

			$params->set( 'page_title', 			0 );
			$params->def( 'pageclass_sfx', 			'' );
			$params->def( 'back_button', 			$mainframe->getCfg( 'back_button' ) );
			$params->def( 'print', 					!$mainframe->getCfg( 'hidePrint' ) );
			$params->def( 'position', 				1 );
			$params->def( 'name', 					1 );
			$params->def( 'email', 					0 );
			$params->def( 'street_address', 		1 );
			$params->def( 'suburb', 				1 );
			$params->def( 'state', 					1 );
			$params->def( 'country', 				1 );
			$params->def( 'postcode', 				1 );
			$params->def( 'telephone', 				1 );
			$params->def( 'fax', 					1 );
			$params->def( 'misc', 					1 );
			$params->def( 'image', 					1 );
			$params->def( 'email_description', 		1 );
			$params->def( 'email_description_text', $_LANG->_( 'EMAIL_DESCRIPTION' ) );
			$params->def( 'email_form', 			1 );
			$params->def( 'email_copy', 			1 );
			// global print|pdf|email
			$params->def( 'icons', 					$mainframe->getCfg( 'icons' ) );
			// contact only icons
			$params->def( 'contact_icons', 			0 );
			$params->def( 'icon_address', 			'' );
			$params->def( 'icon_email', 			'' );
			$params->def( 'icon_telephone', 		'' );
			$params->def( 'icon_fax', 				'' );
			$params->def( 'icon_misc', 				'' );
			$params->def( 'drop_down', 				0 );
			$params->def( 'vcard', 					1 );

			if ( $contact->email_to && $params->get( 'email' )) {
				// email cloacking
				$contact->email = mosHTML::emailCloaking( $contact->email_to );
			}

			$contact->vcard_link 	= sefRelToAbs( 'index2.php?option=com_contact&amp;task=vcard&amp;contact_id='. $contact->id .'&amp;no_html=1' );
			$contact->count 		= $count;
			$contact->category_link	= 'index.php?option=com_contact&amp;catid='. $contact->catid .'&amp;Itemid='. $Itemid;

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
					$params->set( 'marker_address', 	$_LANG->_( 'CONTACT_ADDRESS' ) );
					$params->set( 'marker_email', 		$_LANG->_( 'CONTACT_EMAIL' ) );
					$params->set( 'marker_telephone', 	$_LANG->_( 'CONTACT_TELEPHONE' ) );
					$params->set( 'marker_fax', 		$_LANG->_( 'CONTACT_FAX' ) );
					$params->set( 'marker_misc', 		$_LANG->_( 'CONTACT_MISC' ) );
					$params->set( 'column_width', 		'100' );
					break;
				case 2:
				// none
					$params->set( 'marker_address', 	'' );
					$params->set( 'marker_email', 		'' );
					$params->set( 'marker_telephone', 	'' );
					$params->set( 'marker_fax', 		'' );
					$params->set( 'marker_misc', 		'' );
					$params->set( 'column_width', 		'0px' );
					break;
				default:
				// icons
					$image1 = mosAdminMenus::ImageCheck( 'con_address.png', '/images/M_images/', $params->get( 'icon_address' ), 	'/images/M_images/', 'address' );
					$image2 = mosAdminMenus::ImageCheck( 'emailButton.png', '/images/M_images/', $params->get( 'icon_email' ), 		'/images/M_images/', 'email' );
					$image3 = mosAdminMenus::ImageCheck( 'con_tel.png', 	'/images/M_images/', $params->get( 'icon_telephone' ), 	'/images/M_images/', 'telephone' );
					$image4 = mosAdminMenus::ImageCheck( 'con_fax.png', 	'/images/M_images/', $params->get( 'icon_fax' ), 		'/images/M_images/', 'fax' );
					$image5 = mosAdminMenus::ImageCheck( 'con_info.png', 	'/images/M_images/', $params->get( 'icon_misc' ), 		'/images/M_images/', 'misc' );
					$params->set( 'marker_address', 	$image1 );
					$params->set( 'marker_email', 		$image2 );
					$params->set( 'marker_telephone', 	$image3 );
					$params->set( 'marker_fax', 		$image4 );
					$params->set( 'marker_misc',		$image5 );
					$params->set( 'column_width', 		'40' );
					break;
			}

			// params from menu item
			$menu = new mosMenu( $database );
			$menu->load( $Itemid );
			$menu_params = new mosParameters( $menu->params );

			$menu_params->def( 'page_title', 	1 );
			$menu_params->def( 'header', 		$menu->name );
			$menu_params->def( 'pageclass_sfx', '' );
			$menu_params->def( 'meta_key', 		'' );
			$menu_params->def( 'meta_descrip',	'' );
			$menu_params->def( 'seo_title', 	$menu->name );

			// SEO Meta Tags
			$mainframe->setPageMeta( $menu_params->get( 'seo_title' ), $menu_params->get( 'meta_key' ), $menu_params->get( 'meta_descrip' ) );

			contactScreens_front::item( $params, $menu_params, $contact, $list );
		} else {
			echo $_LANG->_( 'CONTACT_NONE' );
		}
	}

	function vcard() {
		global $database;
		global $mosConfig_sitename, $mosConfig_live_site;

		mosFS::load( '@class' );

		$id = intval( mosGetParam( $_REQUEST ,'contact_id', 0 ) );

		$contact 	= new mosContact( $database );
		$contact->load( $id );
		$name 		= explode( ' ', $contact->name );
		$count 		= count( $name );

		// Adds parameter handling
		$params = new mosParameters( $contact->params );

		$params->def( 'name', 			1 );
		$params->def( 'position', 		1 );
		$params->def( 'email', 			0 );
		$params->def( 'street_address', 1 );
		$params->def( 'suburb', 		1 );
		$params->def( 'state', 			1 );
		$params->def( 'country', 		1 );
		$params->def( 'postcode', 		1 );
		$params->def( 'telephone', 		1 );
		$params->def( 'fax', 			1 );
		$params->def( 'misc', 			1 );

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

		// name
		if ( $params->get( 'name' ) ) {
			$v->setName( $surname, $firstname, $middlename, '' );
		} else {
			$v->setName( $mosConfig_sitename, '', '', '' );
		}

		// title = position
		if ( $params->get( 'position' ) ) {
			$v->setTitle( $contact->con_position );
		}

		// address handling
		if ( !$params->get( 'street_address' ) ) {
			$contact->address = '';
		}
		if ( !$params->get( 'suburb' ) ) {
			$contact->suburb = '';
		}
		if ( !$params->get( 'state' ) ) {
			$contact->state = '';
		}
		if ( !$params->get( 'postcode' ) ) {
			$contact->postcode = '';
		}
		if ( !$params->get( 'country' ) ) {
			$contact->country = '';
		}
		$v->setAddress( '', '', $contact->address, $contact->suburb, $contact->state, $contact->postcode, $contact->country );

		// email
		if ( $params->get( 'email' ) ) {
			$v->setEmail( $contact->email_to );
		}

		// telephone
		if ( $params->get( 'telephone' ) ) {
			$v->setPhoneNumber( $contact->telephone, 'PREF;WORK;VOICE' );
		}

		// fax
		if ( $params->get( 'fax' ) ) {
			$v->setPhoneNumber( $contact->fax, 'WORK;FAX' );
		}

		// miscellanous info
		if ( $params->get( 'misc' ) ) {
			$v->setNote( $contact->misc );
		}

		// url of website
		$v->setURL( $mosConfig_live_site, 'WORK' );

		// organisation = sitename
		$v->setOrg( $mosConfig_sitename );

		// vcard filename
		$filename	= str_replace( ' ', '_', $contact->name );
		$v->setFilename( $filename );

		$output 	= $v->getVCard( $mosConfig_sitename );
		$filename 	= $v->getFileName();


		// header info for page
		header( 'Content-Disposition: attachment; filename='. $filename );
		header( 'Content-Length: '. strlen( $output ) );
		header( 'Connection: close' );
		header( 'Content-Type: text/x-vCard; name='. $filename );

		print $output;
	}

	function sendmail() {
		global $database, $Itemid;
		global $mosConfig_sitename, $mosConfig_live_site, $mosConfig_mailfrom, $mosConfig_fromname;
		global $option;
		global $_LANG;

		$con_id	= intval( mosGetParam( $_REQUEST ,'con_id', 0 ) );

		$query = "SELECT *"
		. "\n FROM #__contact_details"
		. "\n WHERE id = '$con_id'"
		;
		$database->setQuery( $query );
		$contact = $database->loadObjectList();

		$default 	= $mosConfig_sitename.' '. $_LANG->_( 'ENQUIRY' );
		$email 		= trim( mosGetParam( $_POST, 'email', '' ) );
		$text 		= trim( mosGetParam( $_POST, 'text', '' ) );
		$name 		= trim( mosGetParam( $_POST, 'name', '' ) );
		$subject 	= trim( mosGetParam( $_POST, 'subject', $default ) );
		$email_copy = mosGetParam( $_POST, 'email_copy', 0 );

		if ( !$email || !$text || ( is_email( $email )==false ) ) {
			mosErrorAlert( $_LANG->_( 'CONTACT_FORM_NC' ) );
		} else {
			$prefix = sprintf( $_LANG->_( 'ENQUIRY_TEXT' ), $mosConfig_live_site );
			$text 	= $prefix ."\n". $name. ' < '. $email .' >' ."\n\n". stripslashes( $text );

			// send email to contact email
			mosMail( $email, $name , $contact[0]->email_to, $mosConfig_fromname .': '. $subject, $text );

			// send copy of email to submitter
			if ( $email_copy ) {
				$copy_text 		= sprintf( $_LANG->_( 'COPY_TEXT' ), $contact[0]->name, $mosConfig_sitename );
				$copy_text 		= $copy_text ."\n\n". $text .'';
				$copy_subject 	= $_LANG->_( 'COPY_SUBJECT' ). $subject;

				mosMail( $mosConfig_mailfrom, $mosConfig_fromname, $email, $copy_subject, $copy_text );
			}
			?>
			<script>
			<!--
			alert( "<?php echo $_LANG->_( 'THANK_MESSAGE' ); ?>" );
			javascript:history.go(-1);
			//-->
			</script>
			<noscript>
				<center>
					<b><?php echo $_LANG->_( 'THANK_MESSAGE' ); ?></b>
				</center>
			</noscript>
			<?php
		}
	}
}
$tasker = new contactTasks_front();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();

function is_email($email){
	$rBool=false;

	if  ( preg_match( "/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/" , $email ) ){
		$rBool=true;
	}
	return $rBool;
}
?>