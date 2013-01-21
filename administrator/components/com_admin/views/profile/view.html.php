<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class to allow users edit their own profile.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 * @since       1.6
 */
class AdminViewProfile extends JViewLegacy
{
	/*
	 * @var    JForm  The JForm for this view
	 * @since  1.6
	 */
	protected $form;

	/*
	 * @var    JObject  The JObject holding data for this view
	 * @since  1.6
	 */
	protected $item;

	/*
	 * @var   JObject  The JObject holding state data for this view such as parameters, paths and filters.
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Method to display the view
	 *
	 * @param  string  $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since  1.6
	 */
	public function display($tpl = null)
	{
		$this->form			= $this->get('Form');
		$this->item			= $this->get('Item');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->form->setValue('password', null);
		$this->form->setValue('password2', null);

		parent::display($tpl);
		$this->addToolbar();
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
		JFactory::getApplication()->input->set('hidemainmenu', 1);

		JToolbarHelper::title(JText::_('COM_ADMIN_VIEW_PROFILE_TITLE'), 'user-profile');
		JToolbarHelper::apply('profile.apply');
		JToolbarHelper::save('profile.save');
		JToolbarHelper::cancel('profile.cancel', 'JTOOLBAR_CLOSE');
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_ADMIN_USER_PROFILE_EDIT');
	}
}
