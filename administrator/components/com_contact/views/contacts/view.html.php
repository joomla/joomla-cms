<?php
/**
 * @version		$Id: view.html.php 11952 2009-06-01 03:21:19Z robs $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
	public $state;
	public $items;
	public $pagination;

	/**
	 * Display the view
	 *
	 * @return	void
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('state');
		$items		= $this->get('items');
		$pagination	= $this->get('pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		// Preprocess the list of items to find ordering divisions.
		// TODO: Complete the ordering stuff with nested sets
		foreach ($items as $i => &$item)
		{
			$item->order_up = true;
			$item->order_dn = true;
		}
		$this->assignRef('state',			$state);
		$this->assignRef('items',			$items);
		$this->assignRef('pagination',		$pagination);
		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 */
	protected function _setToolbar()
	{
		$state = $this->get('state');
		JToolBarHelper::title(JText::_('Contact_Manager_Contacts'), 'generic.png');
		JToolBarHelper::addNew('contact.edit', 'JToolbar_New');
		JToolBarHelper::editList('contact.edit','JToolbar_Edit');
		JToolBarHelper::divider();
		JToolBarHelper::publish('contacts.publish');
		JToolBarHelper::unpublish('contacts.unpublish');
		JToolBarHelper::archiveList('contacts.archive','JToolbar_Archive');
		if ($state->get('filter.published') == -2) {
			JToolBarHelper::deleteList('', 'contacts.delete');
		}
		else {
			JToolBarHelper::trash('contacts.trash');
		}
		JToolBarHelper::divider();

		JToolBarHelper::preferences('com_contact');
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.contact');
	}
}


