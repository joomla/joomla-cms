<?php
/**
* @version $Id: mossef.php 2412 2006-02-16 17:24:10Z stingrey $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onPrepareContent', 'convertSEF' );

/**
* Converting internal relative links to SEF URLs
*
* <b>Usage:</b>
* <code><a href="...relative link..."></code>
*/
function convertSEF( &$row, &$params, $page=0 ) 
{
	global $mainframe;
	
	// check to see of SEF is enabled
	if(!$mainframe->getCfg('sef')) {
		return true;
	}
	
	// simple performance check to determine whether bot should process further
	if ( strpos( $row->text, 'href="' ) === false ) {
		return true;
	}
	
	$plugin =& JPluginHelper::getPlugin('content', 'sef'); 

	// check whether plugin has been unpublished
	if ( !$plugin->published ) {
		return true;
	}

	// define the regular expression for the bot
	$regex = "#href=\"(.*?)\"#s";

	// perform the replacement
	$row->text = preg_replace_callback( $regex, 'contentSEF_replacer', $row->text );

	return true;
}
/**
* Replaces the matched tags
* @param array An array of matches (see preg_match_all)
* @return string
*/
function contentSEF_replacer( &$matches ) 
{
	// disable bot from being applied to mailto tags
	if (strstr($matches[1],'mailto:')) {
		return 'href="'. $matches[1] .'"';
	}
	
	$uriLocal =& JURI::getInstance();
	$uriHREF  =& JURI::getInstance($matches[1]);
	
	//disbale bot from being applied to external links
	if($uriLocal->getHost() !== $uriHREF->getHost() && !is_null($uriHREF->getHost())) {
		return 'href="'. $matches[1] .'"';
	}
	
	return 'href="'. sefRelToAbs( 'index.php' . $uriHREF->getQueryString() ) . $uriHREF->getAnchor() .'"';
}
?>