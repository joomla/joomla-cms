<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 * Templates Component Module Model
 *
 * @package		Joomla.Administrator
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

		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		// Get the pagination request variables
		$this->_client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', 0, '', 'int'));
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option.'.'.$this->_client->id.'.limitstart', 'limitstart', 0, 'int');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
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
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
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

	function _buildQuery()
	{
		$query = 'SELECT * FROM #__menu_template'.
				' WHERE client_id='.$this->_client->id.
				' GROUP BY template';
		return $query;
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
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			
			require_once JPATH_COMPONENT.DS.'helpers'.DS.'template.php';
			$tBaseDir = $this->_client->path.DS.'templates';
			$rows = TemplatesHelper::parseXMLTemplateFiles($tBaseDir);		
			
			$total= $this->getTotal();
			for($i = 0; $i < $total; $i++)  {
				$this->_data[$i]->assigned = TemplatesHelper::isTemplateNameAssigned($this->_data[$i]->template,$this->_data[$i]->client_id);
				$this->_data[$i]->home = TemplatesHelper::isTemplateNameDefault($this->_data[$i]->template,$this->_data[$i]->client_id);
				$this->_data[$i]->xmldata = $rows[$this->_data[$i]->template];
			}
		}
		return $this->_data;
	}
	
}
