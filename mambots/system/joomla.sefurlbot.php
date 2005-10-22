<?php
/**
* @version $Id: jossiteurl.php 85 2005-09-15 23:12:03Z akede $
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

$_MAMBOTS->registerFunction( 'onBeforeStart', 'botJoomlaSEFUrl' );

/**
* Converting the site URL to fit to the HTTP request
*
*/
function botJoomlaSEFUrl( ) {
	global $task, $sectionid, $id, $Itemid, $limit, $limitstart;

	if ($GLOBALS['mosConfig_sef']) {
		$url_array = explode("/", $_SERVER['REQUEST_URI']);
		/**
		* Content
		* http://www.domain.com/$option/$task/$sectionid/$id/$Itemid/$limit/$limitstart
		*/
		if (in_array("content", $url_array)) {

			$uri 				= explode("content/", $_SERVER['REQUEST_URI']);
			$option 			= "com_content";
			$_GET['option'] 	= $option;
			$_REQUEST['option'] = $option;
			$pos = array_search ("content", $url_array);

			// language hook for content
			$lang = "";
			foreach($url_array as $key=>$value) {
				if ( !strcasecmp(substr($value,0,5),"lang,") ) {
					$temp = explode(",", $value);
					if (isset($temp[0]) && $temp[0]!="" && isset($temp[1]) && $temp[1]!="") {
						$_GET['lang'] 		= $temp[1];
						$_REQUEST['lang'] 	= $temp[1];
						$lang 				= $temp[1];
					}
					unset($url_array[$key]);
				}
			}

			// $option/$task/$sectionid/$id/$Itemid/$limit/$limitstart
			if (isset($url_array[$pos+6]) && $url_array[$pos+6]!="") {
				$task 					= $url_array[$pos+1];
				$sectionid				= $url_array[$pos+2];
				$id 					= $url_array[$pos+3];
				$Itemid 				= $url_array[$pos+4];
				$limit 					= $url_array[$pos+5];
				$limitstart 			= $url_array[$pos+6];
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;
				$_GET['sectionid'] 		= $sectionid;
				$_REQUEST['sectionid'] 	= $sectionid;
				$_GET['id'] 			= $id;
				$_REQUEST['id'] 		= $id;
				$_GET['Itemid'] 		= $Itemid;
				$_REQUEST['Itemid'] 	= $Itemid;
				$_GET['limit'] 			= $limit;
				$_REQUEST['limit'] 		= $limit;
				$_GET['limitstart'] 	= $limitstart;
				$_REQUEST['limitstart'] = $limitstart;

				$QUERY_STRING = "option=com_content&task=$task&sectionid=$sectionid&id=$id&Itemid=$Itemid&limit=$limit&limitstart=$limitstart";
				// $option/$task/$id/$Itemid/$limit/$limitstart
			} else if (isset($url_array[$pos+5]) && $url_array[$pos+5]!="") {
				$task 					= $url_array[$pos+1];
				$id 					= $url_array[$pos+2];
				$Itemid 				= $url_array[$pos+3];
				$limit 					= $url_array[$pos+4];
				$limitstart 			= $url_array[$pos+5];
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;
				$_GET['id'] 			= $id;
				$_REQUEST['id'] 		= $id;
				$_GET['Itemid'] 		= $Itemid;
				$_REQUEST['Itemid'] 	= $Itemid;
				$_GET['limit'] 			= $limit;
				$_REQUEST['limit'] 		= $limit;
				$_GET['limitstart'] 	= $limitstart;
				$_REQUEST['limitstart'] = $limitstart;

				$QUERY_STRING = "option=com_content&task=$task&id=$id&Itemid=$Itemid&limit=$limit&limitstart=$limitstart";
				// $option/$task/$sectionid/$id/$Itemid
			} else if (!(isset($url_array[$pos+5]) && $url_array[$pos+5]!="") && isset($url_array[$pos+4]) && $url_array[$pos+4]!="") {
				$task 					= $url_array[$pos+1];
				$sectionid 				= $url_array[$pos+2];
				$id 					= $url_array[$pos+3];
				$Itemid 				= $url_array[$pos+4];
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;
				$_GET['sectionid'] 		= $sectionid;
				$_REQUEST['sectionid'] 	= $sectionid;
				$_GET['id'] 			= $id;
				$_REQUEST['id'] 		= $id;
				$_GET['Itemid'] 		= $Itemid;
				$_REQUEST['Itemid'] 	= $Itemid;

				$QUERY_STRING = "option=com_content&task=$task&sectionid=$sectionid&id=$id&Itemid=$Itemid";
				// $option/$task/$id/$Itemid
			} else if (!(isset($url_array[$pos+4]) && $url_array[$pos+4]!="") && (isset($url_array[$pos+3]) && $url_array[$pos+3]!="")) {
				$task 					= $url_array[$pos+1];
				$id 					= $url_array[$pos+2];
				$Itemid 				= $url_array[$pos+3];
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;
				$_GET['id'] 			= $id;
				$_REQUEST['id'] 		= $id;
				$_GET['Itemid'] 		= $Itemid;
				$_REQUEST['Itemid'] 	= $Itemid;

				$QUERY_STRING = "option=com_content&task=$task&id=$id&Itemid=$Itemid";
				// $option/$task/$id
			} else if (!(isset($url_array[$pos+3]) && $url_array[$pos+3]!="") && (isset($url_array[$pos+2]) && $url_array[$pos+2]!="")) {
				$task 					= $url_array[$pos+1];
				$id 					= $url_array[$pos+2];
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;
				$_GET['id'] 			= $id;
				$_REQUEST['id'] 		= $id;

				$QUERY_STRING = "option=com_content&task=$task&id=$id";
				// $option/$task
			} else if (!(isset($url_array[$pos+2]) && $url_array[$pos+2]!="") && (isset($url_array[$pos+1]) && $url_array[$pos+1]!="")) {
				$task = $url_array[$pos+1];
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;

				$QUERY_STRING = "option=com_content&task=$task";
			}

			if ($lang!="") {
				$QUERY_STRING .= "&lang=$lang";
			}

			$_SERVER['QUERY_STRING'] = $QUERY_STRING;
			$REQUEST_URI = $uri[0]."index.php?".$QUERY_STRING;
			$_SERVER['REQUEST_URI'] = $REQUEST_URI;
		}

		/*
		Components
		http://www.domain.com/component/$name,$value
		*/
		if (in_array("component", $url_array)) {

			$uri = explode("component/", $_SERVER['REQUEST_URI']);
			$uri_array = explode("/", $uri[1]);
			$QUERY_STRING = "";

			foreach($uri_array as $value) {
				$temp = explode(",", $value);
				if (isset($temp[0]) && $temp[0]!="" && isset($temp[1]) && $temp[1]!="") {
					$_GET[$temp[0]] 	= $temp[1];
					$_REQUEST[$temp[0]] = $temp[1];
					$QUERY_STRING .= $temp[0]=="option" ? "$temp[0]=$temp[1]" : "&$temp[0]=$temp[1]";
				}
			}

			$_SERVER['QUERY_STRING'] 	= $QUERY_STRING;
			$REQUEST_URI 				= $uri[0]."index.php?".$QUERY_STRING;
			$_SERVER['REQUEST_URI'] 	= $REQUEST_URI;
		}
		// Extract to globals
		while(list($key,$value)=each($_GET)) $GLOBALS[$key]=$value;
		// Don't allow config vars to be passed as global
		include( $GLOBALS['mosConfig_absolute_path'] . "/configuration.php" );
	}

}

