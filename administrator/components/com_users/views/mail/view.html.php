<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Users mail view.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_users
 */
class UsersViewMail extends JViewLegacy
{
	/**
	 * @var object form object
	 */
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		// Get data from the model
		$this->form = $this->get('Form');

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('COM_USERS_MASS_MAIL'), 'massmail.png');
		JToolbarHelper::custom('mail.send', 'envelope.png', 'send_f2.png', 'COM_USERS_TOOLBAR_MAIL_SEND_MAIL', false);
		JToolbarHelper::cancel('mail.cancel');
		JToolbarHelper::divider();
		JToolbarHelper::preferences('com_users');
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_USERS_MASS_MAIL_USERS');
	}
}
