<?php
/**
* @version $Id: weblinks.rssbot.php,v 1.0 2005/09/05 00:00:00 stingrey Exp $
* @package Joomla!
* @copyright (C) 2005 Open Source Matters Inc.
* @copyright (C) 2005 Rey Gigataras
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Joomla! is Free Software
*/

/** ensure this file is being included by a parent file */
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

$mainframe->registerEvent( 'onSyndicate', 'botSyndicateWeblinks' );

/**
* Content RSS method
 * The sql must return the following fields that are used in a common display
 * routine: title, link, description, date
* @param string URL info
*/
function botSyndicateWeblinks( $url, $limit ) {
	global $database;

	// Initialize variables
	$parts = null;
	// pull data from referral url and place into array
    parse_str( $url, $parts );

    // check if this is the correct bot to be loaded for component
    if ( $parts['type'] == 'com_weblinks' ) {    
        $where  = "\n WHERE published = 1";
        $catid  = @$parts['catid'];
        
        if ( $catid ) {
            $where .= "\n AND catid = $catid";
        }
       
		/*
		* All SyndicateBots must return
		* title
		* link
		* description
		* date
		* category
		*/
    	$query = "SELECT"
    	. "\n title AS title,"	
    	. "\n url AS link,"
    	. "\n description AS description,"
    	. "\n '' AS date,"
		. "\n '' AS category"
    	. "\n FROM #__weblinks"
    	. $where
    	. "\n ORDER BY ordering"
     	;
		$database->setQuery( $query, 0, $limit );
    	$rows = $database->loadObjectList();

    	return $rows;
    }
}
?>