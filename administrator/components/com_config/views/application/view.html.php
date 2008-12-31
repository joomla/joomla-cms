<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
* @package		Joomla
* @subpackage	Config
*/
class ConfigViewApplication extends JView
{
	/**
	 * The configuration object
	 *
	 * @var JConfig
	 */
	protected $row;
	protected $ftp;
	protected $userparams;
	protected $mediaparams;

	/**
	 * Display the view
	 *
	 * @param	string	Optional sub-template
	 */
	function display($tpl = null)
	{
		// Initialize some variables
		$row = new JConfig();

		// MEMCACHE SETTINGS
		if (!empty($row->memcache_settings) && !is_array($row->memcache_settings)) {
			$row->memcache_settings = unserialize(stripslashes($row->memcache_settings));
		}

		// Load component specific configurations
		$table = &JTable::getInstance('component');
		$table->loadByOption('com_users');
		$userparams = new JParameter($table->params, JPATH_ADMINISTRATOR.DS.'components'.DS.'com_users'.DS.'config.xml');
		$table->loadByOption('com_media');
		$mediaparams = new JParameter($table->params, JPATH_ADMINISTRATOR.DS.'components'.DS.'com_media'.DS.'config.xml');

		// Build the component's submenu
		$submenu = $this->loadTemplate('navigation');

		// Set document data
		$document = &JFactory::getDocument();
		$document->setBuffer($submenu, 'modules', 'submenu');

		// Load settings for the FTP layer
		jimport('joomla.client.helper');
		$ftp = &JClientHelper::setCredentialsFromRequest('ftp');

		$this->assignRef('row',			$row);
		$this->assignRef('ftp',			$ftp);
		$this->assignRef('userparams',	$userparams);
		$this->assignRef('mediaparams',	$mediaparams);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 */
	protected function _setToolbar()
	{
		JToolBarHelper::title(JText::_('Global Configuration'), 'config.png');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel('cancel', 'Close');
		JToolBarHelper::help('screen.config');
	}
}
