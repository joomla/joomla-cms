<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 * Templates Component Module Model
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class TemplatesModelTemplates extends JModel
{
	/**
	 * Category ata array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Category total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Client object
	 *
	 * @var object
	 */
	var $_client = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		global $mainframe, $option;

		// Get the pagination request variables
		$this->_client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', 0, '', 'int'));
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option.'.'.$this->_client->id.'.limitstart', 'limitstart', 0, 'int');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method to get Templates item data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$this->_loadData();
		}

		return $this->_data;
	}

	/**
	 * Method to get the total number of Module items
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$this->_loadData();
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the Templates
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Method to get the client object
	 *
	 * @access public
	 * @return object
	 */
	function getClient()
	{
		return $this->_client;
	}

	function setDefault($id)
	{
		$query = 'DELETE FROM #__templates_menu' .
				' WHERE client_id = '.(int) $this->_client->id .
				' AND (menuid = 0 OR template = '.$this->_db->Quote($id).')';
		$this->_db->setQuery($query);
		$this->_db->query();

		$query = 'INSERT INTO #__templates_menu' .
				' SET client_id = '.(int) $this->_client->id .', template = '.$this->_db->Quote($id).', menuid = 0';
		$this->_db->setQuery($query);
		$this->_db->query();
	}

	function _loadData()
	{
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'template.php';

		$tBaseDir = $this->_client->path.DS.'templates';

		//get template xml file info
		$rows = array();
		$rows = TemplatesHelper::parseXMLTemplateFiles($tBaseDir);
		$this->_total = count($rows);

		// set dynamic template information
		for($i = 0; $i < $this->_total; $i++)  {
			$rows[$i]->assigned		= TemplatesHelper::isTemplateAssigned($rows[$i]->directory);
			$rows[$i]->published	= TemplatesHelper::isTemplateDefault($rows[$i]->directory, $this->_client->id);
		}

		if ($this->getState('limit') > 0)
			$this->_data = array_slice($rows, $this->getState('limitstart'), $this->getState('limit'));
		else
			$this->_data = $rows;
	}
}
