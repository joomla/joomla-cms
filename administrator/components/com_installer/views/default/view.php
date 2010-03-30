<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Extension Manager Default View
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.5
 */
class InstallerViewDefault extends JView
{
	function __construct($config = null)
	{
		parent::__construct($config);
		$this->_addPath('template', $this->_basePath.DS.'views'.DS.'default'.DS.'tmpl');
	}

	function display($tpl=null)
	{
		// Get data from the model
		$state		= &$this->get('State');

		// Are there messages to display ?
		$showMessage	= false;
		if (is_object($state))
		{
			$message1		= $state->get('message');
			$message2		= $state->get('extension_message');
			$showMessage	= ($message1 || $message2);
		}

		$this->assign('showMessage',	$showMessage);
		$this->assignRef('state',		$state);

		JHtml::_('behavior.tooltip');
		$this->_setToolbar();
		parent::display($tpl);
	}
	/**
	 * Display the toolbar
	 */
	protected function _setToolbar()
	{
		$canDo	= InstallerHelper::getActions();
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title(JText::_('INSTALLER_HEADER_' . $this->getName()), 'install.png');

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_installer');
			JToolBarHelper::divider();
		}
		JToolBarHelper::help('screen.installer','JTOOLBAR_HELP');
		// Document
		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('INSTALLER_TITLE_' . $this->getName()));
	}
}
