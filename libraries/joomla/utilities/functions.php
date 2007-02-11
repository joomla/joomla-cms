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

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

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
 * Function to convert an internal Joomla URL to a humanly readible URL.
 *
 * @param	string	$string	The internal URL
 * @return	string	The absolute search engine friendly URL
 * @since	1.0
 */
function sefRelToAbs($value)
{
	global $mainframe;
	
	$router = $mainframe->getRouter();
	$route = $router->build($value);
	
	return $route;
}
?>
