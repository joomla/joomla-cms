<?php
/**
 * @version		$Id: view.html.php 11952 2009-06-01 03:21:19Z robs $
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Contact component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_contact
 * @since		1.5
 */
class ContactViewContact extends JView
{

	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		$app	= &JFactory::getApplication();
		$state		= $this->get('state');
		$item		= $this->get('item');
		$form		= $this->get('form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Bind the label to the form.
		$form->bind($item);

		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);
		$this->assignRef('form',	$form);
		$this->_setToolbar();
		parent::display($tpl);
		JRequest::setVar('hidemainmenu', true);

	}

	/**
	 * Setup the Toolbar
	 *
	 * @since	1.6
	 */
	protected function _setToolbar()
	{
		$user		= &JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		JRequest::setVar('hidemainmenu', 1);

		JToolBarHelper::title(JText::_('Contact_Manager_Contact'));
		JToolBarHelper::apply('contact.apply','JToolbar_Apply');
		JToolBarHelper::save('contact.save','JToolbar_Save');
		JToolBarHelper::addNew('contact.save2new', 'JToolbar_Save_and_new');
				// If an existing item, can save to a copy.
		if (!$isNew) {
			JToolBarHelper::custom('contact.save2copy','save-copy.png', 'save-copy_f2.png', 'JToolbar_Save_as_copy',false );
		}

		if (empty($this->item->id))  {
			JToolBarHelper::cancel('contact.cancel','JToolbar_Cancel');
		}
		else {
			JToolBarHelper::cancel('contact.cancel', 'JToolbar_Close');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.contact.edit'.'JToolbar_Help');
	}
}
