<?php
/**
 * @version $Id$
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
 * Content Component Frontpage Model
 *
 * @author	Louis Landry <louis.landry@joomla.org>
 * @package Joomla
 * @subpackage Content
 * @since 1.1
 */
class JModelFrontpage extends JObject
{
	/**
	 * Database Connector
	 *
	 * @var object
	 */
	var $_db;

	/**
	 * Menu Itemid parameters
	 *
	 * @var object
	 */
	var $_mparams = null;

	/**
	 * Content data in category array
	 *
	 * @var array
	 */
	var $_content = null;

	/**
	 * Number of content rows in category array
	 *
	 * @var array
	 */
	var $_contentTotal = null;

	/**
	 * Content data JPagination Object array
	 *
	 * @var array
	 */
	var $_contentPagination = null;

	/**
	 * Constructor.
	 *
	 * @access protected
	 */
	function __construct( &$db, &$params)
	{
		$this->_mparams	= &$params;
		$this->_db				= & $db;
	}

	/**
	 * Method to set the section id
	 *
	 * @access	public
	 * @param	int	Section ID number
	 */
	function setId($id)
	{
		$this->_content					= null;
		$this->_contentTotal			= null;
		$this->_contentPagination	= null;
	}

	/**
	 * Method to get current menu parameters
	 *
	 * @since 1.1
	 */
	function & getMenuParams()
	{
		return $this->_mparams;
	}

	/**
	 * Method to get content item data for the frontpage
	 *
	 * @since 1.1
	 */
	function getContentData()
	{
		/*
		 * Load the Category data
		 */
		$this->_loadContent();
		return $this->_content;
	}

	/**
	 * Method to load content item data for items in the category if they don't
	 * exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _loadContent()
	{
		/*
		 * Lets load the content if it doesn't already exist
		 */
		if (empty($this->_content))
		{
			global $mainframe;
	
			$user		= & $mainframe->getUser();
			$gid			= $user->get('gid');

			/*
			 * Get the pagination request variables
			 */
			$limit		= JRequest::getVar('limit', 0, '', 'int');
			$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

			/*
			 * If voting is turned on, get voting data as well for the content
			 * items
			 */
			$voting	= JContentHelper::buildVotingQuery();

			/*
			 * Get the WHERE and ORDER BY clauses for the query
			 */
			$where	= $this->_buildContentWhere();
			$orderby	= $this->_buildContentOrderBy();

			$query = "SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by," .
					"\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.attribs, a.urls, a.ordering, a.metakey, a.metadesc, a.access," .
					"\n CHAR_LENGTH( a.`fulltext` ) AS readmore, s.published AS sec_pub, cc.published AS cat_pub, s.access AS sec_access, cc.access AS cat_access," .
					"\n u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups".
					$voting['select'] .
					"\n FROM #__content AS a" .
					"\n INNER JOIN #__content_frontpage AS f ON f.content_id = a.id" .
					"\n LEFT JOIN #__categories AS cc ON cc.id = a.catid" .
					"\n LEFT JOIN #__sections AS s ON s.id = a.sectionid" .
					"\n LEFT JOIN #__users AS u ON u.id = a.created_by" .
					"\n LEFT JOIN #__groups AS g ON a.access = g.id".
					$voting['join'].
					$where.
					$orderby;
			$this->_db->setQuery($query, $limitstart, $limit);
			$Arows = $this->_db->loadObjectList();

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
			$this->_content = $rows;
		}		
		return true;
	}

	/**
	 * Method to load total number of content items in the category.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _loadContentTotal()
	{
		/*
		 * Lets load total number of content items in the category
		 */
		if (!isset($this->_contentTotal) || is_null($this->_contentTotal))
		{
			/*
			 * Get the WHERE and ORDER BY clauses for the query
			 */
			$where	= $this->_buildContentWhere();
			$orderby	= $this->_buildContentOrderBy();
	
			$query = "SELECT COUNT(a.id) as numitems" .
					"\n FROM #__content AS a" .
					"\n LEFT JOIN #__users AS u ON u.id = a.created_by" .
					"\n LEFT JOIN #__groups AS g ON a.access = g.id" .
					$where.
					$orderby;
			$this->_db->setQuery($query);
			$this->_contentTotal = $this->_db->loadResult();
		}		
		return true;
	}

	function _buildContentOrderBy()
	{
		$orderby_sec	= $this->_mparams->def('orderby_sec', '');
		$orderby_pri	= $this->_mparams->def('orderby_pri', '');
		$secondary		= JContentHelper::orderbySecondary($orderby_sec);
		$primary			= JContentHelper::orderbyPrimary($orderby_pri);

		$orderby = "\n ORDER BY $primary $secondary";
		
		return $orderby;
	}

	function _buildContentWhere()
	{
		global $mainframe;

		$user		= & $mainframe->getUser();
		$gid			= $user->get('gid');
		$now		=$mainframe->get('requestTime');
		$noauth	= !$mainframe->getCfg('shownoauth');
		$nullDate	= $this->_db->getNullDate();
	
		/*
		 * First thing we need to do is assert that the content items are in
		 * the current category
		 */
		$where = "\n WHERE 1";
		
		/*
		 * Does the user have access to view the items?
		 */
		if ($noauth)
		{
			$where .= "\n AND a.access <= $gid";
		}
		
		if ($user->authorize('action', 'edit', 'content', 'all'))
		{
			$where .= "\n AND a.state >= 0";
		}
		else
		{
			$where .= "\n AND a.state = 1" .
					"\n AND ( publish_up = '$nullDate' OR publish_up <= '$now' )" .
					"\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )";
		}
	
		$where .= "\n AND s.published = 1";
		$where .= "\n AND cc.published = 1";

		return $where;
	}
}
?>