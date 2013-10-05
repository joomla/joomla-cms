<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Messages component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 * @since       1.6
 */
class MessagesViewMessage extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $state;

	public function display($tpl = null)
	{
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		if ($this->getLayout() == 'edit')
		{
			JToolbarHelper::title(JText::_('COM_MESSAGES_WRITE_PRIVATE_MESSAGE'), 'new-privatemessage.png');
			JToolbarHelper::save('message.save', 'COM_MESSAGES_TOOLBAR_SEND');
			JToolbarHelper::cancel('message.cancel');
			JToolbarHelper::help('JHELP_COMPONENTS_MESSAGING_WRITE');
		}
		else
		{
			JToolbarHelper::title(JText::_('COM_MESSAGES_VIEW_PRIVATE_MESSAGE'), 'inbox.png');
			$sender = JUser::getInstance($this->item->user_id_from);
			if ($sender->authorise('core.admin') || $sender->authorise('core.manage', 'com_messages') && $sender->authorise('core.login.admin'))
			{
				JToolbarHelper::custom('message.reply', 'redo', null, 'COM_MESSAGES_TOOLBAR_REPLY', false);
			}
			JToolbarHelper::cancel('message.cancel');
			JToolbarHelper::help('JHELP_COMPONENTS_MESSAGING_READ');
		}
	}
}
