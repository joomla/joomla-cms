<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Sysinfo View class for the Admin component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @since		1.6
 */
class AdminViewSysinfo extends JViewLegacy
{
	/**
	 * @var array some php settings
	 */
	protected $php_settings=null;
	/**
	 * @var array config values
	 */
	protected $config=null;
	/**
	 * @var array somme system values
	 */
	protected $info=null;
	/**
	 * @var string php info
	 */
	protected $php_info=null;
	/**
	 * @var array informations about writable state of directories
	 */
	protected $directory=null;

	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// Initialise variables.
		$this->php_settings	= $this->get('PhpSettings');
		$this->config		= $this->get('config');
		$this->info			= $this->get('info');
		$this->php_info		= $this->get('PhpInfo');
		$this->directory	= $this->get('directory');

		$this->addToolbar();
		$this->_setSubMenu();
		parent::display($tpl);
	}

	/**
	 * Setup the SubMenu
	 *
	 * @since	1.6
	 */
	protected function _setSubMenu()
	{
		$contents = $this->loadTemplate('navigation');
		$document = JFactory::getDocument();
		$document->setBuffer($contents, 'modules', 'submenu');
	}

	/**
	 * Setup the Toolbar
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_ADMIN_SYSTEM_INFORMATION'), 'systeminfo.png');
		JToolBarHelper::help('JHELP_SITE_SYSTEM_INFORMATION');
	}
}
