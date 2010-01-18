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
		$form 	= $this->get('Form');

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
			JToolBarHelper::title(JText::_('Messages_Write_Private_Message'), 'inbox.png');
			JToolBarHelper::save('message.save', 'Messages_Toolbar_Send');
			JToolBarHelper::cancel('message.cancel');
			JToolBarHelper::help('screen.messages.edit');
		} else {
			JToolBarHelper::title(JText::_('Messages_View_Private_Message'), 'inbox.png');
			JToolBarHelper::custom('message.reply', 'restore.png', 'restore_f2.png', 'Messages_Toolbar_Reply', false);
			JToolBarHelper::cancel('message.cancel');
			JToolBarHelper::help('screen.messages.read');
		}
	}
}