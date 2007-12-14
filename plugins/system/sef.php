<?php
/**
* @version		$Id: sef.php 9527 2007-12-08 23:43:21Z robs $
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

jimport( 'joomla.plugin.plugin');

/**
* Joomla! SEF Plugin
*
* @package 		Joomla
* @subpackage	System
*/
class plgSystemSef extends JPlugin {


	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param	object		$subject The object to observe
	  * @param 	array  		$config  An array that holds the plugin configuration
	 * @since	1.0
	 */	
	function plgSystemSef(&$subject, $config) 
	{
		parent::__construct($subject, $config);
	}

	function onAfterRender()
	{
		global $mainframe;
		// check to see of SEF is enabled
		if(!$mainframe->getCfg('sef')) {
			return true;
		}
		if($mainframe->isAdmin()) {
			return true;
		}
		$document = JResponse::getBody();
		// check whether plugin has been unpublished
		
		//Replace src links
		$base = JURI::base(true).'/';
		$document = preg_replace("/(src)=\"(?!http|ftp|https|\/)([^\"]*)\"/", "$1=\"$base\$2\"", $document);

		//Replace href links
		$regex = "#href=\"(.*?)\"#s";

		// perform the replacement
		$document = preg_replace_callback( $regex, array($this, 'plgContentSEF_replacer'), $document );
		JResponse::setBody($document);
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
		if ( JString::strpos( $matches[1], 'index.php?option' ) !== false )
		{
			return 'href="'. JRoute::_( 'index.php' . $qstring ) . $uriHREF->getFragment() .'"';
		}
		
		if(JString::strpos( $matches[1], 'http://' ) === false && JString::strpos( $matches[1], 'https:' ) === false && is_null($uriHREF->getHost())) {
			//Relative link
			$base = JURI::base();
            if(JString::strpos($matches[1], '/') === 0) $base = substr($base, 0, -1);
            $uriNew =& JFactory::getURI($base.$matches[1]);
			$href = 'href="'.$uriNew->toString().'"';
			return $href;
		}		
	
		return $original;
	}

}
