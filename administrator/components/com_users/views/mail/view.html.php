<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Users mail view.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 */
class UsersViewMail extends JView
{
	/**
	 * @var object form object
	 */
	public $form = null;

	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		// Get data from the model
		$form = &$this->get('Form');

		// Assign data to the view
		$this->assignRef('form', $form);

		// Set the toolbar
		$this->_setToolBar();

		// Display the view
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 *
	 * @since	1.6
	 */
	protected function _setToolBar()
	{
		JRequest::setVar('hidemainmenu', 1);

		JToolBarHelper::title(JText::_('E-mail Groups'), 'massmail.png');
		JToolBarHelper::custom('mail.send', 'send.png', 'send_f2.png', 'Users_Mail_Send_Mail', false);
		JToolBarHelper::cancel('mail.cancel');
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_users');
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.users');
	}
}
