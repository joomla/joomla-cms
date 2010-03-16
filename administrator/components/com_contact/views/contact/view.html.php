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
		// First, unpack the email_form options from the params
		$item->set('email_form', new JObject());
		foreach ($form->getFields('email_form') as $thisField) {
			$item->email_form->set($thisField->name, $item->params->get($thisField->name));
			$item->params->set($thisField->name, null);
		}

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

		JToolBarHelper::title(JText::_('COM_CONTACT_MANAGER_CONTACT'));
		JToolBarHelper::apply('contact.apply','JTOOLBAR_APPLY');
		JToolBarHelper::save('contact.save','JTOOLBAR_SAVE');
		JToolBarHelper::addNew('contact.save2new', 'JTOOLBAR_SAVE_AND_NEW');
				// If an existing item, can save to a copy.
		if (!$isNew) {
			JToolBarHelper::custom('contact.save2copy','save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY',false );
		}

		if (empty($this->item->id))  {
			JToolBarHelper::cancel('contact.cancel','JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('contact.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.contact.edit'.'JTOOLBAR_HELP');
	}
}