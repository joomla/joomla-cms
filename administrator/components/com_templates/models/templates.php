<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

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
	protected $_data = null;

	/**
	 * Category total
	 *
	 * @var integer
	 */
	protected $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $_pagination = null;

	/**
	 * Client object
	 *
	 * @var object
	 */
	protected $_client = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct()
	{
		parent::__construct();

		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		// Get the pagination request variables
		$this->_client	= &JApplicationHelper::getClientInfo(JRequest::getVar('client', 0, '', 'int'));
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option.'.'.$this->_client->id.'.limitstart', 'limitstart', 0, 'int');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method to get the total number of Module items
	 *
	 * @return integer
	 */
	public function getTotal()
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
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Method to get the client object
	 *
	 * @return object
	 */
	public function getClient()
	{
		return $this->_client;
	}

	protected function _buildQuery()
	{
		$query = 'SELECT * FROM #__menu_template'.
				' WHERE client_id='.$this->_client->id.
				' GROUP BY template';
		return $query;
	}

	/**
	 * Method to get Templates item data
	 *
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			require_once JPATH_COMPONENT.DS.'helpers'.DS.'template.php';
			$tBaseDir = $this->_client->path.DS.'templates';
			$this->_data = TemplatesHelper::parseXMLTemplateFiles($tBaseDir);

			$total= $this->getTotal();
			foreach($this->_data as $dir => $data) {
				$this->_data[$dir]->assigned = TemplatesHelper::isTemplateNameAssigned($this->_data[$dir]->name,$this->_client->id);
				$this->_data[$dir]->home = TemplatesHelper::isTemplateNameDefault($this->_data[$dir]->name,$this->_client->id);
//				$this->_data[$dir]->xmldata = $rows[$this->_data[$dir]->name];
			}
		}
		return $this->_data;
	}

}
