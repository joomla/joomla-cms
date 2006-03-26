<?php
/**
* @version $Id: frontpage.rssbot.php,v 1.0 2005/09/05 00:00:00 stingrey Exp $
* @package Joomla!
* @copyright (C) 2005 Open Source Matters Inc.
* @copyright (C) 2005 Rey Gigataras
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Joomla! is Free Software
*/

/** ensure this file is being included by a parent file */
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

$_MAMBOTS->registerFunction( 'onSyndicate', 'botSyndicateFrontpage' );

/**
* Content RSS method
 * The sql must return the following fields that are used in a common display
 * routine: title, link, description, date
* @param string URL info
*/
function botSyndicateFrontpage( $url, $limit ) {
	global $database, $mainframe;
	global $mosConfig_offset;

	// Initialize variables
	$parts = null;

	// pull data from referral url and place into array
    parse_str( $url, $parts );

    // check if this is the correct bot to be loaded for component
    if ( $parts['type'] == 'com_frontpage' ) {   
        $menu = new mosMenu( $database );
        $menu->load( $parts['Itemid'] );
    	$params = new JParameter( $menu->params );

    	$orderby_pri   = $params->get( 'orderby_pri', '' );
    	$orderby_sec   = $params->get( 'orderby_sec', '' );
              
    	// Ordering control
    	$order_pri = _orderby_pri( $orderby_pri );  	
    	$order_sec = _orderby_sec( $orderby_sec );
    	$orderby   = "\n ORDER BY $order_pri $order_sec";

	   $now        = date( 'Y-m-d H:i:s', time() + $mosConfig_offset * 60 * 60 );
	   $link       = $mainframe->getBaseURL() .'index.php?option=com_content&task=view&id=';
	   $nullDate   = $database->getNullDate();
	   
	   /*
	   * All SyndicateBots must return
	   * title
	   * link
	   * description
	   * date
	   * category
	   */
    	$query = "SELECT"
    	. "\n a.title AS title,"	
    	. "\n CONCAT( '$link', a.id ) AS link,"
    	. "\n a.introtext AS description,"
    	. "\n UNIX_TIMESTAMP( a.created ) AS date,"
		. "\n CONCAT( s.title, ' - ', cc.title ) AS category,"
    	. "\n a.id AS id"
    	. "\n FROM #__content AS a"
        . "\n INNER JOIN #__content_frontpage AS f ON f.content_id = a.id"
        . "\n LEFT JOIN #__categories AS cc ON cc.id = a.catid"
        . "\n LEFT JOIN #__sections AS s ON s.id = a.sectionid"
        . "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
        . "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
        . "\n LEFT JOIN #__groups AS g ON a.access = g.id"
        . "\n WHERE a.state = '1'"
        . "\n AND ( publish_up = '$nullDate' OR publish_up <= '$now'  )"
        . "\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )"
	    . $orderby
    	;
		$database->setQuery( $query, 0, $limit );
    	$rows = $database->loadObjectList();
    	
    	$count = count( $rows );
    	for ( $i=0; $i < $count; $i++ ) {
    	    $Itemid = $mainframe->getItemid( $rows[$i]->id );
    	    $rows[$i]->link = $rows[$i]->link .'&Itemid='. $Itemid;   
    	}
    	
    	return $rows;
    }
}

function front_orderby_pri( $orderby ) {
	switch ( $orderby ) {
		case 'alpha':
			$orderby = 'cc.title, ';
			break;
			
		case 'ralpha':
			$orderby = 'cc.title DESC, ';
			break;
			
		case 'order':
			$orderby = 'cc.ordering, ';
			break;
			
		default:
			$orderby = '';
			break;
	}

	return $orderby;
}


function front_orderby_sec( $orderby ) {
	switch ( $orderby ) {
		case 'date':
			$orderby = 'a.created';
			break;
			
		case 'rdate':
			$orderby = 'a.created DESC';
			break;
			
		case 'alpha':
			$orderby = 'a.title';
			break;
			
		case 'ralpha':
			$orderby = 'a.title DESC';
			break;
			
		case 'hits':
			$orderby = 'a.hits DESC';
			break;
			
		case 'rhits':
			$orderby = 'a.hits ASC';
			break;
			
		case 'order':
			$orderby = 'a.ordering';
			break;
			
		case 'author':
			$orderby = 'a.created_by, u.name';
			break;
			
		case 'rauthor':
			$orderby = 'a.created_by DESC, u.name DESC';
			break;
			
		case 'front':
			$orderby = 'f.ordering';
			break;
			
		default:
			$orderby = 'a.ordering';
			break;
	}

	return $orderby;
}
?>