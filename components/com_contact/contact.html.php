<?php
/**
* @version $Id: contact.html.php 137 2005-09-12 10:21:17Z eddieajau $
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
 * @package Joomla
 * @subpackage Contact
 */
class contactScreens_front {
	/**
	 * @param string The main template file to include for output
	 * @param array An array of other standard files to include
	 * @return patTemplate A template object
	 */
	function &createTemplate( $bodyHtml='', $files=null ) {
		$tmpl =& mosFactory::getPatTemplate( $files );

		$directory = mosComponentDirectory( $bodyHtml, dirname( __FILE__ ) );
		$tmpl->setRoot( $directory );

		$tmpl->setAttribute( 'body', 'src', $bodyHtml );

		return $tmpl;
	}

	function list_section( &$params, &$current, &$cats ) {
		global $_MAMBOTS;
		global $_LANG;

		// process the new bots
		$current->text = $current->descrip;
		$_MAMBOTS->loadBotGroup( 'content' );
		$results = $_MAMBOTS->trigger( 'onPrepareContent', array( &$current, &$params ), true );

		$tmpl =& contactScreens_front::createTemplate( 'list-section.html' );

		$tmpl->addVar( 'body', 'show_image',			( $current->img ? 1 : 0 ) );

		$tmpl->addObject( 'current', $current, 'cur_' );

		// category list params
		$tmpl->addObject( 'categories', $cats, 'cat_' );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->displayParsedTemplate( 'body' );
	}

	function table_category( &$params, &$current, &$cats, &$rows ) {
		global $_MAMBOTS;
		global $_LANG;

		// process the new bots
		$current->text = $current->descrip;
		$_MAMBOTS->loadBotGroup( 'content' );
		$results = $_MAMBOTS->trigger( 'onPrepareContent', array( &$current, &$params ), true );

		$tmpl =& contactScreens_front::createTemplate( 'table-category.html' );

		$tmpl->addVar( 'body', 'show_image',			( $current->img ? 1 : 0 ) );

		$tmpl->addObject( 'current', $current, 'cur_' );

		// table item params
		$tmpl->addObject( 'rows', $rows, 'row_' );

		// category list params
		$tmpl->addObject( 'body-list-cat', $cats, 'cat_' );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->displayParsedTemplate( 'body' );
	}

