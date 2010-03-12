<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Messages component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesViewMessage extends JView
{
	protected $state;
	protected $item;
	protected $form;

	public function display($tpl = null)
	{
		$app	= JFactory::getApplication();
		$state	= $this->get('State');
		$item	= $this->get('Item');
		$form	= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Bind the record to the form.
		$form->bind($item);

		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);
		$this->assignRef('form',	$form);

		parent::display($tpl);
		$this->_setToolbar();
	}

	/**
	 * Setup the Toolbar.
	 */
	protected function _setToolbar()
	{
		if ($this->getLayout() == 'edit') {
			JToolBarHelper::title(JText::_('COM_MESSAGES_WRITE_PRIVATE_MESSAGE'), 'inbox.png');
			JToolBarHelper::save('message.save', 'COM_MESSAGES_TOOLBAR_SEND');
			JToolBarHelper::cancel('message.cancel','JTOOLBAR_CANCEL');
			JToolBarHelper::help('screen.messages.edit','JTOOLBAR_HELP');
		} else {
			JToolBarHelper::title(JText::_('COM_MESSAGES_VIEW_PRIVATE_MESSAGE'), 'inbox.png');
			JToolBarHelper::custom('message.reply', 'restore.png', 'restore_f2.png', 'COM_MESSAGES_TOOLBAR_REPLY', false);
			JToolBarHelper::cancel('message.cancel');
			JToolBarHelper::help('screen.messages.read');
		}
	}
}