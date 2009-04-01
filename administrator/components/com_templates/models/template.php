<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/**
 * @package		Joomla.Administrator
 * @subpackage	Templates
 */
class TemplatesModelTemplate extends JModel
{
	/**
	 * Template id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Template data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * client object
	 *
	 * @var object
	 */
	var $_client = null;

	/**
	 * params object
	 *
	 * @var object
	 */
	var $_params = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		$id			= JRequest::getVar('id', '', 'method', 'cmd');
		$cid		= JRequest::getVar('cid', array($id), 'method', 'array');
		$cid		= array(JFilterInput::clean(@$cid[0], 'cmd'));
		$this->setId($cid[0]);

		$this->_client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
	}

	/**
	 * Method to set the Template identifier
	 *
	 * @access	public
	 * @param	int Template identifier
	 */
	function setId($id)
	{
		// Set Template id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get a Template
	 *
	 * @since 1.6
	 */
	function &getData()
	{
		// Load the data
		if (!$this->_loadData())
			$this->_initData();

		return $this->_data;
	}

	/**
	 * Method to get the client object
	 *
	 * @since 1.6
	 */
	function &getClient()
	{
		return $this->_client;
	}

	function &getParams()
	{
		$this->getData();
		return $this->_params;
	}

	function &getTemplate()
	{
		return $this->_id;
	}

	/**
	 * Method to store the Template
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function store($params)
	{
		$menus		= JRequest::getVar('selections', array(), 'post', 'array');
		$default	= JRequest::getBool('default');
		JArrayHelper::toInteger($menus);

		if($this->_client->id == '1')
		{
			$query = 'DELETE FROM #__templates_menu' .
					' WHERE client_id = 1' .
					' AND template = '.$this->_db->Quote( $this->_id );
			$this->_db->setQuery($query);
			$this->_db->query();

			$query = 'INSERT INTO #__templates_menu' .
					' SET client_id = 1,' .
					' template = '. $this->_db->Quote( $this->_id ) .
					', menuid = 0'.
					', params = '.$this->_db->Quote(json_encode($params));
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				return JError::raiseWarning( 500, $this->_db->getError() );
			}
			return true;
		}
		// Reset all existing assignments
		$query = 'DELETE FROM #__templates_menu' .
				' WHERE client_id = 0' .
				' AND template = '.$this->_db->Quote( $this->_id );
		$this->_db->setQuery($query);
		$this->_db->query();

		if ($default) {
			$menus = array( 0 );
		}

		foreach ($menus as $menuid)
		{
			// If 'None' is not in array
			if ((int) $menuid >= 0)
			{
				// check if there is already a template assigned to this menu item
				$query = 'DELETE FROM #__templates_menu' .
						' WHERE client_id = 0' .
						' AND menuid = '.(int) $menuid;
				$this->_db->setQuery($query);
				if (!$this->_db->query()) {
					return JError::raiseWarning( 500, $this->_db->getError() );
				}

				$query = 'INSERT INTO #__templates_menu' .
						' SET client_id = 0,' .
						' template = '. $this->_db->Quote( $this->_id ) .
						', menuid = '.(int) $menuid.
						', params = '.$this->_db->Quote(json_encode($params));
				$this->_db->setQuery($query);
				if (!$this->_db->query()) {
					return JError::raiseWarning( 500, $this->_db->getError() );
				}
			}
		}

		return true;
	}

	/**
	 * Method to load Template data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$db =& JFactory::getDBO();
			require_once JPATH_COMPONENT.DS.'helpers'.DS.'template.php';

			$tBaseDir	= JPath::clean($this->_client->path.DS.'templates');

			if (!is_dir( $tBaseDir . DS . $this->_id )) {
				return JError::raiseWarning( 500, JText::_('Template not found') );
			}
			$lang =& JFactory::getLanguage();
			 // 1.5 or Core
			$lang->load( 'tpl_'.$this->_id, $this->_client->path );
			// 1.6 3PD Templates
			$lang->load( 'joomla', $this->_client->path.DS.'templates'.DS.$this->_id );

			$query = 'SELECT params FROM #__templates_menu WHERE template = '.$db->Quote($this->_id).' AND client_id = 0';
			$db->setQuery($query);
			$ini	= $db->loadResult();
			$xml	= $this->_client->path.DS.'templates'.DS.$this->_id.DS.'templateDetails.xml';
			$row	= TemplatesHelper::parseXMLTemplateFile($tBaseDir, $this->_id);

			$this->_params = new JParameter($ini, $xml, 'template');

			$assigned = TemplatesHelper::isTemplateAssigned($row->directory);
			$default = TemplatesHelper::isTemplateDefault($row->directory, $this->_client->id);
			if ($default) {
				$row->pages = 'all';
			} elseif (!$assigned) {
				$row->pages = 'none';
			} else {
				$row->pages = null;
			}

			$this->_data = $row;
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the Template data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$template = new stdClass();
			$template->name					= null;
			$template->description			= null;
			$template->pages				= null;
			$this->_data = $template;
			return (boolean) $this->_data;
		}
		return true;
	}
}