	function item( &$params, &$menu_params, &$contact, $list ) {
		global $mainframe, $Itemid;
		global $mosConfig_live_site;

		$template	= $mainframe->getTemplate();
		$back 		= mosHTML::BackButton ( $params, 0, 0 );
		$close 		= mosHTML::CloseButton ( $params, 0, 0 );

		// displays Print Icon
		$print_link = $mosConfig_live_site. '/index2.php?option=com_contact&amp;task=view&amp;contact_id='. $contact->id .'&amp;Itemid='. $Itemid .'&amp;pop=1';
		$print 		= mosHTML::PrintIcon( $contact, $params, 0, $print_link, '', 0 );

		$show_email_form	= ( $contact->email_to && !$params->get( 'popup' ) && $params->get( 'email_form' ) ? 1 : 0 );
		$show_dropdown		= ( ( $contact->count > 1 )  && !$params->get( 'popup' ) && $params->get( 'drop_down' ) );
		$show_page_header	= ( $params->get( 'page_title' )  && !$params->get( 'popup' ) 	? 1 : 0 );

		$show_name			= ( $contact->name && $params->get( 'name' ) 					? 1 : 0 );
		$show_position		= ( $contact->con_position && $params->get( 'position' ) 		? 1 : 0 );
		$show_image			= ( $contact->image && $params->get( 'image' ) 					? 1 : 0 );
		$show_info_address	= ( $contact->address && $params->get( 'street_address' ) 		? 1 : 0 );
		$show_info_suburb	= ( $contact->suburb && $params->get( 'suburb' ) 				? 1 : 0 );
		$show_info_state	= ( $contact->state && $params->get( 'state' ) 					? 1 : 0 );
		$show_info_country	= ( $contact->country && $params->get( 'country' ) 				? 1 : 0 );
		$show_info_postcode	= ( $contact->postcode && $params->get( 'postcode' ) 			? 1 : 0 );

		$show_info_misc		= ( $contact->misc && $params->get( 'misc' ) 					? 1 : 0 );
		$show_info_fax		= ( $contact->fax && $params->get( 'fax' ) 						? 1 : 0 );
		$show_info_phone	= ( $contact->telephone && $params->get( 'telephone' ) 			? 1 : 0 );
		$show_info_email	= ( $contact->email_to && $params->get( 'email' ) 				? 1 : 0 );

		$show_contact_contact	= ( $show_info_fax || $show_info_phone || $show_info_email	? 1 : 0  );
		$show_contact_address	= ( $show_info_address || $show_info_suburb || $show_info_state || $show_info_country || $show_info_postcode ? 1 : 0 );

		$show_email_descrip = ( $params->get( 'email_description' ) 						? 1 : 0 );

		$n = count( $list );
		for ( $i = 0; $i < $n; $i++ ) {
			$link 				= sefRelToAbs( 'index.php?option=com_contact&task=view&contact_id='. $list[$i]->value .'&Itemid='. $Itemid );
		   	$js_link[$i]->link 	= "\n links[".$list[$i]->value."] = '$link';";
		}

		$tmpl =& contactScreens_front::createTemplate( 'item.html' );

		$tmpl->addVar( 'body', 'print_icon', 		$print );
		$tmpl->addVar( 'body', 'show_back_button', 	( ( $params->get( 'back_button' ) && !$params->get( 'popup' ) ) ? 1 : 0 ) );

		$tmpl->addVar( 'body', 'template_css',		$mosConfig_live_site .'/templates/'. $template .'/css/template_css.css'	);
		$tmpl->addObject( 'body-list-js', $js_link, 'js_' );

		// contact variables
		$tmpl->addObject( 'contact', $contact, 'contact_' );
		$tmpl->addVar( 'body', 'contact_form_url', sefRelToAbs( 'index.php?option=com_contact&amp;Itemid='. $Itemid ) );

		// show contact variables
		$tmpl->addVar( 'body', 'show_email_form', 	$show_email_form );
		$tmpl->addVar( 'body', 'show_dropdown', 	$show_dropdown );

		$tmpl->addVar( 'body', 'show_info_name', 		$show_name );
		$tmpl->addVar( 'body', 'show_info_position', 	$show_position );
		$tmpl->addVar( 'body', 'show_info_image', 		$show_image );

		$tmpl->addVar( 'body', 'show_contact_address', 	$show_contact_address );
		$tmpl->addVar( 'body', 'show_info_address', 	$show_info_address );
		$tmpl->addVar( 'body', 'show_info_suburb', 		$show_info_suburb );
		$tmpl->addVar( 'body', 'show_info_state', 		$show_info_state );
		$tmpl->addVar( 'body', 'show_info_country',		$show_info_country );
		$tmpl->addVar( 'body', 'show_info_postcode', 	$show_info_postcode );
		$tmpl->addVar( 'body', 'show_info_misc', 		$show_info_misc );

		$tmpl->addVar( 'body', 'show_contact_contact', 	$show_contact_contact );
		$tmpl->addVar( 'body', 'show_info_fax', 		$show_info_fax );
		$tmpl->addVar( 'body', 'show_info_phone', 		$show_info_phone );
		$tmpl->addVar( 'body', 'show_info_email', 		$show_info_email );
		$tmpl->addVar( 'body', 'show_marker_address', 	$params->get( 'address_check' ) > 0 );

		// email variables
		$tmpl->addVar( 'body', 'email_form_url', 		sefRelToAbs( 'index.php?option=com_contact&amp;Itemid='. $Itemid ) );
		$tmpl->addVar( 'body', 'email_url', 			sefRelToAbs( 'index.php?option=com_contact&amp;Itemid='. $Itemid ) );
		$tmpl->addVar( 'body', 'show_email_descrip',	$show_email_descrip );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );
		$tmpl->addObject( 'body', $menu_params->toObject(), 'm_' );

		$tmpl->displayParsedTemplate( 'body' );
	}
}
?>