<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a contact.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_contact
 * @since		1.6
 */
class ContactViewContact extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		// Initialiase variables.
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

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
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		// Since we don't track these assets at the item level, use the category id.
		$canDo		= ContactHelper::getActions($this->item->catid, 0);

		JToolBarHelper::title(JText::_('COM_CONTACT_MANAGER_CONTACT'), 'contact.png');

		// Build the actions for new and existing records.
		if ($isNew)  {
			// For new records, check the create permission.
			if ($isNew && (count($user->getAuthorisedCategories('com_contact', 'core.create')) > 0)) {
				JToolBarHelper::apply('contact.apply');
				JToolBarHelper::save('contact.save');
				JToolBarHelper::save2new('contact.save2new');
			}

			JToolBarHelper::cancel('contact.cancel');
		}
		else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId)) {
					JToolBarHelper::apply('contact.apply');
					JToolBarHelper::save('contact.save');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create')) {
						JToolBarHelper::save2new('contact.save2new');
					}
				}
			}

			// If checked out, we can still save
			if ($canDo->get('core.create')) {
				JToolBarHelper::save2copy('contact.save2copy');
			}

			JToolBarHelper::cancel('contact.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_CONTACTS_CONTACTS_EDIT');
	}
}
