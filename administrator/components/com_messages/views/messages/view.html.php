<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of messages.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesViewMessages extends JView
{
	protected $state;
	protected $items;
	protected $pagination;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
		$this->_setToolbar();
	}

	/**
	 * Setup the Toolbar.
	 */
	protected function _setToolbar()
	{
		$state	= $this->get('State');
		$canDo	= MessagesHelper::getActions();

		JToolBarHelper::title(JText::_('Messages_Manager_Messages'), 'inbox.png');

		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('message.add');
		}
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('messages.publish', 'publish.png', 'publish_f2.png', 'Messages_Toolbar_Publish', true);
			JToolBarHelper::custom('messages.unpublish', 'unpublish.png', 'unpublish_f2.png', 'Messages_Toolbar_UnPublish', true);
		}
		if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'messages.delete');
		}
		else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('messages.trash');
		}

		//JToolBarHelper::addNew('module.add');
		$bar = &JToolBar::getInstance('toolbar');
		$bar->appendButton('Popup', 'config', 'Messages_Toolbar_My_Settings', 'index.php?option=com_messages&amp;view=config&amp;tmpl=component', 850, 400);

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_messages');
		}
		JToolBarHelper::help('screen.messages.inbox');
	}
}