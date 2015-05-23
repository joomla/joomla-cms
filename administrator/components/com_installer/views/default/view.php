<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Extension Manager Default View
 *
 * @since  1.5
 */
class InstallerViewDefault extends JViewLegacy
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  Configuration array
	 *
	 * @since   1.5
	 */
	public function __construct($config = null)
	{
		$app = JFactory::getApplication();
		parent::__construct($config);
		$this->_addPath('template', $this->_basePath . '/views/default/tmpl');
		$this->_addPath('template', JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_installer/default');
	}

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function display($tpl = null)
	{
		// Get data from the model.
		$state = $this->get('State');

		// Are there messages to display?
		$showMessage = false;

		if (is_object($state))
		{
			$message1		= $state->get('message');
			$message2		= $state->get('extension_message');
			$showMessage	= ($message1 || $message2);
		}

		$this->showMessage = $showMessage;
		$this->state = &$state;

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$canDo	= JHelperContent::getActions('com_installer');
		JToolbarHelper::title(JText::_('COM_INSTALLER_HEADER_' . $this->getName()), 'puzzle install');

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_installer');
			JToolbarHelper::divider();
		}

		// Document.
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_INSTALLER_TITLE_' . $this->getName()));

		// Render side bar.
		$this->sidebar = JHtmlSidebar::render();
	}
}
