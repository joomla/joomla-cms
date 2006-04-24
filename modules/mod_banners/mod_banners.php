<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$clientids	= $params->get( 'banner_cids' );
$limit		= intval($params->get( 'count', 1 ));
$randomise	= intval($params->get( 'randomise' ));
$cssSuffix	= $params->get( 'moduleclass_sfx' );

$mod = JRequest::getVar( 'module' );
$task = JRequest::getVar( 'task' );
$bid = JRequest::getVar( 'bid', 0, '', 'int' );

if( $mod == 'mod_banners' && $task == 'click' && $bid != 0 ) {

	// update click count
	$query = "UPDATE #__banner"
	. "\n SET clicks = ( clicks + 1 )"
	. "\n WHERE bid = $bid"
	;

	$database->setQuery( $query );
	if(!$database->query()) {
		JError::raiseError( 500, $db->stderror());
	}


	// redirect to banner url
	$query = "SELECT clickurl FROM #__banner"
	. "\n WHERE bid = $bid"
	;

	$database->setQuery( $query );
	if(!$database->query()) {
		JError::raiseError( 500, $db->stderror());
	}

	$database->loadObject($row);

	if (substr( $row->clickurl, 0, 7 ) != 'http://' &&  substr( $row->clickurl, 0, 8 ) != 'https://' ) {
		$row->clickurl = "http://$row->clickurl";
	}
	josRedirect( $row->clickurl );


}

$query = "SELECT *"
	. ($randomise ? ', RAND() AS ordering' : ', 1 AS ordering')
	. "\n FROM #__banner"
	. "\n WHERE showBanner = 1 "
	. ($clientids ? ' AND cid IN ( ' . $clientids . ' ) ' : '')
	. "\nORDER BY ordering "
	. "\nLIMIT " . $limit;

$database->setQuery( $query );
if(!$database->query()) {
	JError::raiseError( 500, $db->stderror());
}

$banners = $database->loadObjectList();
$numrows = count($banners);

echo '<div class="bannergroup' . $cssSuffix . '">';

for ($i = 0; $i < $numrows; $i++) {
	$item = &$banners[$i];

	$query = "UPDATE #__banner"
	. "\n SET impmade = impmade + 1"
	. "\n WHERE bid = $item->bid"
	;
	$database->setQuery( $query );

	if(!$database->query()) {
		JError::raiseError( 500, $db->stderror());
	}
	$item->impmade++;

	// expire the banner
	if ($item->imptotal >= $item->impmade) {

		$query = "INSERT INTO #__bannerfinish ( cid, type, name, impressions, clicks, imageurl, datestart, dateend )"
		. "\n VALUES ( $item->cid, '$item->type', '$item->name', $item->impmade, $item->clicks, '$item->imageurl', '$item->date', 'now()' )"
		;
		$database->setQuery($query);

		if(!$database->query()) {
			JError::raiseError( 500, $db->stderror());
		}

		$query = "DELETE FROM #__banner"
		. "\n WHERE bid = $item->bid"
		;
		$database->setQuery($query);
		if(!$database->query()) {
			JError::raiseError( 500, $db->stderror());
		}
	}

	echo '<div class="banneritem' . $cssSuffix . '">';
	if(trim($item->custombannercode)) {
		echo $item->custombannercode;
	} else if(eregi("(\.bmp|\.gif|\.jpg|\.jpeg|\.png)$", $item->imageurl)) {
		$imageurl 	= 'images/banners/'.$item->imageurl;
		$link		= sefRelToAbs( 'index.php?module=mod_banners&amp;task=click&amp;bid='. $item->bid );
		echo '<a href="'.$link.'" target="_blank"><img src="'.$imageurl.'" border="0" alt="'.JText::_('Banner').'" /></a>';
	} else if(eregi("\.swf$", $item->imageurl)) {
		$imageurl = "images/banners/".$item->imageurl;
		echo "	<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0\" border=\"5\">
					<param name=\"movie\" value=\"$imageurl\"><embed src=\"$imageurl\" loop=\"false\" pluginspage=\"http://www.macromedia.com/go/get/flashplayer\" type=\"application/x-shockwave-flash\"></embed>
				</object>";
	}

	echo '	<div class="clr"></div>'
	 	 .  '</div>';
}
echo '</div>';
?>