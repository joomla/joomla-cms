<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
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
	protected $_id = null;

	/**
	 * client object
	 *
	 * @var object
	 */
	protected $_client = null;

	/**
	 * params object
	 *
	 * @var object
	 */
	protected $_params = null;

	/**
	 * Template name
	 *
	 * @var string
	 */
	protected $_template = null;

	/**
	 * Template parametersets
	 *
	 * @var array
	 */
	protected $_paramsets = null;

	/**
	 * Currently active parameterset
	 *
	 * @var int
	 */
	protected $_activerecord = null;

	/**
	 * Data of the template
	 *
	 * @var object
	 */
	protected $_data = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_template		= JRequest::getVar('template');
		$this->_activerecord 	= JRequest::getVar('id', '', 'method', 'int');

		$this->_client	= &JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
	}

	/**
	 * Method to get a Template
	 *
	 * @since 1.6
	 */
	public function &getData()
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
	public function &getClient()
	{
		return $this->_client;
	}

	public function &getCurrentParams()
	{
		$this->getData();
		return $this->_paramsets[$this->_activerecord];
	}

	public function &getTemplate()
	{
		return $this->_template;
	}

	public function &getParametersets()
	{
		return $this->_paramsets;
	}

	public function &getId()
	{
		return $this->_activerecord;
	}

	/**
	 * Method to store the Template
	 *
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	public function store($params)
	{
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'templates.php';
		$menus		= JRequest::getVar('selections', array(), 'post', 'array');
		$menutype		= JRequest::getVar('menus', '', 'post', 'string');
		$description = JRequest::getVar('description', '', 'post', 'string');
		JArrayHelper::toInteger($menus);
		$query = 'UPDATE #__template_styles SET description='.$this->_db->Quote($description).
					', params = '.$this->_db->Quote(json_encode($params)).
					' WHERE id = '.$this->_db->Quote($this->_activerecord);
		$this->_db->setQuery($query);
		if (!$this->_db->query()) {
			return JError::raiseWarning(500, $this->_db->getError());
		}
		if ($this->_client->id==1)
			return true;
		if ($menutype=='default') {
			$query = 'UPDATE #__template_styles SET home=0 WHERE client_id='.$this->_db->Quote($this->_client->id);
			$this->_db->setQuery($query);
			$this->_db->query();
			$query = 'UPDATE #__template_styles SET home=1 WHERE id='.$this->_db->Quote(JRequest::getInt('id'));
			$this->_db->setQuery($query);
			$this->_db->query();
		}
		if ($this->_client->id == '1')	{
			return true;
		}

		$query = 'UPDATE #__menu SET template_style_id=0 WHERE template_style_id='.$this->_db->Quote($this->_id);
		$this->_db->setQuery($query);
		if (!$this->_db->query()) {
			return JError::raiseWarning(500, $this->_db->getError());
		}

		return true;
	}

	/**
	 * Method to load Template data
	 *
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	protected function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_paramsets) && empty($this->_data))
		{
			$query = 'SELECT * FROM #__template_styles WHERE template = '.$this->_db->Quote($this->_template);
			$this->_db->setQuery($query);
			$this->_paramsets = $this->_db->loadObjectList('id');
			if($this->_activerecord == 0)
			{
				if(count($this->_paramsets))
				{
					$this->_activerecord = current($this->_paramsets)->id;
				} else {
					$this->_activerecord = 0;
				}
			}
			require_once JPATH_COMPONENT.DS.'helpers'.DS.'templates.php';
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
			$this->_data	= TemplatesHelper::parseXMLTemplateFile($tBaseDir, $this->_template);

			$this->_paramsets[$this->_activerecord]->params = new JParameter($this->_paramsets[$this->_activerecord]->params, $xml, 'template');

			$assigned = TemplatesHelper::isTemplateAssigned($this->_template);

/**			if ($this->_data->home) {
				$this->_data->pages = 'all';
			} elseif (!$assigned) {
				$this->_data->pages = 'none';
			} else {
				$this->_data->pages = null;
			}**/
		}
		return true;
	}

	public function add()
	{
		$query = 'SELECT params FROM #__template_styles WHERE id = '.$this->_db->Quote($this->_id);
		$this->_db->setQuery($query);
		$oldparams = $this->_db->loadResult();
		$query = 'INSERT INTO #__template_styles (template,client_id,home,description,params) VALUE ('.
				$this->_db->Quote($this->_template).','.$this->_db->Quote($this->_client->id).',0,'.$this->_db->Quote(JText::_('New Style')).','.
				$this->_db->Quote($oldparams).')';
		$this->_db->setQuery($query);
		$this->_db->query();
		return $this->_db->insertid();
	}

	public function delete()
	{
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'templates.php';
		$query = 'SELECT COUNT(*) FROM #__template_styles WHERE template = '.$this->_db->Quote($this->_template).
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
		$query = 'DELETE FROM #__template_styles WHERE id = '.$this->_db->Quote(JRequest::getInt('id'));
		$this->_db->setQuery($query);
		$this->_db->query();
		return true;
	}

	/**
	 * Method to initialise the Template data
	 *
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	protected function _initData()
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
