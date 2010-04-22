<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Contacts component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @since		1.5
 */
class ContactViewContacts extends JView
{
	public $items;
	public $pagination;
	public $state;

	/**
	 * Display the view
	 *
	 * @return	void
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('items');
		$this->pagination	= $this->get('pagination');
		$this->state		= $this->get('state');

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
		$state = $this->get('state');
		JToolBarHelper::title(JText::_('COM_CONTACT_MANAGER_CONTACTS'), 'generic.png');
		JToolBarHelper::addNew('contact.edit', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('contact.edit','JTOOLBAR_EDIT');
		JToolBarHelper::divider();
		JToolBarHelper::publish('contacts.publish','JTOOLBAR_PUBLISH');
		JToolBarHelper::unpublish('contacts.unpublish','JTOOLBAR_UNPUBLISH');
		JToolBarHelper::divider();
		JToolBarHelper::archiveList('contacts.archive','JTOOLBAR_ARCHIVE');
		if ($state->get('filter.published') == -2) {
			JToolBarHelper::deleteList('', 'contacts.delete','JTOOLBAR_EMPTY_TRASH');
		} else {
			JToolBarHelper::trash('contacts.trash','JTOOLBAR_TRASH');
		}
		JToolBarHelper::divider();

		JToolBarHelper::preferences('com_contact');
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.contact','JTOOLBAR_HELP');
	}
}