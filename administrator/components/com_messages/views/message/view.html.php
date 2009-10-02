<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
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
	public $recipientslist;
	public $subject;
	public $item;

	public function display($tpl = null)
	{
		$recipientslist = $this->get('RecipientsList');
		$subject = $this->get('Subject');
		$item = $this->get('Item');

		$model = $this->getModel();
		$model->markAsRead();

		$this->assignRef('recipientslist', $recipientslist);
		$this->assignRef('subject', $subject);
		$this->assignRef('item', $item);

		parent::display($tpl);

		if ($this->getLayout() == 'edit') {
			$this->_setFormToolbar();
		} else {
			$this->_setDefaultToolbar();
		}
	}

	protected function _setFormToolbar()
	{
		JToolBarHelper::title(JText::_('Write Private Message'), 'inbox.png');
		JToolBarHelper::save('save', 'Send');
		JToolBarHelper::cancel();
		JToolBarHelper::help('screen.messages.edit');
	}

	protected function _setDefaultToolbar()
	{
		JToolBarHelper::title(JText::_('View Private Message'), 'inbox.png');
		JToolBarHelper::custom('reply', 'restore.png', 'restore_f2.png', 'Reply', false);
		JToolBarHelper::deleteList();
		JToolBarHelper::cancel();
		JToolBarHelper::help('screen.messages.read');
	}
}