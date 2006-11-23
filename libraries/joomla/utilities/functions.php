<?php
/**
 * @version $Id$
 * @package		Joomla.Framework
 * @subpackage	Utilities
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
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
* @package Joomla.Framework
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

function josErrorAlert( $text, $action='window.history.go(-1);', $mode=1 ) {
	$text = nl2br( $text );
	$text = addslashes( $text );
	$text = strip_tags( $text );

	switch ( $mode ) {
		case 2:
			echo "<script>$action</script> \n";
			break;

		case 1:
		default:
			echo "<script>alert('$text'); $action</script> \n";
			echo '<noscript>';
			echo "$text\n";
			echo '</noscript>';
			break;
	}

	exit;
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
 * @package Joomla.Framework
 * @param string SQL with ordering As value and 'name field' AS text
 * @param integer The length of the truncated headline
 * @since 1.0
 */
function mosGetOrderingList( $sql, $chop='30' ) {

	$db =& JFactory::getDBO();
	$order = array();
	$db->setQuery( $sql );
	if (!($orders = $db->loadObjectList())) {
		if ($db->getErrorNum()) {
			echo $db->stderr();
			return false;
		} else {
			$order[] = JHTMLSelect::option( 1, JText::_( 'first' ) );
			return $order;
		}
	}
	$order[] = JHTMLSelect::option( 0, '0 '. JText::_( 'first' ) );
	for ($i=0, $n=count( $orders ); $i < $n; $i++) {

		if (JString::strlen($orders[$i]->text) > $chop) {
			$text = JString::substr($orders[$i]->text,0,$chop)."...";
		} else {
			$text = $orders[$i]->text;
		}

		$order[] = JHTMLSelect::option( $orders[$i]->value, $orders[$i]->value.' ('.$text.')' );
	}
	$order[] = JHTMLSelect::option( $orders[$i-1]->value+1, ($orders[$i-1]->value+1).' '. JText::_( 'last' ) );

	return $order;
}

/**
* Utility function to provide ToolTips
*
* @package Joomla.Framework
* @param string ToolTip text
* @param string Box title
* @returns HTML code for ToolTip
* @since 1.0
*/
function mosToolTip( $tooltip, $title='', $width='', $image='tooltip.png', $text='', $href='', $link=1 )
{
	global $mainframe;

	$lang = JFactory::getLanguage();

	$tooltip = addslashes(htmlspecialchars($tooltip));
	$title   = addslashes(htmlspecialchars($title));

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
?>