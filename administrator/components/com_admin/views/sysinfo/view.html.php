<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Sysinfo View class for the Admin component
 *
 * @since  1.6
 */
class AdminViewSysinfo extends JViewLegacy
{
	/**
	 * Some PHP settings
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $php_settings = array();

	/**
	 * Config values
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $config = array();

	/**
	 * Some system values
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $info = array();

	/**
	 * PHP info
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $php_info = null;

	/**
	 * Information about writable state of directories
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $directory = array();

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin'))
		{
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$this->php_settings = $this->get('PhpSettings');
		$this->config       = $this->get('config');
		$this->info         = $this->get('info');
		$this->php_info     = $this->get('PhpInfo');
		$this->directory    = $this->get('directory');

		$this->addToolbar();
		$this->_setSubMenu();

		return parent::display($tpl);
	}

	/**
	 * Setup the SubMenu
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @note    Necessary for Hathor compatibility
	 */
	protected function _setSubMenu()
	{
		try
		{
			$contents = $this->loadTemplate('navigation');
			$document = JFactory::getDocument();
			$document->setBuffer($contents, 'modules', 'submenu');
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Setup the Toolbar
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_ADMIN_SYSTEM_INFORMATION'), 'info-2 systeminfo');
		JToolbarHelper::link(JRoute::_('index.php?option=com_admin&view=sysinfo&format=text'), 'COM_ADMIN_DOWNLOAD_SYSTEM_INFORMATION_TEXT', 'download');
		JToolbarHelper::link(JRoute::_('index.php?option=com_admin&view=sysinfo&format=json'), 'COM_ADMIN_DOWNLOAD_SYSTEM_INFORMATION_JSON', 'download');
		JToolbarHelper::help('JHELP_SITE_SYSTEM_INFORMATION');
	}
}
