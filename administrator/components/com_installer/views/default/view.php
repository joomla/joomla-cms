<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
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
	/**
	 * @since	1.5
	 */
	function __construct($config = null)
	{
		$app = JFactory::getApplication();
		parent::__construct($config);
		$this->_addPath('template', $this->_basePath . '/views/default/tmpl');
		$this->_addPath('template', JPATH_THEMES.'/'.$app->getTemplate().'/html/com_installer/default');
	}

	/**
	 * @since	1.5
	 */
	function display($tpl=null)
	{
		// Get data from the model
		$state	= $this->get('State');

		// Are there messages to display ?
		$showMessage	= false;
		if (is_object($state)) {
			$message1		= $state->get('message');
			$message2		= $state->get('extension_message');
			$showMessage	= ($message1 || $message2);
		}

		$this->assign('showMessage',	$showMessage);
		$this->assignRef('state',		$state);

		JHtml::_('behavior.tooltip');
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$canDo	= InstallerHelper::getActions();
		JToolBarHelper::title(JText::_('COM_INSTALLER_HEADER_' . $this->getName()), 'install.png');

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_installer');
			JToolBarHelper::divider();
		}

		// Document
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_INSTALLER_TITLE_' . $this->getName()));
	}
}
