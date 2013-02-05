<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * User note edit view
 *
 * @package     Joomla.Administrator
 * @subpackage  com_users
 * @since       2.5
 */
class UsersViewNote extends JViewLegacy
{
	/**
	 * The edit form.
	 *
	 * @var    JForm
	 * @since  2.5
	 */
	protected $form;

	/**
	 * The item data.
	 *
	 * @var    object
	 * @since  2.5
	 */
	protected $item;

	/**
	 * The model state.
	 *
	 * @var    JObject
	 * @since  2.5
	 */
	protected $state;

	/**
	 * Override the display method for the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		// Initialise view variables.
		$this->state = $this->get('State');
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Get the component HTML helpers
		JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Display the toolbar.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function addToolbar()
	{
		$input = JFactory::getApplication()->input;
		$input->set('hidemainmenu', 1);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo		= UsersHelper::getActions($this->state->get('filter.category_id'), $this->item->id);

		JToolBarHelper::title(JText::_('COM_USERS_NOTES'), 'user');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || (count($user->getAuthorisedCategories('com_users', 'core.create')))))
		{
			JToolBarHelper::apply('note.apply');
			JToolBarHelper::save('note.save');
		}

		if (!$checkedOut && (count($user->getAuthorisedCategories('com_users', 'core.create'))))
		{
			JToolBarHelper::save2new('note.save2new');
		}

		// If an existing item, can save to a copy.
		if (!$isNew && (count($user->getAuthorisedCategories('com_users', 'core.create')) > 0))
		{
			JToolBarHelper::save2copy('note.save2copy');
		}
		if (empty($this->item->id))
		{
			JToolBarHelper::cancel('note.cancel');
		}
		else
		{
			JToolBarHelper::cancel('note.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_USERS_USER_NOTES_EDIT');
	}
}
