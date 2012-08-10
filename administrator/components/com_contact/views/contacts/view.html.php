<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of contacts.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 * @since       1.6
 */
class ContactViewContacts extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @return	void
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Preprocess the list of items to find ordering divisions.
		// TODO: Complete the ordering stuff with nested sets
		foreach ($this->items as &$item) {
			$item->order_up = true;
			$item->order_dn = true;
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
		require_once JPATH_COMPONENT.'/helpers/contact.php';
		$canDo	= ContactHelper::getActions($this->state->get('filter.category_id'));
		$user	= JFactory::getUser();
		JToolbarHelper::title(JText::_('COM_CONTACT_MANAGER_CONTACTS'), 'contact.png');

		if ($canDo->get('core.create') || (count($user->getAuthorisedCategories('com_contact', 'core.create'))) > 0) {
			JToolbarHelper::addNew('contact.add');
		}

		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own'))) {
			JToolbarHelper::editList('contact.edit');
		}

		if ($canDo->get('core.edit.state')) {
			JToolbarHelper::divider();
			JToolbarHelper::publish('contacts.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('contacts.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::divider();
			JToolbarHelper::archiveList('contacts.archive');
			JToolbarHelper::checkin('contacts.checkin');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolbarHelper::deleteList('', 'contacts.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolbarHelper::divider();
		}
		elseif ($canDo->get('core.edit.state')) {
			JToolbarHelper::trash('contacts.trash');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin')) {
			JToolbarHelper::preferences('com_contact');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_COMPONENTS_CONTACTS_CONTACTS');
	}
}
