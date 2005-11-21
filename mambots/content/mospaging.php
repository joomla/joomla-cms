<?php
/**
* @version $Id$
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
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onPrepareContent', 'botMosPaging' );

/**
* Page break mambot
*
* <b>Usage:</b>
* <code>{mospagebreak}</code>
* <code>{mospagebreak title=The page title}</code>
* or
* <code>{mospagebreak heading=The first page}</code>
* or
* <code>{mospagebreak title=The page title&heading=The first page}</code>
* or
* <code>{mospagebreak heading=The first page&title=The page title}</code>
*
*/
function botMosPaging( $published, &$row, &$params, $page=0 ) {
	global $mainframe, $Itemid, $database;
	;

 	// expression to search for
 	$regex = '/{(mospagebreak)\s*(.*?)}/i';

	// check whether mambot has been unpublished
 	if (!$published || $params->get( 'intro_only' )|| $params->get( 'popup' )) {
		$row->text = preg_replace( $regex, '', $row->text );
		return;
	}

	// find all instances of mambot and put in $matches
	$matches = array();
	preg_match_all( $regex, $row->text, $matches, PREG_SET_ORDER );

	// split the text around the mambot
	$text = preg_split( $regex, $row->text );

	// count the number of pages
	$n = count( $text );

	// we have found at least one mambot, therefore at least 2 pages
	if ($n > 1) {
		// load mambot params info
		$query = "SELECT id"
		. "\n FROM #__mambots"
		. "\n WHERE element = 'mospaging'"
		. "\n AND folder = 'content'"
		;
		$database->setQuery( $query );
	 	$id 	= $database->loadResult();
	 	$mambot = new mosMambot( $database );
	  	$mambot->load( $id );
	 	$botParams = new mosParameters( $mambot->params );

	 	$title	= $botParams->def( 'title', 1 );

	 	// adds heading or title to <site> Title
	 	if ( $title ) {
			$page_text = $page + 1;
			$row->page_title = sprintf( JText::_( 'Page' ), $page_text );
			if ( !$page ) {
				// processing for first page
				parse_str( str_replace( '&amp;', '&', $matches[0][2] ), $args );

				if ( @$args['heading'] ) {
					$row->page_title = $args['heading'];
				} else {
					$row->page_title = '';
				}
			} else if ( $matches[$page-1][2] ) {
				parse_str(  str_replace( '&amp;', '&', $matches[$page-1][2] ), $args );

				if ( @$args['title'] ) {
					$row->page_title = stripslashes( $args['title'] );
				}
			}
	 	}

		// reset the text, we already hold it in the $text array
		$row->text = '';

		$hasToc = $mainframe->getCfg( 'multipage_toc' );

		if ( $hasToc ) {
			// display TOC
			createTOC( $row, $matches, $page );
		} else {
			$row->toc = '';
		}

		// traditional mos page navigation
		require_once( JPATH_SITE . '/includes/pageNavigation.php' );
		$pageNav = new mosPageNav( $n, $page, 1 );

		// page counter
		$row->text .= '<div class="pagenavcounter">';
		$row->text .= $pageNav->writeLeafsCounter();
		$row->text .= '</div>';

		// page text
		$row->text .= $text[$page];

		$row->text .= '<br />';
		$row->text .= '<div class="pagenavbar">';

		// adds navigation between pages to bottom of text
		if ( $hasToc ) {
			createNavigation( $row, $page, $n );
		}

		// page links shown at bottom of page if TOC disabled
		if (!$hasToc) {
			$row->text .= $pageNav->writePagesLinks( 'index.php?option=com_content&amp;task=view&amp;id='. $row->id .'&amp;Itemid='. $Itemid );
		}

		$row->text .= '</div><br />';
	}

	return true;
}

function createTOC( &$row, &$matches, &$page ) {
	global $Itemid;
	;

	$nonseflink = 'index.php?option=com_content&amp;task=view&amp;id='. $row->id .'&amp;Itemid='. $Itemid;
	$link = 'index.php?option=com_content&amp;task=view&amp;id='. $row->id .'&amp;Itemid='. $Itemid;
	$link = sefRelToAbs( $link );

	$heading = $row->title;
	// allows customization of first page title by checking for `heading` attribute in first bot
	if ( @$matches[0][2] ) {
		parse_str( str_replace( '&amp;', '&', $matches[0][2] ), $args );

		if ( @$args['heading'] ) {
			$heading = $args['heading'];
			$row->title .= ': '. $heading;
		}
	}

	// TOC Header
	$row->toc = '
	<table cellpadding="0" cellspacing="0" class="contenttoc" align="right">
	<tr>
		<th>'
		. JText::_( 'Article Index' ) .
		'</th>
	</tr>
	';

	// TOC First Page link
	$row->toc .= '
	<tr>
		<td>
		<a href="'. $link .'" class="toclink">'
		. $heading .
		'</a>
		</td>
	</tr>
	';

	$i = 2;
	$args2 = array();

	foreach ( $matches as $bot ) {
		$link = $nonseflink .'&amp;limit=1&amp;limitstart='. ($i-1);
		$link = sefRelToAbs( $link );

		if ( @$bot[2] ) {
			parse_str( str_replace( '&amp;', '&', $bot[2] ), $args2 );

			if ( @$args2['title'] ) {
				$row->toc .= '
				<tr>
					<td>
					<a href="'. $link .'" class="toclink">'
					. stripslashes( $args2['title'] ) .
					'</a>
					</td>
				</tr>
				';
			} else {
				$row->toc .= '
				<tr>
					<td>
					<a href="'. $link .'" class="toclink">'
					. sprintf( JText::_( 'Page' ), $i ) .
					'</a>
					</td>
				</tr>
				';
			}
		} else {
			$row->toc .= '
			<tr>
				<td>
				<a href="'. $link .'" class="toclink">'
				. sprintf( JText::_( 'Page' ), $i ) .
				'</a>
				</td>
			</tr>
			';
		}
		$i++;
	}

	$row->toc .= '</table>';
}

function createNavigation( &$row, $page, $n ) {
	global $Itemid;
	;

	$link = 'index.php?option=com_content&amp;task=view&amp;id='. $row->id .'&amp;Itemid='. $Itemid;

	$pnSpace = "";
    if (JText::_( '&lt' ) || JText::_( '&gt' )) $pnSpace = " ";

	if ( $page < $n-1 ) {
		$link_next = $link .'&amp;limit=1&amp;limitstart='. ( $page + 1 );
		$link_next = sefRelToAbs( $link_next );
        // Next >>
		$next = '<a href="'. $link_next .'">' . JText::_( 'Next' ) . $pnSpace . JText::_( '&gt' ) . JText::_( '&gt' ) .'</a>';
	} else {
		$next = JText::_( 'Next' );
	}

	if ( $page > 0 ) {
		$link_prev = $link .'&amp;limit=1&amp;limitstart='. ( $page - 1 );
		$link_prev = sefRelToAbs( $link_prev );
        // << Prev
		$prev = '<a href="'. $link_prev .'">'. JText::_( '&lt' ) . JText::_( '&lt' ) . $pnSpace . JText::_( 'Prev' ) .'</a>';
	} else {
		$prev = JText::_( 'Prev' );
	}

	$row->text .= '<div>' . $prev . ' - ' . $next .'</div>';
}
?>
