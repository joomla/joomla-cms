<?php

/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');
jimport( 'joomla.plugin.plugin' );

class plgSystemBacklink extends JPlugin
{

	var $_db = null;

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgSystemBacklink(& $subject, $config)
	{
		$this->_db = JFactory::getDBO();
		parent :: __construct($subject, $config);
	}

	function onAfterInitialise()
	{
		global $mainframe;
		if ($mainframe->isAdmin()) {
			return; // Dont run in admin
		}

		$sef = $this->params->get('sef', 1);
		$url = $this->params->get('url', 1);

		$legacysef = $this->params->get('legacysef', 1);
		if (!$sef && !$url && !$legacysef)
			return; // None of the options enabled, bail!

		// Grab the system as early as possible, we're going to terminate it potentially
		// Case 1: Query string match (shouldn't need this but its here anyway)
		if ($url && isset ($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], '&')) {
			$query_string = $_SERVER['QUERY_STRING'];
			$this->_lookup($query_string);
		}

		// Case 2: SEF or similar match
		if ($sef && isset ($_SERVER['SCRIPT_NAME']) && isset ($_SERVER['REQUEST_URI'])) {
			$part = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
			if($part != '/') {
				$search = str_replace($part, '', $_SERVER['REQUEST_URI']);
			} else {
				$search = ltrim($_SERVER['REQUEST_URI'],'/');
			}
			$this->_lookup($search);
		}
		// Case 3: Old school core sef; used to backlink
		// Enable only if:
		// 1: SEF is enabled
		// 2: Legacy SEF Plugin Param is set
		// 3: And there is no backlink
		if ($mainframe->getCfg('sef')
			&& $legacysef
			&& !strstr($_SERVER['REQUEST_URI'],'nobacklink')
			&& !strlen($_SERVER['QUERY_STRING'])) {
			$this->_legacysef();
		}

	}

	function _lookup($searchstring)
	{
		// return blank strings and just index.php on its own...
		if (!strlen($searchstring) || $searchstring == trim('index.php',' ?')) {
			return;
		}

		$sef = $this->params->get('sef', 1);
		$url = $this->params->get('url', 1);

		if (!$sef && !$url) {
			return; // Neither option enabled, bail!
		}

		$query	= 'SELECT * FROM #__migration_backlinks WHERE ';
		$where	= Array ();
		$search	= $db->Quote( $db->getEscaped( $searchstring, true ).'%', false );

		if ($url) {
			$where[] = 'url LIKE ' . $search;
		}
		if ($sef) {
			$where[] = 'sefurl LIKE ' . $search;
		}

		$query .= implode(' OR ', $where);
		$this->_db->setQuery($query);
		$results = $this->_db->loadAssocList();

		if (count($results)) {
			// Get the first one...
			$this->_redirect($results[0]['itemid'], $results[0]['name'], $results[0]['newurl']);
		}
	}

	function _redirect($Itemid, $name, $url = null)
	{
		global $mainframe;
		if (!strlen($url))
		{
			$menu = & JSite :: getMenu();
			$item = $menu->getItem($Itemid);
			//$url = $item->link;

			switch ($item->type)
			{
				case 'url' :
					if ((strpos($item->link, 'index.php?') !== false) && (strpos($item->link, 'Itemid=') === false)) {
						$url = $item->link . '&amp;Itemid=' . $item->id;
					} else {
						$url = $item->link;
					}
					break;

				default :
					$url = 'index.php?Itemid=' . $item->id;
					//$url = $item->link . '&Itemid='.$item->id;
					break;
			}
			$url = JRoute :: _($url);
			//$url = JURI :: base() . $url; // was $surl with third option of below being url and second being surl
			$name = $item->name;
		}
		// Check we're not redirecting to ourselves
		if(!stristr($url,$_SERVER['REQUEST_URI']) && !stristr($url,$_SERVER['SCRIPT_NAME'].'/'.$_SERVER['QUERY_STRING'])) {
			return;
		}

		$name = $name ? $name : "Unknown";

		header('Location: ' . str_replace('&amp;','&',$url)); // redirect and kill of and &amp;
		die(JText :: sprintf('"%s" has moved to <a href="%s">%s</a>. Click the link if your browser does not redirect you automatically.', $name, $url, $url));
	}

	function _legacysef()
	{
		$mosConfig_absolute_path = JPATH_SITE;
		$mosConfig_live_site = JURI :: base();
		$url_array = explode('/', $_SERVER['REQUEST_URI']);

		if (in_array('content', $url_array))
		{
			/**
			* Content
			* http://www.domain.com/$option/$task/$sectionid/$id/$Itemid/$limit/$limitstart
			*/

			$uri = explode('content/', $_SERVER['REQUEST_URI']);
			$option = 'com_content';
			$_GET['option'] = $option;
			$_REQUEST['option'] = $option;
			$pos = array_search('content', $url_array);

			// language hook for content
			$lang = '';
			foreach ($url_array as $key => $value)
			{
				if (!strcasecmp(substr($value, 0, 5), 'lang,'))
				{
					$temp = explode(',', $value);
					if (isset ($temp[0]) && $temp[0] != '' && isset ($temp[1]) && $temp[1] != '')
					{
						$_GET['lang'] = $temp[1];
						$_REQUEST['lang'] = $temp[1];
						$lang = $temp[1];
					}
					unset ($url_array[$key]);
				}
			}

			if (isset ($url_array[$pos +8]) && $url_array[$pos +8] != '' && in_array('category', $url_array) && (strpos($url_array[$pos +5], 'order,') !== false) && (strpos($url_array[$pos +6], 'filter,') !== false))
			{
				// $option/$task/$sectionid/$id/$Itemid/$order/$filter/$limit/$limitstart
				$task = $url_array[$pos +1];
				$sectionid = $url_array[$pos +2];
				$id = $url_array[$pos +3];
				$Itemid = $url_array[$pos +4];
				$order = str_replace('order,', '', $url_array[$pos +5]);
				$filter = str_replace('filter,', '', $url_array[$pos +6]);
				$limit = $url_array[$pos +7];
				$limitstart = $url_array[$pos +8];

				// pass data onto global variables
				$_GET['task'] = $task;
				$_REQUEST['task'] = $task;
				$_GET['sectionid'] = $sectionid;
				$_REQUEST['sectionid'] = $sectionid;
				$_GET['id'] = $id;
				$_REQUEST['id'] = $id;
				$_GET['Itemid'] = $Itemid;
				$_REQUEST['Itemid'] = $Itemid;
				$_GET['order'] = $order;
				$_REQUEST['order'] = $order;
				$_GET['filter'] = $filter;
				$_REQUEST['filter'] = $filter;
				$_GET['limit'] = $limit;
				$_REQUEST['limit'] = $limit;
				$_GET['limitstart'] = $limitstart;
				$_REQUEST['limitstart'] = $limitstart;

				$QUERY_STRING = "option=com_content&task=$task&sectionid=$sectionid&id=$id&Itemid=$Itemid&order=$order&filter=$filter&limit=$limit&limitstart=$limitstart";
			}
			else if (isset ($url_array[$pos +7]) && $url_array[$pos +7] != '' && $url_array[$pos +5] > 1000 && (in_array('archivecategory', $url_array) || in_array('archivesection', $url_array)))
			{
				// $option/$task/$id/$limit/$limitstart/year/month/module
				$task = $url_array[$pos +1];
				$id = $url_array[$pos +2];
				$limit = $url_array[$pos +3];
				$limitstart = $url_array[$pos +4];
				$year = $url_array[$pos +5];
				$month = $url_array[$pos +6];
				$module = $url_array[$pos +7];

				// pass data onto global variables
				$_GET['task'] = $task;
				$_REQUEST['task'] = $task;
				$_GET['id'] = $id;
				$_REQUEST['id'] = $id;
				$_GET['limit'] = $limit;
				$_REQUEST['limit'] = $limit;
				$_GET['limitstart'] = $limitstart;
				$_REQUEST['limitstart'] = $limitstart;
				$_GET['year'] = $year;
				$_REQUEST['year'] = $year;
				$_GET['month'] = $month;
				$_REQUEST['month'] = $month;
				$_GET['module'] = $module;
				$_REQUEST['module'] = $module;

				$QUERY_STRING = "option=com_content&task=$task&id=$id&limit=$limit&limitstart=$limitstart&year=$year&month=$month&module=$module";
			}
			else if (isset ($url_array[$pos +7]) && $url_array[$pos +7] != '' && $url_array[$pos +6] > 1000 && (in_array('archivecategory', $url_array) || in_array('archivesection', $url_array)))
			{
				// $option/$task/$id/$Itemid/$limit/$limitstart/year/month
				$task = $url_array[$pos +1];
				$id = $url_array[$pos +2];
				$Itemid = $url_array[$pos +3];
				$limit = $url_array[$pos +4];
				$limitstart = $url_array[$pos +5];
				$year = $url_array[$pos +6];
				$month = $url_array[$pos +7];

				// pass data onto global variables
				$_GET['task'] = $task;
				$_REQUEST['task'] = $task;
				$_GET['id'] = $id;
				$_REQUEST['id'] = $id;
				$_GET['Itemid'] = $Itemid;
				$_REQUEST['Itemid'] = $Itemid;
				$_GET['limit'] = $limit;
				$_REQUEST['limit'] = $limit;
				$_GET['limitstart'] = $limitstart;
				$_REQUEST['limitstart'] = $limitstart;
				$_GET['year'] = $year;
				$_REQUEST['year'] = $year;
				$_GET['month'] = $month;
				$_REQUEST['month'] = $month;

				$QUERY_STRING = "option=com_content&task=$task&id=$id&Itemid=$Itemid&limit=$limit&limitstart=$limitstart&year=$year&month=$month";
			}
			else if (isset ($url_array[$pos +7]) && $url_array[$pos +7] != '' && in_array('category', $url_array) && (strpos($url_array[$pos +5], 'order,') !== false))
			{
				// $option/$task/$sectionid/$id/$Itemid/$order/$limit/$limitstart
				$task = $url_array[$pos +1];
				$sectionid = $url_array[$pos +2];
				$id = $url_array[$pos +3];
				$Itemid = $url_array[$pos +4];
				$order = str_replace('order,', '', $url_array[$pos +5]);
				$limit = $url_array[$pos +6];
				$limitstart = $url_array[$pos +7];

				// pass data onto global variables
				$_GET['task'] = $task;
				$_REQUEST['task'] = $task;
				$_GET['sectionid'] = $sectionid;
				$_REQUEST['sectionid'] = $sectionid;
				$_GET['id'] = $id;
				$_REQUEST['id'] = $id;
				$_GET['Itemid'] = $Itemid;
				$_REQUEST['Itemid'] = $Itemid;
				$_GET['order'] = $order;
				$_REQUEST['order'] = $order;
				$_GET['limit'] = $limit;
				$_REQUEST['limit'] = $limit;
				$_GET['limitstart'] = $limitstart;
				$_REQUEST['limitstart'] = $limitstart;

				$QUERY_STRING = "option=com_content&task=$task&sectionid=$sectionid&id=$id&Itemid=$Itemid&order=$order&limit=$limit&limitstart=$limitstart";
			}
			else if (isset ($url_array[$pos +6]) && $url_array[$pos +6] != '')
			{
				// $option/$task/$sectionid/$id/$Itemid/$limit/$limitstart
				$task = $url_array[$pos +1];
				$sectionid = $url_array[$pos +2];
				$id = $url_array[$pos +3];
				$Itemid = $url_array[$pos +4];
				$limit = $url_array[$pos +5];
				$limitstart = $url_array[$pos +6];

				// pass data onto global variables
				$_GET['task'] = $task;
				$_REQUEST['task'] = $task;
				$_GET['sectionid'] = $sectionid;
				$_REQUEST['sectionid'] = $sectionid;
				$_GET['id'] = $id;
				$_REQUEST['id'] = $id;
				$_GET['Itemid'] = $Itemid;
				$_REQUEST['Itemid'] = $Itemid;
				$_GET['limit'] = $limit;
				$_REQUEST['limit'] = $limit;
				$_GET['limitstart'] = $limitstart;
				$_REQUEST['limitstart'] = $limitstart;

				$QUERY_STRING = "option=com_content&task=$task&sectionid=$sectionid&id=$id&Itemid=$Itemid&limit=$limit&limitstart=$limitstart";
			}
			else if (isset ($url_array[$pos +5]) && $url_array[$pos +5] != '')
			{
				// $option/$task/$id/$Itemid/$limit/$limitstart
				$task = $url_array[$pos +1];
				$id = $url_array[$pos +2];
				$Itemid = $url_array[$pos +3];
				$limit = $url_array[$pos +4];
				$limitstart = $url_array[$pos +5];

				// pass data onto global variables
				$_GET['task'] = $task;
				$_REQUEST['task'] = $task;
				$_GET['id'] = $id;
				$_REQUEST['id'] = $id;
				$_GET['Itemid'] = $Itemid;
				$_REQUEST['Itemid'] = $Itemid;
				$_GET['limit'] = $limit;
				$_REQUEST['limit'] = $limit;
				$_GET['limitstart'] = $limitstart;
				$_REQUEST['limitstart'] = $limitstart;

				$QUERY_STRING = "option=com_content&task=$task&id=$id&Itemid=$Itemid&limit=$limit&limitstart=$limitstart";
			}
			else if (isset ($url_array[$pos +4]) && $url_array[$pos +4] != '' && (in_array('archivecategory', $url_array) || in_array('archivesection', $url_array)))
			{
				// $option/$task/$year/$month/$module
				$task = $url_array[$pos +1];
				$year = $url_array[$pos +2];
				$month = $url_array[$pos +3];
				$module = $url_array[$pos +4];

				// pass data onto global variables
				$_GET['task'] = $task;
				$_REQUEST['task'] = $task;
				$_GET['year'] = $year;
				$_REQUEST['year'] = $year;
				$_GET['month'] = $month;
				$_REQUEST['month'] = $month;
				$_GET['module'] = $module;
				$_REQUEST['module'] = $module;

				$QUERY_STRING = "option=com_content&task=$task&year=$year&month=$month&module=$module";
			}
			else if (!(isset ($url_array[$pos +5]) && $url_array[$pos +5] != '') && isset ($url_array[$pos +4]) && $url_array[$pos +4] != '')
			{
				// $option/$task/$sectionid/$id/$Itemid
				$task = $url_array[$pos +1];
				$sectionid = $url_array[$pos +2];
				$id = $url_array[$pos +3];
				$Itemid = $url_array[$pos +4];

				// pass data onto global variables
				$_GET['task'] = $task;
				$_REQUEST['task'] = $task;
				$_GET['sectionid'] = $sectionid;
				$_REQUEST['sectionid'] = $sectionid;
				$_GET['id'] = $id;
				$_REQUEST['id'] = $id;
				$_GET['Itemid'] = $Itemid;
				$_REQUEST['Itemid'] = $Itemid;

				$QUERY_STRING = "option=com_content&task=$task&sectionid=$sectionid&id=$id&Itemid=$Itemid";
			}
			else if (!(isset ($url_array[$pos +4]) && $url_array[$pos +4] != '') && (isset ($url_array[$pos +3]) && $url_array[$pos +3] != ''))
			{
				// $option/$task/$id/$Itemid
				$task = $url_array[$pos +1];
				$id = $url_array[$pos +2];
				$Itemid = $url_array[$pos +3];

				// pass data onto global variables
				$_GET['task'] = $task;
				$_REQUEST['task'] = $task;
				$_GET['id'] = $id;
				$_REQUEST['id'] = $id;
				$_GET['Itemid'] = $Itemid;
				$_REQUEST['Itemid'] = $Itemid;

				$QUERY_STRING = "option=com_content&task=$task&id=$id&Itemid=$Itemid";
			}
			else if (!(isset ($url_array[$pos +3]) && $url_array[$pos +3] != '') && (isset ($url_array[$pos +2]) && $url_array[$pos +2] != ''))
			{
				// $option/$task/$id
				$task = $url_array[$pos +1];
				$id = $url_array[$pos +2];

				// pass data onto global variables
				$_GET['task'] = $task;
				$_REQUEST['task'] = $task;
				$_GET['id'] = $id;
				$_REQUEST['id'] = $id;

				$QUERY_STRING = "option=com_content&task=$task&id=$id";
			}
			else if (!(isset ($url_array[$pos +2]) && $url_array[$pos +2] != '') && (isset ($url_array[$pos +1]) && $url_array[$pos +1] != ''))
			{
				// $option/$task
				$task = $url_array[$pos +1];

				$_GET['task'] = $task;
				$_REQUEST['task'] = $task;

				$QUERY_STRING = 'option=com_content&task=' . $task;
			}

			if ($lang != '') {
				$QUERY_STRING .= '&amp;lang=' . $lang;
			}

			$_SERVER['QUERY_STRING'] = $QUERY_STRING;
			$REQUEST_URI = $uri[0] . 'index.php?' . $QUERY_STRING;
			$_SERVER['REQUEST_URI'] = $REQUEST_URI;

		}
		else if (in_array('component', $url_array))
		{
			$name = 'component';
			/*
			Components
			http://www.domain.com/component/$name,$value
			*/
			$uri = explode('component/', $_SERVER['REQUEST_URI']);
			$uri_array = explode('/', $uri[1]);
			$QUERY_STRING = '';

			// needed for check if component exists
			$path = $mosConfig_absolute_path . '/components';
			$dirlist = array ();
			if (is_dir($path))
			{
				$base = opendir($path);
				while (false !== ($dir = readdir($base)))
				{
					if ($dir !== '.' && $dir !== '..' && is_dir($path . '/' . $dir) && strtolower($dir) !== 'cvs' && strtolower($dir) !== '.svn') {
						$dirlist[] = $dir;
					}
				}
				closedir($base);
			}

			foreach ($uri_array as $value)
			{
				$temp = explode(',', $value);
				if (isset ($temp[0]) && $temp[0] != '' && isset ($temp[1]) && $temp[1] != '')
				{
					$_GET[$temp[0]] = $temp[1];
					$_REQUEST[$temp[0]] = $temp[1];

					// check to ensure component actually exists
					if ($temp[0] == 'option')
					{
						$check = '';
						if (count($dirlist)) {
							foreach ($dirlist as $dir) {
								if ($temp[1] == $dir) {
									$check = 1;
									break;
								}
							}
						}
						// redirect to 404 page if no component found to match url
						if (!$check)
						{
							header('HTTP/1.0 404 Not Found');
							require_once ($mosConfig_absolute_path . '/templates/404.php');
							exit (404);
						}
					}

					if ($QUERY_STRING == '') {
						$QUERY_STRING .= "$temp[0]=$temp[1]";
					} else {
						$QUERY_STRING .= "&$temp[0]=$temp[1]";
					}
				}
			}

			$_SERVER['QUERY_STRING'] = $QUERY_STRING;
			$REQUEST_URI = $uri[0] . 'index.php?' . $QUERY_STRING;
			$_SERVER['REQUEST_URI'] = $REQUEST_URI;

		}
		// let this go through and the rest of the system should handle it properly
	}

}