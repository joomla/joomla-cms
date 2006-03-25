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
 * Content Component Section Model
 *
 * @author	Louis Landry <louis.landry@joomla.org>
 * @package Joomla
 * @subpackage Content
 * @since 1.1
 */
class JModelSection extends JModel
{
	/**
	 * Category id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Section data
	 *
	 * @var object
	 */
	var $_section = null;

	/**
	 * Categories data
	 *
	 * @var array
	 */
	var $_categories = null;

	/**
	 * Content data in category array
	 *
	 * @var array
	 */
	var $_content = array();

	/**
	 * Method to set the section id
	 *
	 * @access	public
	 * @param	int	Section ID number
	 */
	function setId($id)
	{
		// Set new ID and wipe data
		$this->_id				= $id;
		$this->_section		= null;
		$this->_categories	= null;
		$this->_content		= array();
	}

	/**
	 * Method to get section data for the current section
	 *
	 * @since 1.1
	 */
	function getSection()
	{
		/*
		 * Initialize some variables
		 */
		$user = & $this->_app->getUser();

		/*
		 * Load the Category data
		 */
		if ($this->_loadSection())
		{
			/*
			 * Make sure the category is published
			 */
			if (!$this->_section->published)
			{
				JError::raiseError(404, JText::_("Resource Not Found"));
				return false;
			}
			/*
			 * check whether category access level allows access
			 */
			if ($this->_section->access > $user->get('gid'))
			{
				JError::raiseError(403, JText::_("Access Forbidden"));
				return false;
			}
		}
		return $this->_section;
	}

	/**
	 * Method to get sibling category data for the current category
	 *
	 * @since 1.1
	 */
	function getCategories()
	{
		/*
		 * Initialize some variables
		 */
		$user = & $this->_app->getUser();

		/*
		 * Load the Category data
		 */
		if ($this->_loadSection() && $this->_loadCategories())
		{
			/*
			 * Make sure the category is published
			 */
			if (!$this->_section->published)
			{
				JError::raiseError(404, JText::_("Resource Not Found"));
				return false;
			}
			/*
			 * check whether category access level allows access
			 */
			if ($this->_section->access > $user->get('gid'))
			{
				JError::raiseError(403, JText::_("Access Forbidden"));
				return false;
			}
		}
		return $this->_categories;
	}

	/**
	 * Method to get content item data for the current section
	 *
	 * @param	int	$state	The content state to pull from for the current
	 * section
	 * @since 1.1
	 */
	function getContent($state = 1)
	{
		/*
		 * Initialize some variables
		 */
		$user = & $this->_app->getUser();

		/*
		 * Load the Category data
		 */
		if ($this->_loadSection() && $this->_loadContent($state))
		{
			/*
			 * Make sure the category is published
			 */
			if (!$this->_section->published)
			{
				JError::raiseError(404, JText::_("Resource Not Found"));
				return false;
			}
			/*
			 * check whether category access level allows access
			 */
			if ($this->_section->access > $user->get('gid'))
			{
				JError::raiseError(403, JText::_("Access Forbidden"));
				return false;
			}
		}
		return $this->_content[$state];
	}

	/**
	 * Method to get archived article data for the current section
	 *
	 * @param	int	$state	The content state to pull from for the current section
	 * @since 1.5
	 */
	function getArchives($state = -1)
	{
		return $this->getContent(-1);
	}

	/**
	 * Method to get archived article data for the current section
	 *
	 * @param	int	$state	The content state to pull from for the current section
	 * @since 1.5
	 */
	function getTree()
	{
		return $this->_loadTree();
	}

	/**
	 * Method to load section data if it doesn't exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _loadSection()
	{
		if (empty($this->_section))
		{
			/*
			* Lets get the information for the current section
			*/
			if ($this->_id)
			{
				$where = "\n WHERE id = '$this->_id'";
			}
			else
			{
				$where = null;
			}

