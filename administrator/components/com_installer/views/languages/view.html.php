<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       2.5.7
 */
// No direct access
defined('_JEXEC') or die;

include_once dirname(__FILE__).'/../default/view.php';

/**
 * Language installer view
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       2.5.7
 */
class InstallerViewLanguages extends InstallerViewDefault
{
	/**
	 * @var object item list
	 */
	protected $items;

	/**
	 * @var object pagination information
	 */
	protected $pagination;

	/**
	 * @var object model state
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   null  $tpl  template to display
	 *
	 * @return mixed|void
	 */
	public function display($tpl=null)
	{
		// Get data from the model
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		$canDo	= InstallerHelper::getActions();
		JToolBarHelper::title(JText::_('COM_INSTALLER_HEADER_' . $this->getName()), 'install.png');

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::custom('languages.install', 'upload', 'upload', 'COM_INSTALLER_TOOLBAR_INSTALL', true, false);
			JToolBarHelper::custom('languages.find', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_FIND_LANGUAGES', false, false);
			JToolBarHelper::custom('languages.purge', 'purge', 'purge', 'JTOOLBAR_PURGE_CACHE', false, false);
			JToolBarHelper::divider();
			parent::addToolbar();
			// TODO: this help screen will need to be created
			JToolBarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_LANGUAGES');
		}
	}
}