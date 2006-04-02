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
function pluginEmailCloak( &$row, &$params, $page=0 ) 
{
	global $database;

	// simple performance check to determine whether bot should process further
	if ( strpos( $row->text, '@' ) === false ) {
		return true;
	}
	
 	$plugin =& JPluginHelper::getPlugin('content', 'emailcloak'); 

	// check whether plugin has been unpublished
	if ( !$plugin->published ) {
		return true;
	}

	// check for presence of {emailcloak=off} which is explicits disables this bot for the item
	if ( !strpos( $row->text, '{emailcloak=off}' ) === false ) {		
		$row->text = str_replace( '{emailcloak=off}', '', $row->text );
		return true;
	}
	
	// load plugin params info
 	$pluginParams = new JParameter( $plugin->params );
 	$mode		= $pluginParams->def( 'mode', 1 );

	$search_email		= "([[:alnum:]_\.\-]+)(\@[[:alnum:]\.\-]+\.+)([[:alnum:]\.\-]+)";
	$search_email_msg   = "([[:alnum:]_\.\-]+)(\@[[:alnum:]\.\-]+\.+)([[:alnum:]\.\-]+)([[:alnum:][:space:][:punct:]][^\"<>]+)";
	$search_text 		= "([[:alnum:][:space:][:punct:]][^<>]+)";
	
	// search for derivativs of link code <a href="mailto:email@amail.com">email@amail.com</a>
	$searchlink	= "(<a [[:alnum:] _\"\'=\@\.\-]*href=[\"\']mailto:". $search_email ."[\"\'][[:alnum:] _\"\'=\@\.\-]*>)". $search_email ."</a>";
	while( eregi( $searchlink, $row->text, $regs ) ) {
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
	$searchlink	= "(<a [[:alnum:] _\"\'=\@\.\-]*href=[\"\']mailto:". $search_email ."[\"\'][[:alnum:] _\"\'=\@\.\-]*)>". $search_text ."</a>";
	while( eregi( $searchlink, $row->text, $regs ) ) {
		$mail 		= $regs[2] . $regs[3] . $regs[4];
		$mail_text 	= $regs[5];
		
		$replacement = mosHTML::emailCloaking( $mail, $mode, $mail_text, 0 );
		
		// replace the found address with the js cloacked email
		$row->text 	= str_replace( $regs[0], $replacement, $row->text );
	}
	
	// search for derivativs of link code <a href="mailto:email@amail.com?subject=Text">email@amail.com</a>
	$searchlink		= "(<a [[:alnum:] _\"\'=\@\.\-]*href=[\"\']mailto:". $search_email_msg ."[\"\'][[:alnum:] _\"\'=\@\.\-]*)>". $search_email ."</a>";
	while( eregi( $searchlink, $row->text, $regs ) ) {
		
		$mail		= $regs[2] . $regs[3] . $regs[4] . $regs[5];
		$mail_text	= $regs[6] . $regs[7]. $regs[8];
		
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
	$searchlink		= "(<a [[:alnum:] _\"\'=\@\.\-]*href=[\"\']mailto:". $search_email_msg ."[\"\'][[:alnum:] _\"\'=\@\.\-]*)>". $search_text ."</a>";
	while( eregi( $searchlink, $row->text, $regs ) ) {
		
		$mail		= $regs[2] . $regs[3] . $regs[4] . $regs[5];
		$mail_text	= $regs[6];
		
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
?>