<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Utilities
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Include library dependencies
jimport('joomla.filter.input');

/**
* Replaces &amp; with & for xhtml compliance
*
* Needed to handle unicode conflicts due to unicode conflicts
*
* @since 1.0
*/
function ampReplace( $text ) {
	$text = str_replace( '&&', '*--*', $text );
	$text = str_replace( '&#', '*-*', $text );
	$text = str_replace( '&amp;', '&', $text );
	$text = preg_replace( '|&(?![\w]+;)|', '&amp;', $text );
	$text = str_replace( '*-*', '&#', $text );
	$text = str_replace( '*--*', '&&', $text );

	return $text;
}

function mosTreeRecurse( $id, $indent, $list, &$children, $maxlevel=9999, $level=0, $type=1 ) {
	if (@$children[$id] && $level <= $maxlevel) {
		foreach ($children[$id] as $v) {
			$id = $v->id;

			if ( $type ) {
				$pre 	= '<sup>L</sup>&nbsp;';
				$spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			} else {
				$pre 	= '- ';
				$spacer = '&nbsp;&nbsp;';
			}

			if ( $v->parent == 0 ) {
				$txt 	= $v->name;
			} else {
				$txt 	= $pre . $v->name;
			}
			$pt = $v->parent;
			$list[$id] = $v;
			$list[$id]->treename = "$indent$txt";
			$list[$id]->children = count( @$children[$id] );
			$list = mosTreeRecurse( $id, $indent . $spacer, $list, $children, $maxlevel, $level+1, $type );
		}
	}
	return $list;
}

/**
* Utility function to provide ToolTips
*
* @param string ToolTip text
* @param string Box title
* @returns HTML code for ToolTip
* @since 1.0
*/
function mosToolTip( $tooltip, $title='', $width='', $image='tooltip.png', $text='', $href='', $link=1 )
{
	global $mainframe;

	$lang =& JFactory::getLanguage();

	$tooltip	= addslashes(htmlspecialchars($tooltip));
	$title		= addslashes(htmlspecialchars($title));

	$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

	if ( $width ) {
		$width = ', WIDTH, \''.$width .'\'';
	}

	if ( $title ) {
		$title = ', CAPTION, \''. JText::_( $title ) .'\'';
	}

	if ( !$text ) {
		$image 	= $url . 'includes/js/ThemeOffice/'. $image;
		$text 	= '<img src="'. $image .'" border="0" alt="'. JText::_( 'Tooltip' ) .'"/>';
	} else {
		$text 	= JText::_( $text, true );
	}

	$style = 'style="text-decoration: none; color: #333;"';

	if ( $href ) {
		$href = ampReplace( $href );
		$style = '';
	}
	$pos = $lang->isRTL() ? 'LEFT': 'RIGHT';
	$mousover = 'return overlib(\''. JText::_( $tooltip, true ) .'\''. $title .', BELOW, '.$pos. $width .');';

	$tip = '<!--'. JText::_( 'Tooltip' ) .'--> \n';
	if ( $link ) {
		$tip = '<a href="'. $href .'" onmouseover="'. $mousover .'" onmouseout="return nd();" '. $style .'>'. $text .'</a>';
	} else {
		$tip = '<span onmouseover="'. $mousover .'" onmouseout="return nd();" '. $style .'>'. $text .'</span>';
	}

	return $tip;
}

/**
 * Function to convert an internal Joomla URL to a humanly readible URL.
 *
 * @param	string	$string	The internal URL
 * @return	string	The absolute search engine friendly URL
 * @since	1.0
 */
function sefRelToAbs($value)
{
	global $mainframe, $Itemid, $option;

	static $strings;

	if (!$strings) {
		$strings = array();
	}

	// Replace all &amp; with & - ensures cache integrity
	$string = str_replace('&amp;', '&', $value);

	if (!isset( $strings[$string] ))
	{
		// Initialize some variables
		$config	= & JFactory::getConfig();
		$params = array();

		// Get config variables
		$mode    = $config->getValue('config.sef_rewrite');
		$rewrite = $config->getValue('config.sef');

		// Home index.php
		if ($string == 'index.php') {
			$string = '';
		}

		// decompose link into url component parts
		$uri  =& JURI::getInstance($string);
		$menu =& JMenu::getInstance();

		// If the itemid isn't set in the URL use default
		if(!$itemid = $uri->getVar('Itemid'))
		{
			if($itemid = JRequest::getVar('Itemid')) {
				$uri->setVar('Itemid', $itemid);
			}
		}

		// rewite URL
		if ($itemid && $rewrite && !eregi("^(([^:/?#]+):)", $string) && !strcasecmp(substr($string, 0, 9), 'index.php'))
		{
			$route = ''; //the route created

			// get the menu item for the itemid
			$item = $menu->getItem($itemid);

			// Build component name and sef handler path
			$path = JPATH_BASE.DS.'components'.DS.$item->component.DS.'request.php';

			$uri->delVar('option'); //don't need the option anymore
			$uri->delVar('Itemid'); //don't need the itemid anymore
			$query = $uri->getQuery(true);

			// Use the custom request handler if it exists
			if (file_exists($path))
			{
				require_once $path;
				$function	= substr($item->component, 4).'BuildURL';
				$parts		= $function($query, $params);

				$route = implode('/', $parts);
				$route = ($route) ? $route : null;

				$uri->setQuery($query);
			}

			// get the query
			$query = $uri->getQuery();

			// check if link contained fragment identifiers (ex. #foo)
			$fragment = null;
			if ($fragment = $uri->getFragment()) {
				// ensure fragment identifiers are compatible with HTML4
				if (preg_match('@^[A-Za-z][A-Za-z0-9:_.-]*$@', $fragment)) {
					$fragment = '#'.$fragment;
				}
			}

			if($query) {
				$query = '?'.$query;
			}


			$url = $item->name_alias.'/'.$route.$fragment.$query;

			// Prepend the base URI if we are not using mod_rewrite
			if (!$mode) {
				$url = 'index.php/'.$url;
			}
			$strings[$string] = $url;

			return str_replace( '&', '&amp;', $url );
		}

		$strings[$string] = $uri->toString();
	}

	return str_replace( '&', '&amp;', $strings[$string] );
}
?>