/**
 * 3 states for SSL
 *
 * -1 = Off, use non-SSL URL
 *  0 = Ignore, and use whatever site is using
 *  1 = On, use SSL URL
 */
function sefRelToAbs( $string ) {
	global $iso_client_lang;

	if( $GLOBALS['mosConfig_mbf_content'] && $string!="index.php" &&
		!eregi("^(([^:/?#]+):)",$string) && !strcasecmp(substr($string,0,9),"index.php") &&
		!eregi("lang=", $string) ) {
		$string .= "&lang=$iso_client_lang";
	}

	if ($GLOBALS['mosConfig_sef'] && !eregi("^(([^:/?#]+):)",$string) && !strcasecmp(substr($string,0,9),"index.php")) {

		// Replace all &amp; with &
		$string = str_replace( '&amp;', '&', $string );

		/*
		Home
		index.php
		*/
		if ($string=="index.php") {
			$string="";
		}

		$sefstring = "";
		if ( (eregi("option=com_content",$string) || eregi("option=content",$string) ) && !eregi("task=new",$string) && !eregi("task=edit",$string) ) {
			/*
			Content
			index.php?option=com_content&task=$task&sectionid=$sectionid&id=$id&Itemid=$Itemid&limit=$limit&limitstart=$limitstart
			*/
			$sefstring .= "content/";
			if (eregi("&task=",$string)) {
				$temp = split("&task=", $string);
				$temp = split("&", $temp[1]);

				$sefstring .= $temp[0]."/";
			}
			if (eregi("&sectionid=",$string)) {
				$temp = split("&sectionid=", $string);
				$temp = split("&", $temp[1]);

				$sefstring .= $temp[0]."/";
			}
			if (eregi("&id=",$string)) {
				$temp = split("&id=", $string);
				$temp = split("&", $temp[1]);

				$sefstring .= $temp[0]."/";
			}
			if (eregi("&Itemid=",$string)) {
				$temp = split("&Itemid=", $string);
				$temp = split("&", $temp[1]);

				$sefstring .= $temp[0]."/";
			}
			if (eregi("&limit=",$string)) {
				$temp = split("&limit=", $string);
				$temp = split("&", $temp[1]);

				$sefstring .= $temp[0]."/";
			}
			if (eregi("&limitstart=",$string)) {
				$temp = split("&limitstart=", $string);
				$temp = split("&", $temp[1]);

				$sefstring .= $temp[0]."/";
			}
			if (eregi("&lang=",$string)) {
				$temp = split("&lang=", $string);
				$temp = split("&", $temp[1]);

				$sefstring .= "lang,".$temp[0]."/";
			}
			$string = $sefstring;
		} else if (eregi("option=com_",$string) && !eregi("task=new",$string) && !eregi("task=edit",$string)) {
			/*
			Components
			index.php?option=com_xxxx&...
			*/
			$sefstring 	= "component/";
			$temp 		= split("\?", $string);
			$temp 		= split("&", $temp[1]);

			foreach($temp as $key => $value) {
				$sefstring .= $value."/";
			}
			$string = str_replace( '=', ',', $sefstring );
		}

	}

	return $GLOBALS['mosConfig_live_site'] . "/" . $string;
}

?>