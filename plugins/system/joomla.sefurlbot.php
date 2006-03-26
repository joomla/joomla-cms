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

$mainframe->registerEvent( 'onBeforeStart', 'botJoomlaSEFUrl' );

/**
* Converting the site URL to fit to the HTTP request
*
*/
function botJoomlaSEFUrl( ) {
	global $mainframe, $task, $sectionid, $id, $Itemid, $limit, $limitstart, $database, $mod_rewrite_off;

	/*
	 * Initialize some variables
	 */
	$mod_rewrite_off 	= 0;
	$SEF 				= $mainframe->getCfg('sef');

	//Only use SEF is enabled and not in the administrator
	if ($SEF && !$mainframe->isAdmin()) {

		// load plugin params info
	 	$plugin =& JPluginHelper::getPlugin('system', 'joomla.sefurlbot');
	 	$pluginParams = new JParameter( $plugin->params );

		$mod_rewrite_off = $pluginParams->get( 'mode', 0 );

		$url_array = explode('/', $_SERVER['REQUEST_URI']);

		if (in_array('content', $url_array)) {

			/**
			* Content
			* http://www.domain.com/$option/$task/$sectionid/$id/$Itemid/$limit/$limitstart
			*/

			$uri 				= explode('content/', $_SERVER['REQUEST_URI']);
			$option 			= 'com_content';
			$_GET['option'] 	= $option;
			$_REQUEST['option'] = $option;
			$pos 				= array_search ('content', $url_array);

			// language hook for content
			$lang = '';
			foreach($url_array as $key=>$value) {
				if ( !strcasecmp(substr($value,0,5),'lang,') ) {
					$temp = explode(',', $value);
					if (isset($temp[0]) && $temp[0] != '' && isset($temp[1]) && $temp[1] != '') {
						$_GET['lang'] 		= $temp[1];
						$_REQUEST['lang'] 	= $temp[1];
						$lang 				= $temp[1];
					}
					unset($url_array[$key]);
				}
			}

		if (isset($url_array[$pos+8]) && $url_array[$pos+8] != '' && in_array('category', $url_array) && ( strpos( $url_array[$pos+5], 'order,' ) !== false ) && ( strpos( $url_array[$pos+6], 'filter,' ) !== false ) ) {
				// $option/$task/$sectionid/$id/$Itemid/$order/$filter/$limit/$limitstart
				$task 					= $url_array[$pos+1];
				$sectionid				= $url_array[$pos+2];
				$id 					= $url_array[$pos+3];
				$Itemid 				= $url_array[$pos+4];
				$order 					= str_replace( 'order,', '', $url_array[$pos+5] );
				$filter					= str_replace( 'filter,', '', $url_array[$pos+6] );
				$limit 					= $url_array[$pos+7];
				$limitstart 			= $url_array[$pos+8];
				
				// pass data onto global variables
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;
				$_GET['sectionid'] 		= $sectionid;
				$_REQUEST['sectionid'] 	= $sectionid;
				$_GET['id'] 			= $id;
				$_REQUEST['id'] 		= $id;
				$_GET['Itemid'] 		= $Itemid;
				$_REQUEST['Itemid'] 	= $Itemid;
				$_GET['order'] 			= $order;
				$_REQUEST['order'] 		= $order;
				$_GET['filter'] 		= $filter;
				$_REQUEST['filter'] 	= $filter;
				$_GET['limit'] 			= $limit;
				$_REQUEST['limit'] 		= $limit;
				$_GET['limitstart'] 	= $limitstart;
				$_REQUEST['limitstart'] = $limitstart;
				
				$QUERY_STRING = "option=com_content&task=$task&sectionid=$sectionid&id=$id&Itemid=$Itemid&order=$order&filter=$filter&limit=$limit&limitstart=$limitstart";
			} else if (isset($url_array[$pos+7]) && $url_array[$pos+7] != '' && ( in_array('archivecategory', $url_array) || in_array('archivesection', $url_array) ) ) {
				// $option/$task/$sectionid/$Itemid/$limit/$limitstart/year/month
				$task 					= $url_array[$pos+1];
				$sectionid				= $url_array[$pos+2];
				$Itemid 				= $url_array[$pos+3];
				$limit 					= $url_array[$pos+4];
				$limitstart 			= $url_array[$pos+5];
				$year 					= $url_array[$pos+6];
				$month 					= $url_array[$pos+7];
				
				// pass data onto global variables
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;
				$_GET['id'] 			= $sectionid;
				$_REQUEST['id'] 		= $sectionid;
				$_GET['Itemid'] 		= $Itemid;
				$_REQUEST['Itemid'] 	= $Itemid;
				$_GET['limit'] 			= $limit;
				$_REQUEST['limit'] 		= $limit;
				$_GET['limitstart'] 	= $limitstart;
				$_REQUEST['limitstart'] = $limitstart;
				$_GET['year'] 			= $year;
				$_REQUEST['year'] 		= $year;
				$_GET['month'] 			= $month;
				$_REQUEST['month'] 		= $month;
				
				$QUERY_STRING = "option=com_content&task=$task&sectionid=$sectionid&Itemid=$Itemid&limit=$limit&limitstart=$limitstart&year=$year&month=$month";			
			} else if (isset($url_array[$pos+7]) && $url_array[$pos+7] != '' && in_array('category', $url_array) && ( strpos( $url_array[$pos+5], 'order,' ) !== false )) {
				// $option/$task/$sectionid/$id/$Itemid/$order/$limit/$limitstart
				$task 					= $url_array[$pos+1];
				$sectionid				= $url_array[$pos+2];
				$id 					= $url_array[$pos+3];
				$Itemid 				= $url_array[$pos+4];
				$order 					= str_replace( 'order,', '', $url_array[$pos+5] );
				$limit 					= $url_array[$pos+6];
				$limitstart 			= $url_array[$pos+7];
				
				// pass data onto global variables
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;
				$_GET['sectionid'] 		= $sectionid;
				$_REQUEST['sectionid'] 	= $sectionid;
				$_GET['id'] 			= $id;
				$_REQUEST['id'] 		= $id;
				$_GET['Itemid'] 		= $Itemid;
				$_REQUEST['Itemid'] 	= $Itemid;
				$_GET['order'] 			= $order;
				$_REQUEST['order'] 		= $order;
				$_GET['limit'] 			= $limit;
				$_REQUEST['limit'] 		= $limit;
				$_GET['limitstart'] 	= $limitstart;
				$_REQUEST['limitstart'] = $limitstart;
				
				$QUERY_STRING = "option=com_content&task=$task&sectionid=$sectionid&id=$id&Itemid=$Itemid&order=$order&limit=$limit&limitstart=$limitstart";
			} else if (isset($url_array[$pos+6]) && $url_array[$pos+6] != '') {
			// $option/$task/$sectionid/$id/$Itemid/$limit/$limitstart
				$task 					= $url_array[$pos+1];
				$sectionid				= $url_array[$pos+2];
				$id 					= $url_array[$pos+3];
				$Itemid 				= $url_array[$pos+4];
				$limit 					= $url_array[$pos+5];
				$limitstart 			= $url_array[$pos+6];

				// pass data onto global variables
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
			} else if (isset($url_array[$pos+5]) && $url_array[$pos+5] != '') {
			// $option/$task/$id/$Itemid/$limit/$limitstart
				$task 					= $url_array[$pos+1];
				$id 					= $url_array[$pos+2];
				$Itemid 				= $url_array[$pos+3];
				$limit 					= $url_array[$pos+4];
				$limitstart 			= $url_array[$pos+5];

				// pass data onto global variables
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
			} else if (isset($url_array[$pos+4]) && $url_array[$pos+4] != '' && ( in_array('archivecategory', $url_array) || in_array('archivesection', $url_array) )) {
			// $option/$task/$year/$month/$module
				$task 					= $url_array[$pos+1];
				$year 					= $url_array[$pos+2];
				$month 					= $url_array[$pos+3];
				$module 				= $url_array[$pos+4];

				// pass data onto global variables
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;
				$_GET['year'] 			= $year;
				$_REQUEST['year'] 		= $year;
				$_GET['month'] 			= $month;
				$_REQUEST['month'] 		= $month;
				$_GET['module'] 		= $module;
				$_REQUEST['module']		= $module;

				$QUERY_STRING = "option=com_content&task=$task&year=$year&month=$month&module=$module";
			} else if (!(isset($url_array[$pos+5]) && $url_array[$pos+5] != '') && isset($url_array[$pos+4]) && $url_array[$pos+4] != '') {
			// $option/$task/$sectionid/$id/$Itemid
				$task 					= $url_array[$pos+1];
				$sectionid 				= $url_array[$pos+2];
				$id 					= $url_array[$pos+3];
				$Itemid 				= $url_array[$pos+4];

				// pass data onto global variables
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;
				$_GET['sectionid'] 		= $sectionid;
				$_REQUEST['sectionid'] 	= $sectionid;
				$_GET['id'] 			= $id;
				$_REQUEST['id'] 		= $id;
				$_GET['Itemid'] 		= $Itemid;
				$_REQUEST['Itemid'] 	= $Itemid;

				$QUERY_STRING = "option=com_content&task=$task&sectionid=$sectionid&id=$id&Itemid=$Itemid";
			} else if (!(isset($url_array[$pos+4]) && $url_array[$pos+4] != '') && (isset($url_array[$pos+3]) && $url_array[$pos+3] != '')) {
			// $option/$task/$id/$Itemid
				$task 					= $url_array[$pos+1];
				$id 					= $url_array[$pos+2];
				$Itemid 				= $url_array[$pos+3];

				// pass data onto global variables
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;
				$_GET['id'] 			= $id;
				$_REQUEST['id'] 		= $id;
				$_GET['Itemid'] 		= $Itemid;
				$_REQUEST['Itemid'] 	= $Itemid;

				$QUERY_STRING = "option=com_content&task=$task&id=$id&Itemid=$Itemid";
			} else if (!(isset($url_array[$pos+3]) && $url_array[$pos+3] != '') && (isset($url_array[$pos+2]) && $url_array[$pos+2] != '')) {
			// $option/$task/$id
				$task 					= $url_array[$pos+1];
				$id 					= $url_array[$pos+2];

				// pass data onto global variables
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;
				$_GET['id'] 			= $id;
				$_REQUEST['id'] 		= $id;

				$QUERY_STRING = "option=com_content&task=$task&id=$id";
			} else if (!(isset($url_array[$pos+2]) && $url_array[$pos+2] != '') && (isset($url_array[$pos+1]) && $url_array[$pos+1] != '')) {
			// $option/$task
				$task = $url_array[$pos+1];

				// pass data onto global variables
				$_GET['task'] 			= $task;
				$_REQUEST['task'] 		= $task;

				$QUERY_STRING = 'option=com_content&task='. $task;
			}

			if ($lang!='') {
				$QUERY_STRING .= '&lang='. $lang;
			}

			$_SERVER['QUERY_STRING'] 	= $QUERY_STRING;
			$REQUEST_URI 				= $uri[0].'index.php?'.$QUERY_STRING;
			$_SERVER['REQUEST_URI'] 	= $REQUEST_URI;

		} else if (in_array('component', $url_array)) {

			/*
			Components
			http://www.domain.com/component/$name,$value
			*/

			$uri = explode('component/', $_SERVER['REQUEST_URI']);
			$uri_array = explode('/', $uri[1]);
			$QUERY_STRING = '';
			
			// needed for check if component exists
			$path 		= JPATH_BASE .'/components';
			$dirlist 	= array();
			if ( is_dir( $path ) ) {
				$base = opendir( $path );	
				while (false !== ( $dir = readdir($base) ) ) {
					if (is_dir($path .'/'. $dir) && $dir !== '.' && $dir !== '..' && strtolower($dir) !== 'cvs' && strtolower($dir) !== '.svn') {
						$dirlist[] = $dir;
					}
				}
				closedir($base);
			}
			
			foreach($uri_array as $value) {
				$temp = explode(',', $value);
				if (isset($temp[0]) && $temp[0]!='' && isset($temp[1]) && $temp[1]!='') {
					$_GET[$temp[0]] 	= $temp[1];
					$_REQUEST[$temp[0]] = $temp[1];
				
					// check to ensure component actually exists
					if ( $temp[0] == 'option' ) {
						$check = '';
						if (count( $dirlist )) {
							foreach ( $dirlist as $dir ) {
								if ( $temp[1] == $dir ) {
									$check = 1;
									break;
								}
							}
						}
						// redirect to 404 page if no component found to match url
						if ( !$check ) {
							JError::raiseError( 404, JText::_('Resource not found'));
							exit( 404 );
						}
					}
					
					if ( $QUERY_STRING == '' ) {
						$QUERY_STRING .= "$temp[0]=$temp[1]";
					} else {
						$QUERY_STRING .= "&$temp[0]=$temp[1]";
					}
				}
			}

			$_SERVER['QUERY_STRING'] 	= $QUERY_STRING;
			$REQUEST_URI 				= $uri[0].'index.php?'.$QUERY_STRING;
			$_SERVER['REQUEST_URI'] 	= $REQUEST_URI;

			// Extract to globals
			while(list($key,$value)=each($_GET)) {
				if ($key!="GLOBALS") {
					$GLOBALS[$key]=$value;
				}
			}

		} else {

			/*
			Unknown content
			http://www.domain.com/unknown
			*/
			$jdir = str_replace ('index.php', '', $_SERVER['PHP_SELF']);
			$juri = str_replace ($jdir, '', $_SERVER['REQUEST_URI']);

			if ($juri != '' && $juri != '/' && !eregi("index\.php", $_SERVER['REQUEST_URI']) && !eregi("index2\.php", $_SERVER['REQUEST_URI']) && !eregi("/\?", $_SERVER['REQUEST_URI']) && $_SERVER['QUERY_STRING'] == '' ) {
				header( 'HTTP/1.0 404 Not Found' );
				require_once( JPATH_SITE . '/templates/_system/404.php' );
				exit( 404 );
			}
		}

	}
}

