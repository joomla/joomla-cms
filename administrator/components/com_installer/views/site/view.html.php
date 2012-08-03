<?php

/**
 * @author      Jeremy Wilken - Gnome on the run
 * @link        www.gnomeontherun.com
 * @copyright   Copyright 2011 Gnome on the run. All Rights Reserved.
 * @category    Administrator
 * @package     com_installer
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

include_once dirname(__FILE__).'/../default/view.php';

/**
 * Extension Manager Site View
 *
 * @package		SquareOne.Administrator
 * @subpackage	com_installer
 * @since		2.5
 */
class InstallerViewSite extends InstallerViewDefault
{
	/**
	 * @since	2.5
	 */
	function display($tpl=null)
	{
		// Get data from the model
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
        
        $distro = $this->getModel()->getDistro(JRequest::getVar('id', 0, '', 'int'));
        
        $this->assign('distro', $distro);

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	2.5
	 */
	protected function addToolbar()
	{
		$canDo	= InstallerHelper::getActions();
        
        JToolBarHelper::custom('site.install', 'upload', 'upload', 'JTOOLBAR_INSTALL', true, false);
		JToolBarHelper::custom('site.find', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_FIND_EXTENSIONS',false,false);
		JToolBarHelper::custom('site.purge', 'purge', 'purge', 'JTOOLBAR_PURGE_CACHE', false,false);
		JToolBarHelper::divider();
		parent::addToolbar();
		JToolBarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_UPDATE');
        JToolBarHelper::title(JText::sprintf('COM_INSTALLER_TITLE_SITE', $this->distro->name), 'install');
        $document = JFactory::getDocument();
		$document->setTitle(JText::sprintf('COM_INSTALLER_TITLE_' . $this->getName(), $this->distro->name));
	}
}
