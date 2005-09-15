<?php
/**
* @version $Id: mospaging.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
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
	global $mainframe, $Itemid, $database, $_LANG;

 	// expression to search for
 	$regex = '/{(mospagebreak)\s*(.*?)}/i';

 	if ( !$published || $params->get( 'intro_only' ) )  {
		$row->text = preg_replace( $regex, '', $row->text );
		return;
	}

	// used to replace {mospagebreak} tags when previewing item via popup
 	if ( $params->get( 'popup' ) ) {
 		$replace = '<div class="preview">';
 		$replace .= $_LANG->_( 'Page Break' );
 		$replace .= '<hr/></div><br/>';

 		$row->text = preg_replace( $regex, $replace, $row->text );
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
		$query = "SELECT id FROM #__mambots WHERE element = 'mospaging' AND folder = 'content'";
		$database->setQuery( $query );
	 	$id 	= $database->loadResult();
	 	$mambot = new mosMambot( $database );
	  	$mambot->load( $id );
	 	$params = new mosParameters( $mambot->params );

	 	$title		= $params->def( 'title', 1 );
	 	$separator	= $params->def( 'separator', ' :: ' );

	 	// depreciated GC param shifted to Joomla! param
		//$hasToc 	= $mainframe->getCfg( 'multipage_toc' );
		$hasToc 	= $params->def( 'toc', 0 );

	 	// adds heading or title to <site> Title
	 	if ( $title ) {
			$page_text = $page + 1;
			$row->page_title = $_LANG->_( 'Page' ) .' '. $page_text;
			if ( !$page ) {
				// processing for first page
				parse_str( $matches[0][2], $args );

				if ( @$args['heading'] ) {
					$row->page_title = $args['heading'];
				} else {
					$row->page_title = '';
				}
			} else if ( $matches[$page-1][2] ) {
				parse_str( $matches[$page-1][2], $args );

				if ( @$args['title'] ) {
					$row->page_title = $args['title'];
				}
			}
	 	}

		// reset the text, we already hold it in the $text array
		$row->text = '';

		if ( !$hasToc ) {
			// display TOC
			createTOC( $row, $matches, $page );
		} else {
			$row->toc = '';
		}

		// load navigation files
		mosFS::load( '@pageNavigation' );
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
		if ( !$hasToc ) {
			createNavigation( $row, $page, $n );
		}

		// page links shown at bottom of page if TOC disabled
		if ( $hasToc )  {
			$row->text .= $pageNav->writePagesLinks( 'index.php?option=com_content&amp;task=view&amp;id='. $row->id .'&amp;Itemid='. $Itemid );
		}

		$row->text .= '</div><br />';
	}

	// adds mospagebreak heading or title to <site> Title and page title
	if ( isset( $row->page_title ) ) {
		if ( $row->page_title ) {
			$row->title .= ' '. $separator .' '. $row->page_title;
			$mainframe->SetPageTitle( $row->title );
		}
	}

	return true;
}

function createTOC( &$row, &$matches, &$page ) {
	global $Itemid, $_LANG;

	$nonseflink = 'index.php?option=com_content&amp;task=view&amp;id='. $row->id .'&amp;Itemid='. $Itemid;
	$link = 'index.php?option=com_content&amp;task=view&amp;id='. $row->id .'&amp;Itemid='. $Itemid;
	$link = sefRelToAbs( $link );

	$heading = $row->title;
	// allows customization of first page title by checking for `heading` attribute in first bot
	if ( @$matches[0][2] ) {
		parse_str( $matches[0][2], $args );

		if ( @$args['heading'] ) {
			$heading = $args['heading'];
		}
	}

	// TOC Header
	$row->toc = '
	<table cellpadding="0" cellspacing="0" class="contenttoc" align="right">
	<tr>
		<th>'. $_LANG->_( 'TOC_JUMPTO' ) .'</th>
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
					. $args2['title'] .
					'</a>
					</td>
				</tr>
				';
			} else {
				$row->toc .= '
				<tr>
					<td>
					<a href="'. $link .'" class="toclink">'
					. $_LANG->_( 'Page' ) .' '. $i .
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
				. $_LANG->_( 'Page' ) .' '. $i .
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
	global $Itemid, $_LANG;

	$link = 'index.php?option=com_content&amp;task=view&amp;id='. $row->id .'&amp;Itemid='. $Itemid;

	if ( $page < $n-1 ) {
		$link_next = $link .'&amp;limit=1&amp;limitstart='. ( $page + 1 );
		$link_next = sefRelToAbs( $link_next );

		$next = '<a href="'. $link_next .'">' .$_LANG->_( 'Next' ) . $_LANG->_( 'NEXT_ARROW' ) .'</a>';
	} else {
		$next = $_LANG->_( 'Next' );
	}

	if ( $page > 0 ) {
		$link_prev = $link .'&amp;limit=1&amp;limitstart='. ( $page - 1 );
		$link_prev = sefRelToAbs( $link_prev );

		$prev = '<a href="'. $link_prev .'">'. $_LANG->_( 'PREV_ARROW' ) . $_LANG->_( 'Prev' ) .'</a>';
	} else {
		$prev = $_LANG->_( 'Prev' );
	}

	$row->text .= '<div>' . $prev . ' - ' . $next .'</div>';
}
?>