			$query = "SELECT *" .
					"\n FROM #__sections" .
					$where. 
					"\n LIMIT 1";
			$this->_db->setQuery($query);
			return $this->_db->loadObject($this->_section);
		}
		return true;
	}

	/**
	 * Method to load sibling category data if it doesn't exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _loadCategories()
	{
		/*
		 * Lets load the siblings if they don't already exist
		 */
		if (empty($this->_categories))
		{
			$user		= & $this->_app->getUser();
			$noauth	= !$this->_app->getCfg('shownoauth');
			$gid			= $user->get('gid');
			$now		= $this->_app->get('requestTime');
			$nullDate	= $this->_db->getNullDate();
			$params	= & $this->_menu->parameters;
			
			// Ordering control
			$orderby = $params->get('orderby', '');
			$orderby = JContentHelper::orderbySecondary($orderby);
	
			// Handle the access permissions part of the main database query
			if ($user->authorize('action', 'edit', 'content', 'all'))
			{
				$xwhere = '';
				$xwhere2 = "\n AND b.state >= 0";
			}
			else
			{
				$xwhere = "\n AND a.published = 1";
				$xwhere2 = "\n AND b.state = 1" .
						"\n AND ( b.publish_up = '$nullDate' OR b.publish_up <= '$now' )" .
						"\n AND ( b.publish_down = '$nullDate' OR b.publish_down >= '$now' )";
			}
	
			// Determine whether to show/hide the empty categories and sections
			$empty = null;
			$empty_sec = null;
			// show/hide empty categories in section
			if (!$params->get('empty_cat_section'))
			{
				$empty_sec = "\n HAVING numitems > 0";
			}
	
			// Handle the access permissions
			$access_check = null;
			if ($noauth)
			{
				$access_check = "\n AND a.access <= $gid";
			}
	
			// Query of categories within section
			$query = "SELECT a.*, COUNT( b.id ) AS numitems" .
					"\n FROM #__categories AS a" .
					"\n LEFT JOIN #__content AS b ON b.catid = a.id".
					$xwhere2 .
					"\n WHERE a.section = '$this->_id'".
					$xwhere.
					$access_check .
					"\n GROUP BY a.id".$empty.$empty_sec .
					"\n ORDER BY $orderby";
			$this->_db->setQuery($query);
			$this->_categories = & $this->_db->loadObjectList();
		}		
		return true;
	}

	/**
	 * Method to load content item data for items in the category if they don't
	 * exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _loadContent($state = 1)
	{
		if (empty($this->_section))
		{
			return false; // TODO: set error -- can't get siblings when we don't know the category
		}

		/*
		 * Lets load the content if it doesn't already exist
		 */
		if (empty($this->_content[$state]))
		{
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
			$where	= $this->_buildContentWhere($state);
			$orderby	= $this->_buildContentOrderBy($state);

			$query = "SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by," .
					"\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.attribs, a.hits, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access," .
					"\n CHAR_LENGTH( a.`fulltext` ) AS readmore, u.name AS author, u.usertype, cc.name AS category, g.name AS groups".$voting['select'] .
					"\n FROM #__content AS a" .
					"\n INNER JOIN #__categories AS cc ON cc.id = a.catid" .
					"\n LEFT JOIN #__sections AS s ON s.id = a.sectionid" .
					"\n LEFT JOIN #__users AS u ON u.id = a.created_by" .
					"\n LEFT JOIN #__groups AS g ON a.access = g.id".
					$voting['join'].
					$where.
					$orderby;
			$this->_db->setQuery($query, $limitstart, $limit);
			$this->_content[$state] = $this->_db->loadObjectList();
		}		
		return true;
	}

	/**
	 * Method to load content item data for items in the category if they don't
	 * exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _loadTree()
	{
		/*
		 * Lets load the content if it doesn't already exist
		 */
		if (empty($this->_tree))
		{
		$user		= & $this->_app->getUser();
		$gid			= $user->get('gid');
		$now		=$this->_app->get('requestTime');
		$nullDate	= $this->_db->getNullDate();
	
		// Get the information for the current section
		if ($this->_id) {
			$and = "\n AND a.section = '$this->_id'";
		} else {
			$and = null;
		}

		// Query of categories within section
		$query = "SELECT a.name AS catname, a.title AS cattitle, b.* " .
				"\n FROM #__categories AS a" .
				"\n INNER JOIN #__content AS b ON b.catid = a.id" .
				"\n AND b.state = 1" .
				"\n AND ( b.publish_up = '$nullDate' OR b.publish_up <= '$now' )" .
				"\n AND ( b.publish_down = '$nullDate' OR b.publish_down >= '$now' )";
				"\n WHERE a.published = 1" .
				$and .
				"\n AND a.access <= $gid" .
				"\n ORDER BY a.catid, a.ordering, b.ordering";
			$this->_db->setQuery($query);
			$this->_tree = $this->_db->loadObjectList();
		}		
		return true;
	}

	function _buildContentOrderBy($state = 1)
	{
		$filter_order		= JRequest::getVar('filter_order');
		$filter_order_Dir	= JRequest::getVar('filter_order_Dir');

		$orderby = "\n ORDER BY ";
		if ($filter_order && $filter_order_Dir)
		{
			$orderby .= "$filter_order $filter_order_Dir, ";
		}

		$params = & $this->_menu->parameters;
		switch ($state)
		{
			case -1:
				/*
				 * Special ordering for archive content items
				 */
				$orderby_sec	= $params->def('orderby', 'rdate');
				$order_sec		= JContentHelper::orderbySecondary($orderby_sec);
				break;
			case 1:
			default:
				$orderby_sec	= $params->def('orderby_sec', 'rdate');
				$orderby_pri	= $params->def('orderby_pri', '');
				$secondary		= JContentHelper::orderbySecondary($orderby_sec);
				$primary			= JContentHelper::orderbyPrimary($orderby_pri);
				break;
		}
		$orderby .= "$primary $secondary DESC";
		
		return $orderby;
	}

	function _buildContentWhere($state = 1)
	{
		$user		= & $this->_app->getUser();
		$gid			= $user->get('gid');
		$now		=$this->_app->get('requestTime');
		$noauth	= !$this->_app->getCfg('shownoauth');
		$nullDate	= $this->_db->getNullDate();
	
		/*
		 * First thing we need to do is assert that the content items are in
		 * the current category
		 */
		$where = "\n WHERE a.access <= $gid";
		if ($this->_id)
		{
			$where .= "\n AND a.sectionid = $this->_id";
		}

		$where .= "\n AND s.access <= $gid";
		$where .= "\n AND cc.access <= $gid";
		$where .= "\n AND s.published = 1";
		$where .= "\n AND cc.published = 1";

		/*
		 * Regular Published Content
		 */
		switch ($state)
		{
			case 1:
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
				break;

			/*
			 * Archive Content
			 */
			case -1:
				/*
				 * Get some request vars specific to this state
				 */
				$year		= JRequest::getVar( 'year', date('Y') );
				$month	= JRequest::getVar( 'month', date('m') );

				$where .= "\n AND a.state = '-1'";
				$where .= "\n AND YEAR( a.created ) = '$year'";
				$where .= "\n AND MONTH( a.created ) = '$month'";
				break;
			default:
				$where .= "\n AND a.state = '$state'";
				break;
		}
	
		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		 * for the filter onto the WHERE clause of the content item query.
		 */
		$params = & $this->_menu->parameters;
		if ($params->get('filter'))
		{
			$filter = JRequest::getVar('filter', '', 'request');
			if ($filter)
			{
				// clean filter variable
				$filter = strtolower($filter);

				switch ($params->get('filter_type'))
				{
					case 'title' :
						$where .= "\n AND LOWER( a.title ) LIKE '%$filter%'";
						break;

					case 'author' :
						$where .= "\n AND ( ( LOWER( u.name ) LIKE '%$filter%' ) OR ( LOWER( a.created_by_alias ) LIKE '%$filter%' ) )";
						break;

					case 'hits' :
						$where .= "\n AND a.hits LIKE '%$filter%'";
						break;
				}
			}
		}
		return $where;
	}
}
?>