<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onPrepareContent', 'plgContentSEF' );

/**
* Converting internal relative links to SEF URLs
*
* <b>Usage:</b>
* <code><a href="...relative link..."></code>
*/
function plgContentSEF( &$row, &$params, $page=0 )
{
	global $mainframe;

	// check to see of SEF is enabled
	if(!$mainframe->getCfg('sef')) {
		return true;
	}

	// check whether plugin has been unpublished
	if ( !JPluginHelper::isEnabled('content', 'sef')) {
		return true;
	}

	//Replace src links
	$base = JURI::base(true).'/';
	$row->text = preg_replace("/(src)=\"(?!http|ftp|https|\/)([^\"]*)\"/", "$1=\"$base\$2\"", $row->text);

	//Replace href links
	$regex = "#href=\"(.*?)\"#s";

	// perform the replacement
	$row->text = preg_replace_callback( $regex, 'plgContentSEF_replacer', $row->text );

	return true;
}
/**
* Replaces the matched tags
* @param array An array of matches (see preg_match_all)
* @return string
*/
function plgContentSEF_replacer( &$matches )
{
	// original text that might be replaced
	$original = 'href="'. $matches[1] .'"';

	// array list of non http/https	URL schemes
	$url_schemes = array( 'data:', 'file:', 'ftp:', 'gopher:', 'imap:', 'ldap:', 'mailto:', 'news:', 'nntp:', 'telnet:', 'javascript:', 'irc:' );

	foreach ( $url_schemes as $url )
	{
		// disable bot from being applied to specific URL Scheme tag
		if ( JString::strpos($matches[1], $url) !== false )
		{
			return $original;
		}
	}

	// will only process links containing 'index.php?option
	if ( JString::strpos( $matches[1], 'index.php?option' ) !== false )
	{
		$uriLocal	=& JFactory::getURI();
		$uriHREF	=& JFactory::getURI($matches[1]);

		//disbale bot from being applied to external links
		if($uriLocal->getHost() !== $uriHREF->getHost() && !is_null($uriHREF->getHost()))
		{
			return $original;
		}

		if ($qstring = $uriHREF->getQuery())
		{
			$qstring = '?' . $qstring;
		}
		if ($anchor = $uriHREF->getFragment())
		{
			$anchor = '#' . $anchor;
		}
		return 'href="'. JRoute::_( 'index.php' . $qstring ) . $uriHREF->getFragment() .'"';
	}
	else
	{
		return $original;
	}
}