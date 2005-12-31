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

// clientids must be an integer
$clientids = $params->get( 'banner_cids', '' );

$where 	= '';
$banner = null;

if ( $clientids <> '' ) {
	$where = "\n AND cid IN ( $clientids )";
}

$query = "SELECT COUNT(*) AS numrows"
. "\n FROM #__banner"
. "\n WHERE showBanner = 1"
. $where;
$database->setQuery( $query );
$numrows = $database->loadResult();
if ($numrows === null) {
	echo $database->stderr( true );
	return;
}

$bannum = 0;
if ($numrows > 1) {
    $numrows--;
	mt_srand( (double) microtime()*1000000 );
	$bannum = mt_rand( 0, $numrows );
}

if ($numrows){
    $query = "SELECT *"
    . "\n FROM #__banner WHERE showBanner=1 "
    . $where
    . "\n LIMIT $bannum, 1";
    $database->setQuery( $query );
    if ($database->loadObject( $banner )) {

    	$query = "UPDATE #__banner"
    	. "\n SET impmade = impmade + 1"
    	. "\n WHERE bid = $banner->bid"
    	;
    	$database->setQuery( $query );
    	if(!$database->query()) {
    		echo $database->stderr( true );
    		return;
    	}
    	$banner->impmade++;

    	if ($numrows > 0) {
    		// Check if this impression is the last one and print the banner
    		if ($banner->imptotal == $banner->impmade) {

    			$query = "INSERT INTO #__bannerfinish ( cid, type, name, impressions, clicks, imageurl, datestart, dateend )"
    			. "\n VALUES ( $banner->cid, '$banner->type', '$banner->name', $banner->impmade, $banner->clicks, '$banner->imageurl', '$banner->date', 'now()' )"
    			;
    			$database->setQuery($query);
    			if(!$database->query()) {
    				die($database->stderr(true));
    			}

    			$query = "DELETE FROM #__banner"
    			. "\n WHERE bid = $banner->bid"
    			;
    			$database->setQuery($query);
    			if(!$database->query()) {
    				die($database->stderr(true));
    			}
    		}

    		if (trim( $banner->custombannercode )) {
    			echo $banner->custombannercode;
    		} else if (eregi( "(\.bmp|\.gif|\.jpg|\.jpeg|\.png)$", $banner->imageurl )) {
    			$imageurl 	= JURL_SITE .'/images/banners/'. $banner->imageurl;
    			$link		= sefRelToAbs( 'index.php?option=com_banners&amp;task=click&amp;bid='. $banner->bid );
    			echo '<a href="'. $link .'" target="_blank"><img src="'. $imageurl .'" border="0" alt="'. JText::_( 'Banner' ) .'" /></a>';

    		} else if (eregi("\.swf$", $banner->imageurl)) {
    			$imageurl 	= JURL_SITE . "/images/banners/".$banner->imageurl;
    			echo "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0\" border=\"5\">
    					<param name=\"movie\" value=\"$imageurl\"><embed src=\"$imageurl\" loop=\"false\" pluginspage=\"http://www.macromedia.com/go/get/flashplayer\" type=\"application/x-shockwave-flash\"></embed></object>";
    		}
    	}
    } else {
    	echo "&nbsp;";
    }
} else {
	echo "&nbsp;";
}
?>