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
 * Content Component Blog Model
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.1
 */
class JContentBlog
{
	function &getFrontpageData(&$access, &$params)
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize some variables
		 */
		$db			= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$noauth	= !$mainframe->getCfg('shownoauth');
		$offset		= $mainframe->getCfg('offset');
		$now		= $mainframe->get('requestTime');
		$nullDate	= $db->getNullDate();
		$gid			= $user->get('gid');
		$rows		= array ();

		// Ordering control
		$orderby_pri	= $params->def('orderby_pri', '');
		$orderby_sec	= $params->def('orderby_sec', '');
		$order_sec		= JContentHelper::orderbySecondary($orderby_sec);
		$order_pri		= JContentHelper::orderbyPrimary($orderby_pri);

		$voting = JContentHelper::buildVotingQuery();

		$where = JContentHelper::buildWhere(1, $access, $noauth, $gid, 0, $now);
		$where = (count($where) ? "\n WHERE ".implode("\n AND ", $where) : '');

		// query records
		$query = "SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by," .
				"\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.attribs, a.urls, a.ordering, a.metakey, a.metadesc, a.access," .
				"\n CHAR_LENGTH( a.fulltext ) AS readmore, s.published AS sec_pub, cc.published AS cat_pub, s.access AS sec_access, cc.access AS cat_access," .
				"\n u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups".
				$voting['select'] .
				"\n FROM #__content AS a" .
				"\n INNER JOIN #__content_frontpage AS f ON f.content_id = a.id" .
				"\n LEFT JOIN #__categories AS cc ON cc.id = a.catid" .
				"\n LEFT JOIN #__sections AS s ON s.id = a.sectionid" .
				"\n LEFT JOIN #__users AS u ON u.id = a.created_by" .
				"\n LEFT JOIN #__groups AS g ON a.access = g.id".
				$voting['join'].
				$where .
				"\n ORDER BY $order_pri $order_sec";
		$db->setQuery($query);
		$Arows = $db->loadObjectList();

		// special handling required as static content does not have a section / category id linkage
		$i = 0;
		foreach ($Arows as $row)
		{
			if (($row->sec_pub == 1 && $row->cat_pub == 1) || ($row->sec_pub == '' && $row->cat_pub == ''))
			{
				// check to determine if section or category is published
				if (($row->sec_access <= $gid && $row->cat_access <= $gid) || ($row->sec_access == '' && $row->cat_access == ''))
				{
					// check to determine if section or category has proper access rights
					$rows[$i] = $row;
					$i ++;
				}
			}
		}

		return $rows;
	}

	function &getSectionData($id, &$access, &$params)
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize some variables
		 */
		$db			= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$noauth	= !$mainframe->getCfg('shownoauth');
		$now		= $mainframe->get('requestTime');
		$gid			= $user->get('gid');

		// needed for check whether section is published
		$check = ($id ? $id : 0);

		// new blog multiple section handling
		if (!$id)
		{
			$id = $params->def('sectionid', 0);
		}

		$where = JContentHelper::buildWhere(1, $access, $noauth, $gid, $id, $now);
		$where = (count($where) ? "\n WHERE ".implode("\n AND ", $where) : '');

		// Ordering control
		$orderby_sec	= $params->def('orderby_sec', 'rdate');
		$orderby_pri	= $params->def('orderby_pri', '');
		$order_sec		= JContentHelper::orderbySecondary($orderby_sec);
		$order_pri		= JContentHelper::orderbyPrimary($orderby_pri);

		$voting = JContentHelper::buildVotingQuery();

		// Main data query
		$query = "SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by," .
				"\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.attribs, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access," .
				"\n CHAR_LENGTH( a.fulltext ) AS readmore, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups".$voting['select'] .
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
			$secCheck = new JTableSection($db);
			$secCheck->load($check);

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

		return $rows;
	}

	function &getCategoryData($id, &$access, &$params)
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize variables
		 */
		$db			= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$noauth	= !$mainframe->getCfg('shownoauth');
		$now		= $mainframe->get('requestTime');
		$gid			= $user->get('gid');

		// needed for check whether section & category is published
		$check = ($id ? $id : 0);

		// new blog multiple section handling
		if (!$id)
		{
			$id = $params->def('categoryid', 0);
		}

		$where = JContentHelper::buildWhere(2, $access, $noauth, $gid, $id, $now);
		$where = (count($where) ? "\n WHERE ".implode("\n AND ", $where) : '');

		// Ordering control
		$orderby_sec	= $params->def('orderby_sec', 'rdate');
		$orderby_pri	= $params->def('orderby_pri', '');
		$order_sec		= JContentHelper::orderbySecondary($orderby_sec);
		$order_pri		= JContentHelper::orderbyPrimary($orderby_pri);

		$voting = JContentHelper::buildVotingQuery();

		// Main data query
		$query = "SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by," .
				"\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.attribs, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access," .
				"\n CHAR_LENGTH( a.fulltext ) AS readmore, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups".$voting['select'] .
				"\n FROM #__content AS a" .
				"\n LEFT JOIN #__categories AS cc ON cc.id = a.catid" .
				"\n LEFT JOIN #__users AS u ON u.id = a.created_by" .
				"\n LEFT JOIN #__sections AS s ON a.sectionid = s.id" .
				"\n LEFT JOIN #__groups AS g ON a.access = g.id".
				$voting['join'].
				$where.
				"\n ORDER BY $order_pri $order_sec";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// check whether section & category is published
		if (!count($rows))
		{
			$catCheck = new JTableCategory($db);
			$catCheck->load($check);

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

			$secCheck = new JTableSection($db);
			$secCheck->load($catCheck->section);

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

		return $rows;
	}
}
?>