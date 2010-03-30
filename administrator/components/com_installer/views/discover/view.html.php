<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Extension Manager Manage View
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.6
 */

include_once dirname(__FILE__).DS.'..'.DS.'default'.DS.'view.php';

class InstallerViewDiscover extends InstallerViewDefault
{
	function display($tpl=null)
	{

		// Get data from the model
		$state		= &$this->get('State');
		$items		= &$this->get('Items');
		$pagination	= &$this->get('Pagination');

		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
	}
	/**
	 * Setup the Toolbar
	 *
	 * @since	1.6
	 */
	protected function _setToolbar()
	{
		$canDo	= InstallerHelper::getActions();
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::custom('discover.install', 'config', 'config', 'JTOOLBAR_INSTALL', true, false);
		JToolBarHelper::custom('discover.refresh', 'refresh', 'refresh','INSTALLER_TOOLBAR_DISCOVER',false,false);
		JToolBarHelper::custom('discover.purge', 'purge', 'purge', 'JTOOLBAR_PURGE_CACHE', false,false);
		JToolBarHelper::divider();
		parent::_setToolbar();
	}
}
