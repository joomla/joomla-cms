<?php
/**
* @version $Id$
* @package Joomla
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

$mainframe->registerEvent( 'onPrepareContent', 'pluginEmailCloak' );

/**
* Plugin that Cloaks all emails in content from spambots via javascript
*/
function pluginEmailCloak( &$row, &$params, $page=0 ) {
	// simple performance check to determine whether bot should process further
	if ( JString::strpos( $row->text, '@' ) === false ) {
		return true;
	}

 	$plugin =& JPluginHelper::getPlugin('content', 'emailcloak');

	// check whether plugin has been unpublished
	if ( !$plugin->published ) {
		return true;
	}

	// check for presence of {emailcloak=off} which is explicits disables this bot for the item
	if ( !JString::strpos( $row->text, '{emailcloak=off}' ) === false ) {
		$row->text = JString::str_replace( '{emailcloak=off}', '', $row->text );
		return true;
	}

	// load plugin params info
 	$pluginParams 	= new JParameter( $plugin->params );
 	$mode			= $pluginParams->def( 'mode', 1 );

	// any@email.address.com
	$search_email		= "([[:alnum:]_\.\-]+)(\@[[:alnum:]\.\-]+\.+)([[:alnum:]\.\-]+)";
	// any@email.address.com?subject=anyText
	$search_email_msg   = "([[:alnum:]_\.\-]+)(\@[[:alnum:]\.\-]+\.+)([[:alnum:]\.\-]+)([[:alnum:][:space:][:punct:]][^\"<>]+)";
	// anyText
	$search_text 		= "([[:alnum:][:space:][:punct:]][^<>]+)";

	// search for derivativs of link code <a href="mailto:email@amail.com">email@amail.com</a>
	$pattern = botMosEmailCloak_searchPattern( $search_email, $search_email );
	while( eregi( $pattern, $row->text, $regs ) ) {
		$mail 		= $regs[2] . $regs[3] . $regs[4];
		$mail_text 	= $regs[5] . $regs[6] . $regs[7];

		// check to see if mail text is different from mail addy
		if ( $mail_text ) {
			$replacement = mosHTML::emailCloaking( $mail, $mode, $mail_text );
		} else {
			$replacement = mosHTML::emailCloaking( $mail, $mode );
		}

		// replace the found address with the js cloacked email
		$row->text 	= str_replace( $regs[0], $replacement, $row->text );
	}

	// search for derivativs of link code <a href="mailto:email@amail.com">anytext</a>
	$pattern = botMosEmailCloak_searchPattern( $search_email, $search_text );
	while( eregi( $pattern, $row->text, $regs ) ) {
		$mail 		= $regs[2] . $regs[3] . $regs[4];
		$mail_text 	= $regs[5];

		$replacement = mosHTML::emailCloaking( $mail, $mode, $mail_text, 0 );

		// replace the found address with the js cloacked email
		$row->text 	= str_replace( $regs[0], $replacement, $row->text );
	}

	// search for derivativs of link code <a href="mailto:email@amail.com?subject=Text">email@amail.com</a>
	$pattern = botMosEmailCloak_searchPattern( $search_email_msg, $search_email );
	while( eregi( $pattern, $row->text, $regs ) ) {
		$mail		= $regs[2] . $regs[3] . $regs[4] . $regs[5];
		$mail_text	= $regs[6] . $regs[7]. $regs[8];
		//needed for handling of Body parameter
		$mail 		= str_replace( '&amp;', '&', $mail );

		// check to see if mail text is different from mail addy
		if ( $mail_text ) {
			$replacement = mosHTML::emailCloaking( $mail, $mode, $mail_text );
		} else {
			$replacement = mosHTML::emailCloaking( $mail, $mode );
		}


		// replace the found address with the js cloacked email
		$row->text     = str_replace( $regs[0], $replacement, $row->text );
	}

	// search for derivativs of link code <a href="mailto:email@amail.com?subject=Text">anytext</a>
	$pattern = botMosEmailCloak_searchPattern( $search_email_msg, $search_text );
	while( eregi( $pattern, $row->text, $regs ) ) {
		$mail		= $regs[2] . $regs[3] . $regs[4] . $regs[5];
		$mail_text	= $regs[6];
		//needed for handling of Body parameter
		$mail 		= str_replace( '&amp;', '&', $mail );

		$replacement = mosHTML::emailCloaking( $mail, $mode, $mail_text, 0 );

		// replace the found address with the js cloacked email
		$row->text     = str_replace( $regs[0], $replacement, $row->text );
	}

	// search for plain text email@amail.com
	while( eregi( $search_email, $row->text, $regs ) ) {
		$mail = $regs[0];

		$replacement = mosHTML::emailCloaking( $mail, $mode );

		// replace the found address with the js cloacked email
		$row->text = str_replace( $regs[0], $replacement, $row->text );
	}
}

function botMosEmailCloak_searchPattern ( $link, $text ) {
	// <a href="mailto:anyLink">anyText</a>
	$pattern = "(<a [[:alnum:] _\"\'=\@\.\-]*href=[\"\']mailto:". $link	."[\"\'][[:alnum:] _\"\'=\@\.\-]*)>". $text ."</a>";

	return $pattern;
}
?>