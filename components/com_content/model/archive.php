<?php
/**
 * @version $Id: content.php 2851 2006-03-20 21:45:20Z Jinx $
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// require the component helper 
require_once (JApplicationHelper::getPath('helper', 'com_content'));

/**
 * Content Component Archive Model
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.1
 */
class JContentArchive
{
	function getSectionData($id, &$access, &$params)
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize some variables
		 */
		$db			= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$noauth	= !$mainframe->getCfg('shownoauth');
		$option	= JRequest::getVar('option');
		$year		= JRequest::getVar('year', date('Y'));
		$month	= JRequest::getVar('month', date('m'));
		$gid			= $user->get('gid');

		// needed for check whether section is published
		$secID = ($id ? $id : 0);

		$params->set('intro_only', 1);
		$params->set('year', $year);
		$params->set('month', $month);

		// Ordering control
		$orderby_sec	= $params->def('orderby_sec', 'rdate');
		$orderby_pri	= $params->def('orderby_pri', '');
		$order_sec		= JContentHelper::orderbySecondary($orderby_sec);
		$order_pri		= JContentHelper::orderbyPrimary($orderby_pri);

		// Build the WHERE clause for the database query
		$where = JContentHelper::buildWhere(-1, $access, $noauth, $gid, $id, NULL, $year, $month);
		$where = (count($where) ? "\n WHERE ".implode("\n AND ", $where) : '');

		// checks to see if 'All Sections' options used
		if ($id == 0)
		{
			$check = null;
		}
		else
		{
			$check = "\n AND a.sectionid = $id";
		}

		// query to determine if there are any archived entries for the section
		$query = "SELECT a.id" .
				"\n FROM #__content as a" .
				"\n WHERE a.state = -1".
				$check;
		$db->setQuery($query);
		$items = $db->loadObjectList();
		$archives = count($items);

		$voting = JContentHelper::buildVotingQuery();

		// Main Query
		$query = "SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by," .
				"\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.attribs, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access," .
				"\n CHAR_LENGTH( a.fulltext ) AS readmore, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups".$voting['select'].
				"\n FROM #__content AS a" .
				"\n INNER JOIN #__categories AS cc ON cc.id = a.catid" .
				"\n LEFT JOIN #__users AS u ON u.id = a.created_by" .
				"\n LEFT JOIN #__sections AS s ON a.sectionid = s.id" .
				"\n LEFT JOIN #__groups AS g ON a.access = g.id".
				$voting['join'].
				$where.
				"\n ORDER BY $order_pri $order_sec";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// check whether section is published
		if (!count($rows))
		{
			$secCheck = new JModelSection($db);
			$secCheck->load($secID);

			/*
			* check whether section is published
			*/
			if (!$secCheck->published)
			{
				JError::raiseError( 404, JText::_("Resource Not Found") );
			}
			/*
			* check whether section access level allows access
			*/
			if ($secCheck->access > $gid)
			{
				JError::raiseError( 403, JText::_("Access Forbidden") );
			}
		}

		if (!$archives)
		{
			return false;
		}
		else
		{
			return $rows;
		}
	}

	function getCategoryData($id, &$access, &$params)
	{
		global $mainframe, $Itemid;

		// Parameters
		$db			= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$noauth	= !$mainframe->getCfg('shownoauth');
		$now		= $mainframe->get('requestTime');
		$gid			= $user->get('gid');
		$year		= JRequest::getVar( 'year', date('Y') );
		$month	= JRequest::getVar( 'month', date('m') );
		$module	= JRequest::getVar( 'module', '' );

		// needed for check whether section & category is published
		$catID = ($id ? $id : 0);

		// used by archive module
		if ($module)
		{
			$check = '';
		}
		else
		{
			$check = "\n AND a.catid = $id";
		}

		$params->set('year', $year);
		$params->set('month', $month);

		// Ordering control
		$orderby_sec	= $params->def('orderby', 'rdate');
		$order_sec		= JContentHelper::orderbySecondary($orderby_sec);

		// used in query
		$where = JContentHelper::buildWhere(-2, $access, $noauth, $gid, $id, NULL, $year, $month);
		$where = (count($where) ? "\n WHERE ".implode("\n AND ", $where) : '');

		// query to determine if there are any archived entries for the category
		$query = "SELECT a.id" .
				"\n FROM #__content as a" .
				"\n WHERE a.state = -1".
				$check;
		$db->setQuery($query);
		$items = $db->loadObjectList();
		$archives = count($items);

		$voting = JContentHelper::buildVotingQuery();

		$query = "SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by," .
				"\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.attribs, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access," .
				"\n CHAR_LENGTH( a.fulltext ) AS readmore, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups".$voting['select'].
				"\n FROM #__content AS a" .
				"\n INNER JOIN #__categories AS cc ON cc.id = a.catid" .
				"\n LEFT JOIN #__users AS u ON u.id = a.created_by" .
				"\n LEFT JOIN #__sections AS s ON a.sectionid = s.id" .
				"\n LEFT JOIN #__groups AS g ON a.access = g.id".
				$voting['join'].
				$where.
				"\n ORDER BY $order_sec";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// check whether section & category is published
		if (!count($rows))
		{
			$catCheck = new JModelCategory($db);
			$catCheck->load($catID);

			/*
			* check whether category is published
			*/
			if (!$catCheck->published)
			{
				JError::raiseError( 404, JText::_("Resource Not Found") );
			}
			/*
			* check whether category access level allows access
			*/
			if ($catCheck->access > $gid)
			{
				JError::raiseError( 403, JText::_("Access Forbidden") );
			}

			$secCheck = new JModelSection($db);
			$secCheck->load($catCheck->section);

			/*
			* check whether section is published
			*/
			if (!$secCheck->published)
			{
				JError::raiseError( 404, JText::_("Resource Not Found") );
			}
			/*
			* check whether category access level allows access
			*/
			if ($secCheck->access > $gid)
			{
				JError::raiseError( 403, JText::_("Access Forbidden") );
			}
		}

		if (!$archives)
		{
			return false;
		}
		else
		{
			return $rows;
		}
	}
}
?>