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
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onPrepareContent', 'convertPagebreak' );

/**
* Page break plugin
*
* <b>Usage:</b>
* <code>{pagebreak}</code>
* <code>{pagebreak title=The page title}</code>
* or
* <code>{pagebreak heading=The first page}</code>
* or
* <code>{pagebreak title=The page title&heading=The first page}</code>
* or
* <code>{pagebreak heading=The first page&title=The page title}</code>
*
*/
function convertPagebreak( &$row, &$params, $page=0 ) 
{
	global $mainframe, $Itemid, $database;

	// simple performance check to determine whether bot should process further
	if ( strpos( $row->text, '{pagebreak' ) === false ) {
		return true;
	}
	
	if(!$page) {
		$page = 0;
	}
 	// expression to search for
 	$regex = '/{pagebreak\s*(.*?)}/i';

	// Get Plugin info
 	$plugin =& JPluginHelper::getPlugin('content', 'pagebreak'); 

	// check whether plugin has been unpublished
 	if (!$plugin->published || $params->get( 'intro_only' )|| $params->get( 'popup' )) {
		$row->text = preg_replace( $regex, '', $row->text );
		return;
	}	
	
	// find all instances of plugin and put in $matches
	$matches = array();
	preg_match_all( $regex, $row->text, $matches, PREG_SET_ORDER );

	// split the text around the plugin
	$text = preg_split( $regex, $row->text );

	// count the number of pages
	$n = count( $text );

	// we have found at least one plugin, therefore at least 2 pages
	if ($n > 1) {

		// Get plugin parameters
	 	$pluginParams = new JParameter( $plugin->params );
	 	$title	= $pluginParams->get( 'title', 1 );
		$hasToc = $pluginParams->get( 'multipage_toc', 1 );

	 	// adds heading or title to <site> Title
	 	if ( $title ) {
			$page_text = $page + 1;
			$row->page_title = sprintf( JText::_( 'Page' ), $page_text );
			if ( !$page ) {
				// processing for first page
				$attrs = josParseAttributes($matches[0][1]);

				if ( @$attrs['heading'] ) {
					$row->page_title = $attrs['heading'];
				} else {
					$row->page_title = '';
				}
			} else if ( $matches[$page-1][2] ) {
				$attrs = josParseAttributes($matches[$page-1][1]);

				if ( @$attrs['title'] ) {
					$row->page_title = $attrs['title'];
				}
			}
	 	}

		// reset the text, we already hold it in the $text array
		$row->text = '';

		if ( $hasToc ) {
			// display TOC
			createTOC( $row, $matches, $page );
		} else {
			$row->toc = '';
		}

		// traditional mos page navigation
		jimport('joomla.presentation.pagination');
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

function createTOC( &$row, &$matches, &$page ) 
{
	global $Itemid;

	$nonseflink = 'index.php?option=com_content&amp;task=view&amp;id='. $row->id .'&amp;Itemid='. $Itemid;
	$link = 'index.php?option=com_content&amp;task=view&amp;id='. $row->id .'&amp;Itemid='. $Itemid;
	$link = sefRelToAbs( $link );

	$heading = $row->title;
	// allows customization of first page title by checking for `heading` attribute in first bot
	if ( @$matches[0][1] ) {
		$attrs = josParseAttributes($matches[0][1]);
		if ( @$attrs['heading'] ) {
			$heading = $attrs['heading'];
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

	foreach ( $matches as $bot ) {
		$link = $nonseflink .'&amp;limit=1&amp;limitstart='. ($i-1);
		$link = sefRelToAbs( $link );

		if ( @$bot[1] ) {
			$attrs2 = josParseAttributes($bot[1]);

			if ( @$attrs2['title'] ) {
				$row->toc .= '
				<tr>
					<td>
					<a href="'. $link .'" class="toclink">'
					. stripslashes( $attrs2['title'] ) .
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

function createNavigation( &$row, $page, $n ) 
{
	global $Itemid;

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