/**
 * Function to convert an internal Joomla URL to an absolute Search Engine
 * Friendly URL.
 *
 * @param string $string The internal URL
 * @return string The absolute search engine friendly URL
 * @since 1.0
 */
function sefRelToAbs( $string ) {
	global $mainframe, $iso_client_lang, $mod_rewrite_off;
	
	/*
	 * Initialize some variables
	 */
	$SEF 					= $mainframe->getCfg('sef');
	$MultilingualSupport 	= $mainframe->getCfg('multilingual_support');
	$LiveSite 				= $mainframe->getCfg('live_site');

	//multilingual code url support
	if( isset($MultilingualSupport) && ($MultilingualSupport) && $string!='index.php' && !eregi("^(([^:/?#]+):)",$string) && !strcasecmp(substr($string,0,9),'index.php') && !eregi('lang=', $string) ) {
		$string .= "&lang=$iso_client_lang";
	}

	// SEF URL Handling
	if ( $SEF && !eregi("^(([^:/?#]+):)",$string) && !strcasecmp(substr($string,0,9),'index.php')) {
		// Replace all &amp; with &
		$string = str_replace( '&amp;', '&', $string );
		
		// Home index.php
		if ($string == 'index.php') {
			$string = '';
		}
		
		// break link into url component parts
		$url = parse_url( $string );
		
		// check if link contained fragment identifiers (ex. #foo)
		$fragment = '';
		if ( isset($url['fragment']) ) {
			// ensure fragment identifiers are compatible with HTML4
			if (preg_match('@^[A-Za-z][A-Za-z0-9:_.-]*$@', $url['fragment'])) {
				$fragment = '#'. $url['fragment'];
			}
		}
		
		// check if link contained a query component
		if ( isset($url['query']) ) {
			// special handling for javascript
			$url['query'] = stripslashes( str_replace( '+', '%2b', $url['query'] ) );
			
			// Initialize variables
			$parts = null;

			// break url into component parts			
			parse_str( $url['query'], $parts );
			
			// special handling for javascript
			foreach( $parts as $key => $value) {
				if ( strpos( $value, '+' ) !== false ) {
					$parts[$key] = stripslashes( str_replace( '%2b', '+', $value ) );
				}
			}
			
			$sefstring = '';
			
			// Component com_content urls
			if ( ( $parts['option'] == 'com_content' || $parts['option'] == 'content' ) && ( $parts['task'] != 'new' ) && ( $parts['task'] != 'edit' ) ) {
				// index.php?option=com_content [&task=$task] [&sectionid=$sectionid] [&id=$id] [&Itemid=$Itemid] [&limit=$limit] [&limitstart=$limitstart] [&year=$year] [&month=$month] [&module=$module]
				$sefstring .= 'content/';
				
				// task 
				if ( isset( $parts['task'] ) ) {
					$sefstring .= $parts['task'].'/';					
				}
				// sectionid 
				if ( isset( $parts['sectionid'] ) ) {
					$sefstring .= $parts['sectionid'].'/';					
				}
				// id 
				if ( isset( $parts['id'] ) ) {
					$sefstring .= $parts['id'].'/';					
				}
				// Itemid 
				if ( isset( $parts['Itemid'] ) ) {
					//only add Itemid value if it does not correspond with the 'unassigned' Itemid value
					if ( $parts['Itemid'] != 99999999 ) {
						$sefstring .= $parts['Itemid'].'/';					
					}
				}
				// order
				if ( isset( $parts['order'] ) ) {
					$sefstring .= $parts['order'].'/';	
				}
				// filter
				if ( isset( $parts['filter'] ) ) {
					$sefstring .= 'filter,'. $parts['filter'].'/';	
				}
				// limit
				if ( isset( $parts['limit'] ) ) {
					$sefstring .= $parts['limit'].'/';					
				}
				// limitstart
				if ( isset( $parts['limitstart'] ) ) {
					$sefstring .= $parts['limitstart'].'/';					
				}
				// year
				if ( isset( $parts['year'] ) ) {
					$sefstring .= $parts['year'].'/';					
				}
				// month
				if ( isset( $parts['month'] ) ) {
					$sefstring .= $parts['month'].'/';					
				}
				// module
				if ( isset( $parts['module'] ) ) {
					$sefstring .= $parts['module'].'/';					
				}
				// lang
				if ( isset( $parts['lang'] ) ) {
					$sefstring .= 'lang,'. $parts['lang'].'/';					
				}
				
				$string = $sefstring;
				
				// all other components
			} else if ( ( strpos( $parts['option'], 'com_' ) !== false ) && ( @$parts['task'] != 'new' ) && ( @$parts['task'] != 'edit' ) ) {
				// index.php?option=com_xxxx &...
				$sefstring 	= 'component/';
				
				foreach($parts as $key => $value) {
					$sefstring .= $key .','. $value.'/';
				}
				
				$string = str_replace( '=', ',', $sefstring );
			}
		}

		if ( $mod_rewrite_off ) {
			return $LiveSite . '/index.php/' . $string;
		} else {
			return $LiveSite . '/' . $string;
		}

	} else {
	// Handling for when SEF is not activated
		// Relative link handling
		if ( !(strpos( $string, $LiveSite ) === 0) ) {
			// if URI starts with a "/", means URL is at the root of the host...
			if (strncmp($string, '/', 1) == 0) {
				// splits http(s)://xx.xx/yy/zz..." into [1]="http(s)://xx.xx" and [2]="/yy/zz...":
				$live_site_parts = array();
				eregi("^(https?:[\/]+[^\/]+)(.*$)", $LiveSite, $live_site_parts);
				
				$string = $live_site_parts[1] . $string;
			// check that url does not contain `http`, `https` or `ftp` at start of string
			} else if ( ( strpos( $string, 'http' ) !== 0 ) && ( strpos( $string, 'https' ) !== 0 ) && ( strpos( $string, 'ftp' ) !== 0 ) ) {
				// URI doesn't start with a "/" so relative to the page (live-site):
				$string = $LiveSite .'/'. $string;
			}
		}
		
		return $string;
	}
}
?>