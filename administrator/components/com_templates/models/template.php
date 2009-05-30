<?php
/**
 * @version		$Id: template.php 11838 2009-05-27 22:07:20Z eddieajau $
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

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
	 * Template style object list
	 *
	 * @var array
	 */
	
	var $_style = null;
	

	/**
	 * params object
	 *
	 * @var object
	 */
	var $_params = null;

	/**
	 * Template name
	 *
	 * @var string
	 */
	var $_template = null; 
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		$id			= JRequest::getVar('id', '', 'method', 'int');
		$cid		= JRequest::getVar('cid', array($id), 'method', 'array');
		$cid		= array(JFilterInput::clean(@$cid[0], 'int'));
		$this->setId($cid[0]);

		$this->_client	= &JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
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
		if (empty($this->_template)) {
			require_once JPATH_COMPONENT.DS.'helpers'.DS.'template.php';
			$this->_template=TemplatesHelper::getTemplateName($this->_id);
		}
		return $this->_template;
	}

	function &getId()
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
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'template.php';
		$menus		= JRequest::getVar('selections', array(), 'post', 'array');
		$menutype		= JRequest::getVar('menus', '', 'post', 'string');
		$description = JRequest::getVar('description', '', 'post', 'string');
		JArrayHelper::toInteger($menus);
		$query = 'UPDATE #__menu_template SET description='.$this->_db->Quote($description).
					', params = '.$this->_db->Quote(json_encode($params)).
					' WHERE id = '.$this->_db->Quote($this->_id);
		$this->_db->setQuery($query);
		if (!$this->_db->query()) {
			return JError::raiseWarning(500, $this->_db->getError());
		}
		if ($this->_client->id==1)
			return true;
		if ($menutype=='default') {
			$query = 'UPDATE #__menu_template SET home=0 WHERE client_id='.$this->_db->Quote($this->_client->id);
			$this->_db->setQuery($query);
			$this->_db->query();
			$query = 'UPDATE #__menu_template SET home=1 WHERE id='.$this->_db->Quote($this->_id);
			$this->_db->setQuery($query);
			$this->_db->query();
		}
		if ($this->_client->id == '1')	{
			return true;
		}

		$query = 'UPDATE #__menu SET template_id=0 WHERE template_id='.$this->_db->Quote($this->_id);
		$this->_db->setQuery($query);
		if (!$this->_db->query()) {
			return JError::raiseWarning(500, $this->_db->getError());
		}

		foreach ($menus as $menuid)	{
			// If 'None' is not in array
			if ((int) $menuid >= 0)	{
				$query = 'UPDATE #__menu SET template_id='.$this->_db->Quote($this->_id).' WHERE id='.$this->_db->Quote($menuid);
				$this->_db->setQuery($query);
				if (!$this->_db->query()) {
					return JError::raiseWarning(500, $this->_db->getError());
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
			$query = 'SELECT * FROM #__menu_template WHERE id = '.$this->_db->Quote($this->_id);
			$this->_db->setQuery($query);
			$this->_db->query();
			if ($this->_db->getNumRows() == 0) {
				return JError::raiseWarning(500, JText::_('Template not found'));
			}
			$this->_data=$this->_db->loadObject();
			require_once JPATH_COMPONENT.DS.'helpers'.DS.'template.php';
			$this->_template=$this->_data->template;
			$tBaseDir	= JPath::clean($this->_client->path.DS.'templates');


			if (!is_dir($tBaseDir . DS . $this->_template)) {
				return JError::raiseWarning(500, JText::_('Template folder not found'));
			}

			$lang = &JFactory::getLanguage();
			 // 1.5 or Core
			$lang->load('tpl_'.$this->_template, $this->_client->path);
			// 1.6 3PD Templates
			$lang->load('joomla', $this->_client->path.DS.'templates'.DS.$this->_template);

			$xml	= $this->_client->path.DS.'templates'.DS.$this->_template.DS.'templateDetails.xml';
			$this->_data->xmldata	= TemplatesHelper::parseXMLTemplateFile($tBaseDir, $this->_template);

			$this->_params = new JParameter($this->_data->params, $xml, 'template');

			$assigned = TemplatesHelper::isTemplateAssigned($this->_id);
			
			if ($this->_data->home) {
				$this->_data->pages = 'all';
			} elseif (!$assigned) {
				$this->_data->pages = 'none';
			} else {
				$this->_data->pages = null;
			}
		}
		return true;
	}

	function &getStyle()
	{
		if (empty($this->_style)) {
			$query = 'SELECT id,description,home FROM #__menu_template '.
					'WHERE template = '.$this->_db->Quote($this->_template).
					' AND client_id = '.$this->_db->Quote($this->_client->id);
			$this->_db->setQuery($query);
			require_once JPATH_COMPONENT.DS.'helpers'.DS.'template.php';
			$this->_style = $this->_db->loadObjectList();
			for ($i = 0, $n = count($this->_style); $i < $n; $i++) {
				$this->_style[$i]->assigned = TemplatesHelper::isTemplateAssigned($this->_style[$i]->id);
				
			}
			
		}
		return $this->_style;
	}
	
	function add()
	{
		$query = 'SELECT params FROM #__menu_template WHERE id = '.$this->_db->Quote($this->_id);
		$this->_db->setQuery($query);
		$oldparams = $this->_db->loadResult();
		$query = 'INSERT INTO #__menu_template (template,client_id,home,description,params) VALUE ('.
				$this->_db->Quote($this->_template).','.$this->_db->Quote($this->_client->id).',0,'.$this->_db->Quote(JText::_('New Style')).','.
				$this->_db->Quote($oldparams).')';
		$this->_db->setQuery($query);
		$this->_db->query();
		return $this->_db->insertid();
	}
	
	function delete()
	{
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'template.php';
		$query = 'SELECT COUNT(*) FROM #__menu_template WHERE template = '.$this->_db->Quote($this->_template).
				' AND client_id = '.$this->_db->Quote($this->_client->id);
		$this->_db->setQuery($query);
		if ($this->_db->loadResult()==1)
		{
			JError::raiseWarning(500, JText::_('Template must have at least one style'));
			return false;
		}
		if (TemplatesHelper::isTemplateDefault($this->_id))
		{
			JError::raiseWarning(500, JText::_('Can not delete default style'));
			return false;
		}
		$query = 'DELETE FROM #__menu_template WHERE id = '.$this->_db->Quote($this->_id);
		$this->_db->setQuery($query);
		$this->_db->query();
		return true;
	}
	
	function setDefault()
	{
		$query = 'UPDATE #__menu_template SET home=0 WHERE client_id='.$this->_db->Quote($this->_client->id);
		$this->_db->setQuery($query);
		$this->_db->query();
		$query = 'UPDATE #__menu_template SET home=1 WHERE id='.$this->_db->Quote($this->_id);
		$this->_db->setQuery($query);
		$this->_db->query();
		return true;
	}
	
	function getOtherID()
	{
		$query = 'SELECT id FROM #__menu_template WHERE template = '.$this->_db->Quote($this->_template).
				' AND client_id = '.$this->_db->Quote($clientId).' LIMIT 1';
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
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
