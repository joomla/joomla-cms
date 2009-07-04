<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Sysinfo View class for the Admin component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @since		1.6
 */
class AdminViewSysinfo extends JView
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
		// Get the values
		$php_settings = & $this->get('PhpSettings');
		$config = & $this->get('config');
		$info = & $this->get('info');
		$php_info = & $this->get('PhpInfo');
		$directory = & $this->get('directory');
		// Has to be removed (present in the config)
		$editor = & $this->get('editor');
		
		// Assign values to the view
		$this->assignRef('php_settings', $php_settings);
		$this->assignRef('config', $config);
		$this->assignRef('info', $info);
		$this->assignRef('php_info', $php_info);
		$this->assignRef('directory', $directory);
		// Has to be removed (present in the config)
		$this->assignRef('editor', $editor);

		// Setup the toobar
		$this->_setToolbar();
		
		// Setup the menu
		$this->_setSubMenu();
		
		// Display the view
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
		$document = &JFactory::getDocument();
		$document->setBuffer($contents, 'modules', 'submenu');
	}
	/**
	 * Setup the Toolbar
	 *
	 * @since	1.6
	 */
	protected function _setToolbar()
	{
		JToolBarHelper::title(JText::_('Admin_Information'), 'systeminfo.png');
		JToolBarHelper::help('screen.system.info');
	}
}
