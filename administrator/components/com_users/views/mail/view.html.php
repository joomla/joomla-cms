<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Users mail view.
 *
 * @since  1.6
 */
class UsersViewMail extends JViewLegacy
{
	/**
	 * @var object form object
	 */
	protected $form;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// Redirect to admin index if mass mailer disabled in conf
		if (JFactory::getApplication()->get('massmailoff', 0) == 1)
		{
			JFactory::getApplication()->redirect(JRoute::_('index.php', false));
		}

		// Get data from the model
		$this->form = $this->get('Form');

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
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('COM_USERS_MASS_MAIL'), 'users massmail');
		JToolbarHelper::custom('mail.send', 'envelope.png', 'send_f2.png', 'COM_USERS_TOOLBAR_MAIL_SEND_MAIL', false);
		JToolbarHelper::cancel('mail.cancel');
		JToolbarHelper::divider();
		JToolbarHelper::preferences('com_users');
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_USERS_MASS_MAIL_USERS');
	}
}
