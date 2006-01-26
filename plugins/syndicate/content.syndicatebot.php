<?php
/**
* @version $Id: content.rssbot.php,v 1.0 2005/09/05 00:00:00 stingrey Exp $
* @package Joomla!
* @copyright (C) 2005 Open Source Matters Inc.
* @copyright (C) 2005 Rey Gigataras
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Joomla! is Free Software
*/

/** ensure this file is being included by a parent file */
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

$_MAMBOTS->registerFunction( 'onSyndicate', 'botSyndicateContent' );

/**
* Content RSS method
 * The sql must return the following fields that are used in a common display
 * routine: title, link, description, date
* @param string URL info
*/
function botSyndicateContent( $url, $limit ) {
	global $database, $mainframe;

	// pull data from referral url and place into array
    parse_str( $url, $parts );

    // check if this is the correct bot to be loaded for component
    if ( $parts['type'] == 'com_content' ) {    
        $join       = '';
        $where      = "\n WHERE state = 1";
        $id         = $parts['id'];
        
		// menu parameters
        $menu = new mosMenu( $database );
        $menu->load( $parts['Itemid'] );
    	$MenuParams 	= new JParameters( $menu->params );

    	$section       	= $MenuParams->get( 'sectionid', 0 );
    	$category      	= $MenuParams->get( 'categoryid', 0 );
    	$orderby_pri   	= $MenuParams->get( 'orderby_pri', '' );
    	$orderby_sec   	= $MenuParams->get( 'orderby_sec', 'rdate' );
        
		$plugin 		= JPluginHelper::getPlugin('syndicate', 'content.syndicatebot'); 
		$pluginParams 	= new JParameters( $plugin->params );

    	$yItem       	= $pluginParams->get( 'itemContent', 	0 );
    	$yBlogSection   = $pluginParams->get( 'blogSection', 	1 );
    	$yBlogCategory  = $pluginParams->get( 'blogCategory', 	1 );
    	$yListSection   = $pluginParams->get( 'listSection', 	1 );
    	$yTableCategory = $pluginParams->get( 'tableCategory', 	1 );
		
       switch ( $parts['task'] ) {
            case 'blogsection':                
		 		// check whether blog section syndication is activated
				if(!$yBlogSection) {
					return;
				}
	           	if ( $id ) {
                // when a specific section is listed
                    $where  .= "\n AND a.sectionid = $id";
                } else {
                // when more than one section is selected                	
                	if ( $section ) {
                        $where  .= "\n AND a.sectionid IN ( $section )";
                	}
                }
                break;
                
            case 'blogcategory':          	
				// check whether blog category syndication is activated
				if(!$yBlogCategory) {
					return;
				}
                if ( $id ) {
                // when a specific category is listed
                    $where  .= "\n AND a.catid = $id";
                } else {
                // when more than one category is selected                	
                	if ( $section ) {
                        $where  .= "\n AND a.catid IN ( $category )";
                	}
                }
                break;
                
            case 'section':
		 		// check whether list section syndication is activated
				if(!$yListSection) {
					return;
				}
                $where  .= "\n AND a.sectionid = $id";
                break;
                
            case 'category':
				// check whether table category syndication is activated
				if(!$yTableCategory) {
					return;
				}
                $where  .= "\n AND a.catid = $id";
                break;
                
            case 'view':
				// check whether item syndication is activated
				if(!$yItem) {
					return;
				}
                $where  .= "\n AND a.id = $id";
                break;            
        }
        
    	// Ordering control
    	$order_pri 	= _orderby_pri( $orderby_pri );  	
    	$order_sec 	= _orderby_sec( $orderby_sec );
    	$orderby   	= "\n ORDER BY $order_pri $order_sec";

		$link 		= $mainframe->getBaseURL() .'index.php?option=com_content&task=view&id='; 
		
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
   		. "\n INNER JOIN #__categories AS cc ON cc.id = a.catid"
		. "\n INNER JOIN #__sections AS s ON s.id = a.sectionid"
    	. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
    	. $where
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

function _orderby_pri( $orderby ) {
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

function _orderby_sec( $orderby ) {
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
			
		default:
			$orderby = 'a.ordering';
			break;
	}

	return $orderby;
}
?>