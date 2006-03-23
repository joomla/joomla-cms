<?php
/**
 * @version $Id: frontpage.php 2874 2006-03-22 22:57:55Z webImagery $
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
 * Content Component Item Model
 *
 * @author	Louis Landry <louis@webimagery.net>
 * @package Joomla
 * @subpackage Content
 * @since 1.1
 */
class JModelItem extends JObject
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
	 * Constructor.
	 *
	 * @access protected
	 */
	function __construct( &$db, &$params, $id = null)
	{
		$this->_mparams	= &$params;
		$this->_db				= & $db;
		$this->_id				= $id;
	}

	/**
	 * Method to set the item id
	 *
	 * @access	public
	 * @param	int	Item ID number
	 */
	function setId($id)
	{
		/*
		 * Set new ID and wipe data
		 */
		$this->_id			= $id;
		$this->_content	= null;
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
		if ($this->_loadContent())
		{
			global $mainframe;
			$user	= & $mainframe->getUser();

			// Is the category published?
			if (!$this->_content->cat_pub && $this->_content->catid)
			{
				JError::raiseError( 404, JText::_("Resource Not Found") );
			}
			// Is the section published?
			if (!$this->_content->sec_pub && $this->_content->sectionid)
			{
				JError::raiseError( 404, JText::_("Resource Not Found") );
			}
			// Do we have access to the category?
			if (($this->_content->cat_access > $user->get('gid')) && $this->_content->catid)
			{
				JError::raiseError( 403, JText::_("Access Forbidden") );
			}
			// Do we have access to the section?
			if (($this->_content->sec_access > $user->get('gid')) && $this->_content->sectionid)
			{
				JError::raiseError( 403, JText::_("Access Forbidden") );
			}

			$this->_loadContentParams();

			/*
			 * Record the hit on the item if necessary
			 */
			$limitstart	= JRequest::getVar('limitstart',	0, '', 'int');
			if (!$this->_content->parameters->get('intro_only') && ($limitstart == 0))
			{
				$item = & JTable::getInstance('content', $this->_db);
				$item->hit($this->_id);
			}
			
		}
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
			/*
			 * If voting is turned on, get voting data as well for the content
			 * items
			 */
			$voting	= JContentHelper::buildVotingQuery();

			/*
			 * Get the WHERE clause for the query
			 */
			$where	= $this->_buildContentWhere();

			$query = "SELECT a.*, u.name AS author, u.usertype, cc.title AS category, s.title AS section," .
					"\n g.name AS groups, s.published AS sec_pub, cc.published AS cat_pub, s.access AS sec_access, cc.access AS cat_access".$voting['select'].
					"\n FROM #__content AS a" .
					"\n LEFT JOIN #__categories AS cc ON cc.id = a.catid" .
					"\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope = 'content'" .
					"\n LEFT JOIN #__users AS u ON u.id = a.created_by" .
					"\n LEFT JOIN #__groups AS g ON a.access = g.id".
					$voting['join'].
					$where;
			$this->_db->setQuery($query);
			return $this->_db->loadObject($this->_content);
		}		
		return true;
	}

	function _loadContentParams()
	{
		global $mainframe;

		$user				= & $mainframe->getUser();
		$MetaTitle		= $mainframe->getCfg('MetaTitle');
		$MetaAuthor	= $mainframe->getCfg('MetaAuthor');
		$pop				= JRequest::getVar('pop',	0, '', 'int');

		$params = new JParameter($this->_content->attribs);
		$params->set('intro_only', 0);
		$params->def('back_button', $mainframe->getCfg('back_button'));
		if ($this->_content->sectionid == 0)
		{
			$params->set('item_navigation', 0);
		}
		else
		{
			$params->set('item_navigation', $mainframe->getCfg('item_navigation'));
		}
		if ($MetaTitle == '1')
		{
			$mainframe->addMetaTag('title', $this->_content->title);
		}
		if ($MetaAuthor == '1')
		{
			$mainframe->addMetaTag('author', $this->_content->author);
		}

		/*
		 * Get some parameters from global configuration
		 */
		$params->def('link_titles',		$mainframe->getCfg('link_titles'));
		$params->def('author',			!$mainframe->getCfg('hideAuthor'));
		$params->def('createdate',	!$mainframe->getCfg('hideCreateDate'));
		$params->def('modifydate',	!$mainframe->getCfg('hideModifyDate'));
		$params->def('print',				!$mainframe->getCfg('hidePrint'));
		$params->def('pdf',					!$mainframe->getCfg('hidePdf'));
		$params->def('email',				!$mainframe->getCfg('hideEmail'));
		$params->def('rating',				$mainframe->getCfg('vote'));
		$params->def('icons',				$mainframe->getCfg('icons'));
		$params->def('readmore',		$mainframe->getCfg('readmore'));
		
		/*
		 * Get some item specific parameters
		 */
		$params->def('image',					1);
		$params->def('section',				0);
		$params->def('popup',					$pop);
		$params->def('section_link',		0);
		$params->def('category',			0);
		$params->def('category_link',	0);
		$params->def('introtext',			1);
		$params->def('pageclass_sfx',	'');
		$params->def('item_title',			1);
		$params->def('url',						1);

		if ($params->get('section_link') && $this->_content->sectionid)
		{
			$this->_content->section = JContentHelper::getSectionLink($this->_content);
		}

		if ($params->get('category_link') && $this->_content->catid)
		{
			$this->_content->category = JContentHelper::getCategoryLink($this->_content);
		}

		/*
		 * Show or hide the introtext column
		 */
		if ($params->get('introtext'))
		{
			$this->_content->text = $this->_content->introtext. ($params->get('intro_only') ? '' : chr(13).chr(13).$this->_content->fulltext);
		}
		else
		{
			$this->_content->text = $this->_content->fulltext;
		}

		$this->_content->parameters = & $params;
	}

	function _buildContentWhere()
	{
		global $mainframe;

		$user		= & $mainframe->getUser();
		$gid			= $user->get('gid');
		$now		=$mainframe->get('requestTime');
		$nullDate	= $this->_db->getNullDate();
	
		/*
		 * First thing we need to do is assert that the content item is the one
		 * we are looking for and we have access to it.
		 */
		$where = "\n WHERE a.id = $this->_id";
		$where .= "\n AND a.access <= $gid";
		
		if (!$user->authorize('action', 'edit', 'content', 'all'))
		{
			$where .= " AND ( a.state = 1 OR a.state = -1 )" .
					"\n AND ( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )" .
					"\n AND ( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )";
		}
	
		return $where;
	}
}
?>